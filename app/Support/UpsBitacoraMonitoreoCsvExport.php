<?php

namespace App\Support;

use App\Models\UpsBitacora;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * CSV de bitácora operativa / monitoreo UPS (mismo formato en Reportes y en pantalla de bitácora).
 */
final class UpsBitacoraMonitoreoCsvExport
{
    public static function streamDownload(Builder $query, string $filename): StreamedResponse
    {
        $registros = $query->orderBy('created_at')->orderBy('id')->get();

        return response()->streamDownload(function () use ($registros) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                'ID Bitácora',
                'UPS ID',
                'UPS Nombre',
                'UPS Código',
                'Fecha registro',
                'Ind. Normal',
                'Ind. Batería',
                'Ind. Bypass',
                'Ind. Falla',
                'Voltaje entrada (V)',
                'Frecuencia entrada (Hz)',
                'Voltaje salida (V)',
                'Frecuencia salida (Hz)',
                'Potencia salida (W)',
                'Voltaje batería (V)',
                '% Batería',
                'Tiempo respaldo (min)',
                'Tiempo descarga (min)',
                'Estado batería',
                'Temperatura (°C)',
                'Modelo/marca (IA)',
                'Alarmas (IA)',
                'Fase A V',
                'Fase A A',
                'Fase A Hz',
                'Fase B V',
                'Fase B A',
                'Fase B Hz',
                'Fase C V',
                'Fase C A',
                'Fase C Hz',
                'Otros valores IA (plano)',
                'Observaciones',
                'Datos extraídos (JSON completo)',
                'Creado por',
                'Email creador',
            ]);

            foreach ($registros as $r) {
                /** @var UpsBitacora $r */
                $extra = is_array($r->datos_extraidos) ? $r->datos_extraidos : [];
                $extraStr = $extra === [] ? '' : json_encode($extra, JSON_UNESCAPED_UNICODE);

                fputcsv($file, [
                    $r->id,
                    $r->ups_id,
                    $r->ups?->nombre ?? '',
                    $r->ups?->codigo ?? '',
                    $r->created_at?->format('Y-m-d H:i:s') ?? '',
                    $r->indicador_normal ? 'Sí' : 'No',
                    $r->indicador_battery ? 'Sí' : 'No',
                    $r->indicador_bypass ? 'Sí' : 'No',
                    $r->indicador_fault ? 'Sí' : 'No',
                    $r->input_voltage,
                    $r->input_frequency,
                    $r->output_voltage,
                    $r->output_frequency,
                    $r->output_power,
                    $r->battery_voltage,
                    $r->battery_percentage,
                    $r->battery_tiempo_respaldo,
                    $r->battery_tiempo_descarga,
                    $r->battery_estado ?? '',
                    $r->temperatura,
                    $extra['modelo_ups'] ?? '',
                    self::upsAlarmasPlano($extra),
                    self::upsFaseValor($extra, 'a', 'voltage'),
                    self::upsFaseValor($extra, 'a', 'corriente'),
                    self::upsFaseValor($extra, 'a', 'frecuencia'),
                    self::upsFaseValor($extra, 'b', 'voltage'),
                    self::upsFaseValor($extra, 'b', 'corriente'),
                    self::upsFaseValor($extra, 'b', 'frecuencia'),
                    self::upsFaseValor($extra, 'c', 'voltage'),
                    self::upsFaseValor($extra, 'c', 'corriente'),
                    self::upsFaseValor($extra, 'c', 'frecuencia'),
                    self::upsDatosAdicionalesPlano($extra),
                    $r->observaciones ?? '',
                    $extraStr,
                    $r->creadoPor?->name ?? '',
                    $r->creadoPor?->email ?? '',
                ]);
            }

            fclose($file);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * @param  array<string, mixed>  $datosExtraidos
     */
    private static function upsFaseValor(array $datosExtraidos, string $fase, string $campo): string
    {
        $v = data_get($datosExtraidos, "fases.{$fase}.{$campo}");
        if ($v === null || $v === '') {
            return '';
        }

        return is_scalar($v) ? (string) $v : '';
    }

    /**
     * @param  array<string, mixed>  $datosExtraidos
     */
    private static function upsAlarmasPlano(array $datosExtraidos): string
    {
        $a = $datosExtraidos['alarmas'] ?? null;
        if (! is_array($a) || $a === []) {
            return '';
        }

        return implode(' | ', array_map(static function ($x) {
            return str_replace(["\r", "\n"], ' ', (string) $x);
        }, $a));
    }

    /**
     * @param  array<string, mixed>  $datosExtraidos
     */
    private static function upsDatosAdicionalesPlano(array $datosExtraidos): string
    {
        $d = $datosExtraidos['datos_adicionales'] ?? null;
        if (! is_array($d) || $d === []) {
            return '';
        }
        ksort($d);
        $parts = [];
        foreach ($d as $k => $v) {
            if (is_array($v) || is_object($v)) {
                $v = json_encode($v, JSON_UNESCAPED_UNICODE);
            }
            $parts[] = $k.'='.str_replace(["\r", "\n"], ' ', (string) $v);
        }

        return implode('; ', $parts);
    }
}
