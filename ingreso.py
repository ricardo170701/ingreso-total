#!/usr/bin/python3
import os
import time
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
API_BASE = os.getenv("API_BASE", "http://127.0.0.1:8001")
CODIGO_FISICO = os.getenv("CODIGO_FISICO", "P1-ENT")
TIPO_EVENTO = os.getenv("TIPO_EVENTO", "entrada")  # "entrada" o "salida"
DISPOSITIVO_ID = os.getenv("DISPOSITIVO_ID", "RPI-UNKNOWN")
DEVICE_KEY = os.getenv(
    "DEVICE_KEY", ""
)  # opcional si activaste ACCESS_DEVICE_KEY en el backend

# Relés / pines GPIO (BOARD)
RELAY_PINS = [35, 37, 29, 31, 33]
OPEN_SECONDS = float(os.getenv("OPEN_SECONDS", "5"))


def abrir_puerta():
    # Activar relés por X segundos
    for pin in RELAY_PINS:
        GPIO.output(pin, True)
    time.sleep(OPEN_SECONDS)
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


master = Tk()
e = Entry(master)
e.pack()
e.focus_set()


def callback(event):
    try:
        token = e.get().strip()
        if not token:
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
        print("API:", permitido, mensaje)

        if permitido:
            abrir_puerta()
        else:
            denegar_puerta()
    except Exception as ex:
        print("error:", ex)
    finally:
        e.delete("0", "end")


master.bind("<Return>", callback)
mainloop()
