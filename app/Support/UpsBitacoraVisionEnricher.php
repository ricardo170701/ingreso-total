<?php

namespace App\Support;

/**
 * Completa input/output/power del preview cuando el modelo devolvió todo en fases o datos_adicionales.
 */
final class UpsBitacoraVisionEnricher
{
    /**
     * @param  array<string, mixed>  $datos
     */
    public static function enrich(array &$datos): void
    {
        if (! isset($datos['input']) || ! is_array($datos['input'])) {
            $datos['input'] = ['voltage' => null, 'frequency' => null];
        }
        if (! isset($datos['output']) || ! is_array($datos['output'])) {
            $datos['output'] = ['voltage' => null, 'frequency' => null, 'power' => null];
        }
        if (! isset($datos['fases']) || ! is_array($datos['fases'])) {
            $datos['fases'] = [];
        }
        if (! isset($datos['datos_adicionales']) || ! is_array($datos['datos_adicionales'])) {
            $datos['datos_adicionales'] = [];
        }

        $f = $datos['fases'];
        $extra = $datos['datos_adicionales'];

        $volts = [];
        $freqs = [];
        $powerSum = 0.0;
        $phasePowerParts = 0;

        foreach (['a', 'b', 'c'] as $p) {
            if (! isset($f[$p]) || ! is_array($f[$p])) {
                continue;
            }
            $row = $f[$p];
            if (isset($row['voltage']) && is_numeric($row['voltage'])) {
                $volts[] = (float) $row['voltage'];
            }
            if (isset($row['frecuencia']) && is_numeric($row['frecuencia'])) {
                $freqs[] = (float) $row['frecuencia'];
            }
            $v = isset($row['voltage']) && is_numeric($row['voltage']) ? (float) $row['voltage'] : null;
            $i = isset($row['corriente']) && is_numeric($row['corriente']) ? (float) $row['corriente'] : null;
            if ($v !== null && $i !== null) {
                $powerSum += $v * $i;
                $phasePowerParts++;
            }
        }

        if (($datos['output']['voltage'] ?? null) === null && $volts !== []) {
            $datos['output']['voltage'] = self::round2(array_sum($volts) / count($volts));
        }

        if (($datos['output']['frequency'] ?? null) === null && $freqs !== []) {
            $datos['output']['frequency'] = self::round2(array_sum($freqs) / count($freqs));
        }

        if (($datos['output']['power'] ?? null) === null && $phasePowerParts > 0 && $powerSum > 0) {
            $datos['output']['power'] = round($powerSum, 1);
        }

        // Tensión de línea (~208 V) en datos_adicionales → entrada
        $lineVs = [];
        $byLetter = ['a' => null, 'b' => null, 'c' => null];
        foreach ($extra as $k => $v) {
            if (! is_numeric($v)) {
                continue;
            }
            $ks = strtolower(preg_replace('/\s+/', '', (string) $k));
            if (preg_match('/^(linea_volt|line_volt)_([abc])$/', $ks, $m)) {
                $byLetter[$m[2]] = (float) $v;
            }
        }
        foreach (['a', 'b', 'c'] as $L) {
            if ($byLetter[$L] !== null) {
                $lineVs[] = $byLetter[$L];
            }
        }
        if (($datos['input']['voltage'] ?? null) === null && $lineVs !== []) {
            $datos['input']['voltage'] = self::round2(array_sum($lineVs) / count($lineVs));
        }

        // Claves alternativas que a veces devuelve el modelo
        if (($datos['input']['voltage'] ?? null) === null && isset($extra['input_line_voltage_v']) && is_numeric($extra['input_line_voltage_v'])) {
            $datos['input']['voltage'] = self::round2((float) $extra['input_line_voltage_v']);
        }

        foreach (['input_frequency', 'entrada_frecuencia_hz', 'frecuencia_entrada_hz'] as $k) {
            if (($datos['input']['frequency'] ?? null) === null && isset($extra[$k]) && is_numeric($extra[$k])) {
                $datos['input']['frequency'] = self::round2((float) $extra[$k]);
                break;
            }
        }

        // Misma red: si no hay Hz de entrada explícitos, reutilizar salida
        if (($datos['input']['frequency'] ?? null) === null && ($datos['output']['frequency'] ?? null) !== null) {
            $datos['input']['frequency'] = $datos['output']['frequency'];
        }

        self::normalizeVoltageFields($datos);
    }

    /**
     * Redondea todos los voltajes a 2 decimales (entrada/salida/batería, fases A/B/C, datos_adicionales).
     *
     * @param  array<string, mixed>  $datos  Misma forma que devuelve la visión (o datos_extraidos guardados).
     */
    public static function normalizeVoltageFields(array &$datos): void
    {
        foreach (['input', 'output'] as $sec) {
            if (! isset($datos[$sec]) || ! is_array($datos[$sec])) {
                continue;
            }
            if (isset($datos[$sec]['voltage']) && is_numeric($datos[$sec]['voltage'])) {
                $datos[$sec]['voltage'] = self::round2((float) $datos[$sec]['voltage']);
            }
        }

        if (isset($datos['battery']) && is_array($datos['battery']) && isset($datos['battery']['voltage']) && is_numeric($datos['battery']['voltage'])) {
            $datos['battery']['voltage'] = self::round2((float) $datos['battery']['voltage']);
        }

        if (isset($datos['fases']) && is_array($datos['fases'])) {
            foreach (['a', 'b', 'c'] as $p) {
                if (! isset($datos['fases'][$p]) || ! is_array($datos['fases'][$p])) {
                    continue;
                }
                if (isset($datos['fases'][$p]['voltage']) && is_numeric($datos['fases'][$p]['voltage'])) {
                    $datos['fases'][$p]['voltage'] = self::round2((float) $datos['fases'][$p]['voltage']);
                }
            }
        }

        if (! isset($datos['datos_adicionales']) || ! is_array($datos['datos_adicionales'])) {
            return;
        }

        foreach ($datos['datos_adicionales'] as $k => $v) {
            if (! is_numeric($v)) {
                continue;
            }
            $key = strtolower((string) $k);
            if (self::isVoltageAdditionalKey($key)) {
                $datos['datos_adicionales'][$k] = self::round2((float) $v);
            }
        }
    }

    private static function isVoltageAdditionalKey(string $key): bool
    {
        return (bool) preg_match('/voltaje|linea_volt|line_volt|_volt($|_)/i', $key);
    }

    private static function round2(float $n): float
    {
        return round($n, 2);
    }
}
