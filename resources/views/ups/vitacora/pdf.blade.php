<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bitácora UPS - {{ $ups->codigo ?? $ups->nombre }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #000;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }

        .header h1 {
            margin: 0;
            font-size: 18pt;
            font-weight: bold;
        }

        .info-section {
            margin-bottom: 20px;
        }

        .info-row {
            display: flex;
            margin-bottom: 8px;
        }

        .info-label {
            font-weight: bold;
            width: 150px;
        }

        .info-value {
            flex: 1;
        }

        .section-title {
            font-size: 14pt;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }

        .indicadores {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
        }

        .indicador {
            padding: 5px 12px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 10pt;
        }

        .indicador.on {
            background-color: #2ecc71;
            color: white;
        }

        .indicador.off {
            background-color: #95a5a6;
            color: white;
        }

        .datos-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .datos-box {
            border: 1px solid #ccc;
            padding: 12px;
            border-radius: 4px;
            background-color: #f9f9f9;
        }

        .datos-box h3 {
            margin: 0 0 10px 0;
            font-size: 12pt;
            font-weight: bold;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        .observaciones {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            padding: 12px;
            border-radius: 4px;
            margin-top: 15px;
        }

        .metadata {
            background-color: #e9ecef;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 10pt;
        }

        .temperatura-box {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            padding: 12px;
            border-radius: 4px;
            margin-top: 15px;
            text-align: center;
        }

        .temperatura-box h3 {
            margin: 0 0 5px 0;
            font-size: 12pt;
        }

        .temperatura-value {
            font-size: 18pt;
            font-weight: bold;
            color: #0c5460;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Bitácora UPS: {{ $ups->codigo ?? $ups->nombre }}</h1>
        <p style="margin: 5px 0; font-size: 12pt;">{{ $ups->nombre ?? '' }}</p>
    </div>

    <div class="metadata">
        <div class="info-row">
            <span class="info-label">Fecha de registro:</span>
            <span class="info-value">{{ $vitacora->created_at->format('d/m/Y H:i:s') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Registrado por:</span>
            <span class="info-value">
                @if($vitacora->creadoPor)
                    @if($vitacora->creadoPor->nombre && $vitacora->creadoPor->apellido)
                        {{ $vitacora->creadoPor->nombre }} {{ $vitacora->creadoPor->apellido }}
                    @elseif($vitacora->creadoPor->name)
                        {{ $vitacora->creadoPor->name }}
                    @else
                        {{ $vitacora->creadoPor->email }}
                    @endif
                @else
                    Desconocido
                @endif
            </span>
        </div>
    </div>

    <div class="section-title">Indicadores</div>
    <div class="indicadores">
        <span class="indicador {{ $vitacora->indicador_normal ? 'on' : 'off' }}">
            NORMAL: {{ $vitacora->indicador_normal ? 'ON' : 'OFF' }}
        </span>
        <span class="indicador {{ $vitacora->indicador_battery ? 'on' : 'off' }}">
            BATTERY: {{ $vitacora->indicador_battery ? 'ON' : 'OFF' }}
        </span>
        <span class="indicador {{ $vitacora->indicador_bypass ? 'on' : 'off' }}">
            BYPASS: {{ $vitacora->indicador_bypass ? 'ON' : 'OFF' }}
        </span>
        <span class="indicador {{ $vitacora->indicador_fault ? 'on' : 'off' }}">
            FAULT: {{ $vitacora->indicador_fault ? 'ON' : 'OFF' }}
        </span>
    </div>

    <div class="section-title">Datos Técnicos</div>
    <div class="datos-grid">
        <div class="datos-box">
            <h3>Input</h3>
            <div class="info-row">
                <span class="info-label">Voltaje:</span>
                <span class="info-value">{{ $vitacora->input_voltage ? number_format($vitacora->input_voltage, 2) . ' V' : '-' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Frecuencia:</span>
                <span class="info-value">{{ $vitacora->input_frequency ? number_format($vitacora->input_frequency, 2) . ' Hz' : '-' }}</span>
            </div>
        </div>

        <div class="datos-box">
            <h3>Output</h3>
            <div class="info-row">
                <span class="info-label">Voltaje:</span>
                <span class="info-value">{{ $vitacora->output_voltage ? number_format($vitacora->output_voltage, 2) . ' V' : '-' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Frecuencia:</span>
                <span class="info-value">{{ $vitacora->output_frequency ? number_format($vitacora->output_frequency, 2) . ' Hz' : '-' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Potencia:</span>
                <span class="info-value">{{ $vitacora->output_power ? number_format($vitacora->output_power, 2) . ' W' : '-' }}</span>
            </div>
        </div>

        <div class="datos-box">
            <h3>Battery</h3>
            <div class="info-row">
                <span class="info-label">Voltaje:</span>
                <span class="info-value">{{ $vitacora->battery_voltage ? number_format($vitacora->battery_voltage, 2) . ' V' : '-' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Porcentaje:</span>
                <span class="info-value">{{ $vitacora->battery_percentage !== null ? $vitacora->battery_percentage . '%' : '-' }}</span>
            </div>
            @if($vitacora->battery_tiempo_respaldo !== null)
            <div class="info-row">
                <span class="info-label">Tiempo Respaldo:</span>
                <span class="info-value">{{ $vitacora->battery_tiempo_respaldo }} min</span>
            </div>
            @endif
            @if($vitacora->battery_tiempo_descarga !== null)
            <div class="info-row">
                <span class="info-label">Tiempo Descarga:</span>
                <span class="info-value">{{ $vitacora->battery_tiempo_descarga }} min</span>
            </div>
            @endif
            @if($vitacora->battery_estado)
            <div class="info-row">
                <span class="info-label">Estado:</span>
                <span class="info-value">{{ ucfirst($vitacora->battery_estado) }}</span>
            </div>
            @endif
        </div>
    </div>

    @if($vitacora->temperatura !== null)
    <div class="temperatura-box">
        <h3>Temperatura</h3>
        <div class="temperatura-value">{{ number_format($vitacora->temperatura, 2) }} °C</div>
    </div>
    @endif

    @if($vitacora->observaciones)
    <div class="section-title">Observaciones</div>
    <div class="observaciones">
        {!! nl2br(e($vitacora->observaciones)) !!}
    </div>
    @endif

    @if($vitacora->imagenes && $vitacora->imagenes->count() > 0)
    <div class="section-title">Imágenes</div>
    <p style="font-size: 10pt; color: #666;">
        Este registro incluye {{ $vitacora->imagenes->count() }} imagen(es) que se encuentran en la carpeta <strong>fotos/</strong> de este directorio.
    </p>
    @endif
</body>

</html>

