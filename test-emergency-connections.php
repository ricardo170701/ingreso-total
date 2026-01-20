#!/usr/bin/env php
<?php
/**
 * Script de prueba para verificar conexiones al servidor de emergencia (puerto 8000)
 * en las Raspberries Pi.
 *
 * Uso: php test-emergency-connections.php
 */

// Lista de IPs a verificar (modifica según tus Raspberries)
$ips_a_verificar = [
    '192.168.1.100', // Ejemplo: IP Raspberry 1
    '192.168.1.101', // Ejemplo: IP Raspberry 2
    // Agrega más IPs aquí
];

$puerto = 8000;
$timeout = 2.0; // segundos

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║  Test de Conexión - Servidor de Emergencia (Puerto 8000)    ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

echo "Puerto: $puerto\n";
echo "Timeout: $timeout segundos\n\n";

if (empty($ips_a_verificar)) {
    echo "⚠️  ERROR: No hay IPs configuradas para verificar.\n";
    echo "   Edita este archivo y agrega las IPs de tus Raspberries en la variable \$ips_a_verificar\n\n";
    exit(1);
}

echo "Verificando " . count($ips_a_verificar) . " dispositivo(s)...\n\n";

$exitosas = 0;
$fallidas = 0;

foreach ($ips_a_verificar as $ip) {
    echo "📡 Probando $ip:$puerto ... ";

    $start = microtime(true);
    $conexion = @fsockopen($ip, $puerto, $errno, $errstr, $timeout);
    $tiempo = round((microtime(true) - $start) * 1000, 2);

    if ($conexion) {
        fclose($conexion);
        echo "✅ OK ({$tiempo}ms)\n";
        $exitosas++;

        // Intentar hacer una petición HTTP GET al status
        echo "   └─ Consultando estado... ";
        $ch = curl_init("http://{$ip}:{$puerto}/api/emergency/status");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if ($data && isset($data['ok']) && $data['ok'] === true) {
                $activa = $data['active'] ?? false;
                echo $activa ? "🚨 EMERGENCIA ACTIVA\n" : "✅ Normal\n";
            } else {
                echo "⚠️  Respuesta inválida\n";
            }
        } else {
            echo "❌ HTTP $httpCode\n";
        }
    } else {
        echo "❌ FALLO ({$tiempo}ms)\n";
        echo "   └─ Error: [$errno] $errstr\n";
        $fallidas++;

        // Sugerencias de diagnóstico
        if ($errno === 110 || $errno === 0) {
            echo "   └─ 💡 Posibles causas:\n";
            echo "      • ingreso.py no está corriendo en la Raspberry\n";
            echo "      • Firewall bloqueando el puerto 8000\n";
            echo "      • IP incorrecta o dispositivo apagado\n";
        }
    }
    echo "\n";
}

echo str_repeat("─", 64) . "\n";
echo "📊 RESUMEN\n";
echo "   ✅ Exitosas: $exitosas\n";
echo "   ❌ Fallidas: $fallidas\n";
echo "   📈 Tasa de éxito: " . ($exitosas > 0 ? round(($exitosas / count($ips_a_verificar)) * 100, 1) : 0) . "%\n\n";

if ($fallidas > 0) {
    echo "⚠️  RECOMENDACIONES:\n\n";
    echo "1. Verifica que ingreso.py esté corriendo en las Raspberries:\n";
    echo "   ssh pi@IP_RASPBERRY\n";
    echo "   ps aux | grep ingreso.py\n\n";

    echo "2. Verifica que el puerto 8000 esté escuchando:\n";
    echo "   netstat -tlnp | grep 8000\n\n";

    echo "3. Prueba la conexión localmente desde la Raspberry:\n";
    echo "   curl http://localhost:8000/api/emergency/status\n\n";

    echo "4. Verifica el firewall:\n";
    echo "   sudo ufw status\n";
    echo "   sudo ufw allow 8000/tcp\n\n";

    exit(1);
}

echo "✅ Todas las conexiones fueron exitosas!\n\n";
exit(0);
