#!/usr/bin/env python3
"""
Servidor Mock para probar el Protocolo de Emergencia en desarrollo.

Este script simula el servidor Python que corre en las Raspberry Pi para
probar el protocolo de emergencia sin necesidad de hardware físico.

Uso:
    python test-emergency-server.py

El servidor escuchará en http://localhost:8000 y responderá a las peticiones
del protocolo de emergencia.
"""

import json
import time
from http.server import BaseHTTPRequestHandler, HTTPServer
from datetime import datetime

# Configuración (debe coincidir con tu .env)
DOOR_API_KEY = "D8738A38CC8FC927C5EC594F47A22787"  # O usa la de tu .env
PORT = 8000

# Estado de emergencia (en memoria para pruebas)
emergency_state = {"emergency_until": 0, "active": False}


class MockEmergencyHandler(BaseHTTPRequestHandler):
    """Handler HTTP que simula el servidor de emergencia de las puertas"""

    def _json(self, code, payload):
        """Envía respuesta JSON"""
        body = json.dumps(payload, ensure_ascii=False).encode("utf-8")
        self.send_response(code)
        self.send_header("Content-Type", "application/json; charset=utf-8")
        self.send_header("Content-Length", str(len(body)))
        self.end_headers()
        self.wfile.write(body)

    def _authorized(self) -> bool:
        """Verifica API key en header"""
        api_key = self.headers.get("X-API-KEY", "") or self.headers.get(
            "X-DEVICE-KEY", ""
        )
        return api_key == DOOR_API_KEY

    def log_message(self, format, *args):
        """Log personalizado con timestamp"""
        timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        print(f"[{timestamp}] {format % args}")

    def do_GET(self):
        """GET /api/emergency/status - Consulta estado de emergencia"""
        if self.path != "/api/emergency/status":
            self._json(404, {"ok": False, "error": "not_found"})
            return

        now = int(time.time())
        until = emergency_state["emergency_until"]
        active = now < until

        self._json(
            200,
            {
                "ok": True,
                "active": active,
                "now": now,
                "emergency_until": until,
                "remaining_seconds": max(0, until - now),
            },
        )

    def do_POST(self):
        """POST /api/emergency/activate - Activa emergencia por N segundos"""
        if self.path != "/api/emergency/activate":
            self._json(404, {"ok": False, "error": "not_found"})
            return

        if not self._authorized():
            self._json(401, {"ok": False, "error": "unauthorized"})
            print("⚠️  Intento de acceso no autorizado (API key incorrecta)")
            return

        # Leer body
        length = int(self.headers.get("Content-Length", "0") or 0)
        raw = self.rfile.read(length) if length > 0 else b"{}"
        try:
            data = json.loads(raw.decode("utf-8") or "{}")
        except Exception as e:
            self._json(400, {"ok": False, "error": "invalid_json"})
            return

        seconds = int(data.get("duration_seconds", 900) or 900)

        # Límites básicos
        if seconds < 10 or seconds > 3600:
            self._json(422, {"ok": False, "error": "duration_out_of_range"})
            return

        # Activar emergencia
        now = int(time.time())
        until = now + seconds
        emergency_state["emergency_until"] = until
        emergency_state["active"] = True

        print(f"✅ PROTOCOLO DE EMERGENCIA ACTIVADO")
        print(f"   Duración: {seconds} segundos ({seconds/60:.1f} minutos)")
        print(
            f"   Vence en: {datetime.fromtimestamp(until).strftime('%Y-%m-%d %H:%M:%S')}"
        )
        print(f"   IP del cliente: {self.client_address[0]}")

        self._json(
            200,
            {
                "ok": True,
                "duration_seconds": seconds,
                "emergency_until": until,
            },
        )


def main():
    """Inicia el servidor mock"""
    server = HTTPServer(("0.0.0.0", PORT), MockEmergencyHandler)
    print("=" * 60)
    print("🚨 SERVIDOR MOCK DE PROTOCOLO DE EMERGENCIA")
    print("=" * 60)
    print(f"📡 Escuchando en: http://localhost:{PORT}")
    print(f"🔑 API Key esperada: {DOOR_API_KEY}")
    print(f"📍 Endpoints disponibles:")
    print(f"   - GET  /api/emergency/status")
    print(f"   - POST /api/emergency/activate")
    print("=" * 60)
    print("💡 Para probar el protocolo:")
    print(
        "   1. Asegúrate de tener puertas en la BD con ip_entrada='127.0.0.1' o 'localhost'"
    )
    print("   2. Ve a /protocolo en la aplicación web")
    print("   3. Haz clic en 'Activar Protocolo de Emergencia'")
    print("   4. Observa los logs aquí para ver las peticiones")
    print("=" * 60)
    print("\nPresiona Ctrl+C para detener el servidor\n")

    try:
        server.serve_forever()
    except KeyboardInterrupt:
        print("\n\n🛑 Servidor detenido")
        server.shutdown()


if __name__ == "__main__":
    main()
