#!/usr/bin/python3
import os
import time
import json
import threading
import subprocess
from http.server import BaseHTTPRequestHandler, HTTPServer

import requests
import RPi.GPIO as GPIO
from tkinter import Tk, Entry, mainloop

# Forzar display en Raspberry (si aplica)
if os.environ.get("DISPLAY", "") == "":
    os.environ.__setitem__("DISPLAY", ":0.0")

# =========================
# Config API (Laravel)
# =========================
# Cada Raspberry controla un SOLO tipo de evento (entrada o salida).
# Configura por dispositivo vía variables de entorno para no tocar el código.
#
# Ejemplo (Raspberry entrada):
#   export API_BASE="http://127.0.0.1:8001"
#   export CODIGO_FISICO="P1-ENT"
#   export TIPO_EVENTO="entrada"
#   export DISPOSITIVO_ID="P1-ENT-RPI-ENTRADA"
#
# Ejemplo (Raspberry salida):
#   export TIPO_EVENTO="salida"
#   export DISPOSITIVO_ID="P1-ENT-RPI-SALIDA"
API_BASE = os.getenv(
    "API_BASE", "https://l4decybayq.sharedwithexpose.com/api/documentation"
)
CODIGO_FISICO = os.getenv("CODIGO_FISICO", "P2-ENT")
TIPO_EVENTO = os.getenv("TIPO_EVENTO", "entrada")  # "entrada" o "salida"
DISPOSITIVO_ID = os.getenv("DISPOSITIVO_ID", "P1-ENT-RPI-ENTRADA")
DEVICE_KEY = os.getenv(
    "DEVICE_KEY", "1234abcd"
)  # opcional si activaste ACCESS_DEVICE_KEY en el backend

# Relés / pines GPIO (BOARD)
RELAY_PINS = [35, 37, 29, 31, 33]
OPEN_SECONDS = float(os.getenv("OPEN_SECONDS", "5"))

# Estado de apertura manual (persistente en disco)
MANUAL_OPEN_STATE_PATH = os.getenv(
    "MANUAL_OPEN_STATE_PATH", "/var/lib/door/manual_open.json"
)

# =========================
# Emergencia (modo libre) - sin dependencias
# =========================
DOOR_API_KEY = os.getenv("DOOR_API_KEY", "cambia_esto")  # misma clave que usará Laravel
EMERGENCY_PORT = int(os.getenv("EMERGENCY_PORT", "8000"))
STATE_PATH = os.getenv("DOOR_STATE_PATH", "/var/lib/door/emergency.json")


def _load_state():
    """Carga el estado de emergencia desde disco"""
    try:
        with open(STATE_PATH, "r", encoding="utf-8") as f:
            return json.load(f)
    except Exception:
        return {}


def _save_state(state: dict):
    """Guarda el estado de emergencia en disco (atomic write)"""
    os.makedirs(os.path.dirname(STATE_PATH), exist_ok=True)
    tmp = STATE_PATH + ".tmp"
    with open(tmp, "w", encoding="utf-8") as f:
        json.dump(state, f)
    os.replace(tmp, STATE_PATH)


def emergency_until() -> int:
    """Retorna timestamp Unix hasta cuándo está activa la emergencia"""
    st = _load_state()
    return int(st.get("emergency_until", 0) or 0)


def emergency_active() -> bool:
    """Verifica si la emergencia está activa (no vencida)"""
    return int(time.time()) < emergency_until()


def set_emergency(seconds: int) -> int:
    """Activa emergencia por N segundos. Retorna timestamp de vencimiento"""
    now = int(time.time())
    until = now + int(seconds)
    _save_state({"emergency_until": until})
    return until


class EmergencyHandler(BaseHTTPRequestHandler):
    """Handler HTTP para recibir comandos de emergencia desde Laravel"""

    def _json(self, code, payload):
        """Envía respuesta JSON"""
        body = json.dumps(payload).encode("utf-8")
        self.send_response(code)
        self.send_header("Content-Type", "application/json; charset=utf-8")
        self.send_header("Content-Length", str(len(body)))
        self.end_headers()
        self.wfile.write(body)

    def _authorized(self) -> bool:
        """Verifica API key en header (soporta X-API-KEY y X-DEVICE-KEY)"""
        api_key = self.headers.get("X-API-KEY", "") or self.headers.get(
            "X-DEVICE-KEY", ""
        )
        return api_key == DOOR_API_KEY

    def do_GET(self):
        """GET /api/emergency/status - Consulta estado de emergencia
        GET /api/door/status - Consulta estado de la puerta (manual open)"""

        if self.path == "/api/door/status":
            self._json(
                200,
                {
                    "ok": True,
                    "manual_open": is_manual_open(),
                },
            )
            return

        if self.path != "/api/emergency/status":
            self._json(404, {"ok": False, "error": "not_found"})
            return

        now = int(time.time())
        until = emergency_until()
        self._json(
            200,
            {
                "ok": True,
                "active": now < until,
                "now": now,
                "emergency_until": until,
                "remaining_seconds": max(0, until - now),
            },
        )

    def do_POST(self):
        """POST /api/emergency/activate - Activa emergencia por N segundos
        POST /reboot - Reinicia la Raspberry Pi
        POST /api/door/toggle - Abre/cierra la puerta manualmente"""

        # Endpoint de apertura/cierre manual
        if self.path == "/api/door/toggle":
            if not self._authorized():
                self._json(401, {"ok": False, "error": "unauthorized"})
                return

            try:
                # Obtener estado actual
                estado_actual = is_manual_open()
                # Cambiar estado (toggle)
                nuevo_estado = not estado_actual
                mantener_puerta_abierta(nuevo_estado)

                self._json(
                    200,
                    {
                        "ok": True,
                        "manual_open": nuevo_estado,
                        "message": (
                            "Puerta abierta" if nuevo_estado else "Puerta cerrada"
                        ),
                    },
                )
            except Exception as e:
                self._json(500, {"ok": False, "error": str(e)})
            return

        # Endpoint de reinicio
        if self.path == "/reboot":
            if not self._authorized():
                self._json(401, {"ok": False, "error": "unauthorized"})
                return

            # Ejecutar sudo reboot en un proceso separado
            try:
                # Ejecutar sudo reboot en background (el servidor se desconectará)
                subprocess.Popen(
                    ["sudo", "reboot"],
                    stdout=subprocess.DEVNULL,
                    stderr=subprocess.DEVNULL,
                )
                self._json(
                    200, {"ok": True, "message": "Comando de reinicio ejecutado"}
                )
            except Exception as e:
                self._json(500, {"ok": False, "error": str(e)})
            return

        # Endpoint de emergencia
        if self.path != "/api/emergency/activate":
            self._json(404, {"ok": False, "error": "not_found"})
            return

        if not self._authorized():
            self._json(401, {"ok": False, "error": "unauthorized"})
            return

        length = int(self.headers.get("Content-Length", "0") or 0)
        raw = self.rfile.read(length) if length > 0 else b"{}"
        try:
            data = json.loads(raw.decode("utf-8") or "{}")
        except Exception:
            self._json(400, {"ok": False, "error": "invalid_json"})
            return

        seconds = int(data.get("duration_seconds", 900) or 900)
        # Límites básicos para evitar abusos
        if seconds < 10 or seconds > 3600:
            self._json(422, {"ok": False, "error": "duration_out_of_range"})
            return

        until = set_emergency(seconds)
        self._json(
            200, {"ok": True, "duration_seconds": seconds, "emergency_until": until}
        )

    def log_message(self, format, *args):
        """Silencia logs del servidor HTTP (opcional)"""
        pass


def start_emergency_server():
    """Inicia el servidor HTTP en un thread separado"""
    server = HTTPServer(("0.0.0.0", EMERGENCY_PORT), EmergencyHandler)
    print(f"Servidor de emergencia iniciado en puerto {EMERGENCY_PORT}")
    server.serve_forever()


def mantener_puerta_abierta(abierta: bool):
    """Mantener la puerta abierta (activar relés) o cerrarla (desactivar relés)"""
    for pin in RELAY_PINS:
        GPIO.output(pin, abierta)

    # Guardar estado
    _save_manual_state({"manual_open": abierta})


def _save_manual_state(state: dict):
    """Guarda el estado de apertura manual en disco"""
    os.makedirs(os.path.dirname(MANUAL_OPEN_STATE_PATH), exist_ok=True)
    tmp = MANUAL_OPEN_STATE_PATH + ".tmp"
    with open(tmp, "w", encoding="utf-8") as f:
        json.dump(state, f)
    os.replace(tmp, MANUAL_OPEN_STATE_PATH)


def _load_manual_state() -> dict:
    """Carga el estado de apertura manual desde disco"""
    try:
        with open(MANUAL_OPEN_STATE_PATH, "r", encoding="utf-8") as f:
            return json.load(f)
    except Exception:
        return {"manual_open": False}


def is_manual_open() -> bool:
    """Retorna si la puerta está manualmente abierta"""
    state = _load_manual_state()
    return bool(state.get("manual_open", False))


def abrir_puerta(segundos: float = None):
    """Abre la puerta por el tiempo especificado. Si no se especifica, usa OPEN_SECONDS."""
    tiempo_apertura = segundos if segundos is not None else OPEN_SECONDS
    # Activar relés por X segundos
    for pin in RELAY_PINS:
        GPIO.output(pin, True)
    time.sleep(tiempo_apertura)
    for pin in RELAY_PINS:
        GPIO.output(pin, False)


def denegar_puerta():
    # Aquí puedes agregar sonido / led / UI. Por ahora solo log.
    print("Acceso denegado")


def verify_access(token: str) -> dict:
    url = f"{API_BASE}/api/access/verify"
    headers = {"Accept": "application/json"}
    if DEVICE_KEY:
        headers["X-DEVICE-KEY"] = DEVICE_KEY

    payload = {
        "token": token,
        "codigo_fisico": CODIGO_FISICO,
        "tipo_evento": TIPO_EVENTO,
        "dispositivo_id": DISPOSITIVO_ID,
    }

    r = requests.post(url, json=payload, headers=headers, timeout=2)
    r.raise_for_status()
    return r.json()


GPIO.setmode(GPIO.BOARD)
for pin in RELAY_PINS:
    GPIO.setup(pin, GPIO.OUT)
    GPIO.output(pin, False)

# Restaurar estado de apertura manual al iniciar (si estaba abierta)
try:
    if is_manual_open():
        print("Restaurando estado: puerta abierta manualmente")
        mantener_puerta_abierta(True)
except Exception as e:
    print(f"Error restaurando estado manual: {e}")


master = Tk()
e = Entry(master)
e.pack()
e.focus_set()


def callback(event):
    try:
        token = e.get().strip()
        if not token:
            return

        # ✅ Si está abierta manualmente: NO consultes API, abre directo
        if is_manual_open():
            print("PUERTA ABIERTA MANUALMENTE: abriendo sin validar QR")
            abrir_puerta()
            return

        # ✅ Si está en emergencia: NO consultes API, abre directo
        if emergency_active():
            print("EMERGENCIA ACTIVA: abriendo sin validar QR")
            abrir_puerta()
            return

        # Llamada al backend (Laravel) para validar permisos y registrar el evento.
        try:
            res = verify_access(token)
        except Exception as ex:
            print("Error conectando a API:", ex)
            denegar_puerta()
            return

        permitido = bool(res.get("permitido", False))
        mensaje = res.get("message", "")
        tiempo_apertura = res.get("tiempo_apertura")

        print("API:", permitido, mensaje)
        if tiempo_apertura is not None:
            print(f"Tiempo de apertura: {tiempo_apertura} segundos")

        if permitido:
            # Usar el tiempo de apertura de la API si está disponible, sino usar OPEN_SECONDS
            tiempo = (
                float(tiempo_apertura) if tiempo_apertura is not None else OPEN_SECONDS
            )
            abrir_puerta(tiempo)
        else:
            denegar_puerta()
    except Exception as ex:
        print("error:", ex)
    finally:
        e.delete("0", "end")


# ✅ Arranca el agente HTTP en paralelo (no bloquea Tk)
t = threading.Thread(target=start_emergency_server, daemon=True)
t.start()

master.bind("<Return>", callback)
mainloop()
