<?php

namespace App\Support;

use App\Models\Ups;
use App\Models\UpsBitacora;

class UpsUmbralesEvaluator
{
    /**
     * @return array{activa: bool, mensajes: list<string>}
     */
    public static function alertas(Ups $ups, UpsBitacora $row): array
    {
        $umbrales = $ups->umbrales;
        if (!is_array($umbrales) || $umbrales === []) {
            return ['activa' => false, 'mensajes' => []];
        }

        $mensajes = [];

        self::checkMetric($umbrales, 'temperatura', $row->temperatura !== null ? (float) $row->temperatura : null, 'Temperatura (°C)', $mensajes);
        self::checkMetric($umbrales, 'battery_percentage', $row->battery_percentage !== null ? (float) $row->battery_percentage : null, '% batería', $mensajes);
        self::checkMetric($umbrales, 'input_voltage', $row->input_voltage !== null ? (float) $row->input_voltage : null, 'Voltaje entrada (V)', $mensajes);
        self::checkMetric($umbrales, 'output_voltage', $row->output_voltage !== null ? (float) $row->output_voltage : null, 'Voltaje salida (V)', $mensajes);
        self::checkMetric($umbrales, 'battery_voltage', $row->battery_voltage !== null ? (float) $row->battery_voltage : null, 'Voltaje batería (V)', $mensajes);
        self::checkMetric($umbrales, 'output_power', $row->output_power !== null ? (float) $row->output_power : null, 'Potencia salida (W)', $mensajes);

        return [
            'activa' => count($mensajes) > 0,
            'mensajes' => $mensajes,
        ];
    }

    /**
     * @param  list<string>  $mensajes
     */
    private static function checkMetric(
        array $umbrales,
        string $key,
        ?float $valor,
        string $label,
        array &$mensajes
    ): void {
        if ($valor === null || !isset($umbrales[$key]) || !is_array($umbrales[$key])) {
            return;
        }
        $r = $umbrales[$key];
        $min = array_key_exists('min', $r) && $r['min'] !== null && $r['min'] !== '' ? (float) $r['min'] : null;
        $max = array_key_exists('max', $r) && $r['max'] !== null && $r['max'] !== '' ? (float) $r['max'] : null;
        if ($min === null && $max === null) {
            return;
        }
        if ($min !== null && $valor < $min) {
            $mensajes[] = "{$label} {$valor} por debajo del mínimo configurado ({$min}).";
        }
        if ($max !== null && $valor > $max) {
            $mensajes[] = "{$label} {$valor} por encima del máximo configurado ({$max}).";
        }
    }
}
