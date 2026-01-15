<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mantenimiento #{{ $mantenimiento->id }}</title>
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
            margin-bottom: 10px;
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
            margin-top: 25px;
            margin-bottom: 10px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }

        .descripcion-content {
            background-color: #f5f5f5;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            white-space: pre-wrap;
            min-height: 80px;
        }

        .documentos-list {
            margin-top: 10px;
        }

        .documento-item {
            margin-bottom: 8px;
            padding: 8px;
            background-color: #f9f9f9;
            border-left: 3px solid #dc2626;
        }

        .documento-nombre {
            font-weight: bold;
            color: #dc2626;
        }

        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #ccc;
            font-size: 9pt;
            color: #666;
            text-align: center;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10pt;
            font-weight: bold;
        }

        .badge-realizado {
            background-color: #d1fae5;
            color: #065f46;
        }

        .badge-programado {
            background-color: #fef3c7;
            color: #92400e;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>REPORTE DE MANTENIMIENTO</h1>
        <p style="margin: 5px 0; font-size: 12pt;">Mantenimiento #{{ $mantenimiento->id }}</p>
    </div>

    <div class="info-section">
        <div class="info-row">
            <div class="info-label">Puerta:</div>
            <div class="info-value">{{ $mantenimiento->puerta->nombre }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Ubicación:</div>
            <div class="info-value">{{ $mantenimiento->puerta->piso->nombre ?? 'N/A' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Fecha:</div>
            <div class="info-value">{{ $mantenimiento->fecha_mantenimiento->format('d/m/Y') }}</div>
        </div>
        @if(($mantenimiento->tipo ?? null) === 'programado')
        <div class="info-row">
            <div class="info-label">Fecha límite:</div>
            <div class="info-value">
                {{ $mantenimiento->fecha_fin_programada?->format('d/m/Y') ?? '-' }}
            </div>
        </div>
        @endif
        <div class="info-row">
            <div class="info-label">Tipo:</div>
            <div class="info-value">
                <span class="badge badge-{{ $mantenimiento->tipo }}">
                    {{ $mantenimiento->tipo === 'realizado' ? 'Realizado' : 'Programado' }}
                </span>
            </div>
        </div>
    </div>

    @if($mantenimiento->descripcion_mantenimiento)
    <div class="section-title">DESCRIPCIÓN DE MANTENIMIENTO</div>
    <div class="descripcion-content">
        {{ $mantenimiento->descripcion_mantenimiento }}
    </div>
    @endif

    @if($mantenimiento->documentos && $mantenimiento->documentos->count() > 0)
    <div class="section-title">DOCUMENTOS ADJUNTOS</div>
    <div class="documentos-list">
        @foreach($mantenimiento->documentos as $documento)
        <div class="documento-item">
            <div class="documento-nombre">📄 {{ $documento->nombre_original ?? 'Documento PDF' }}</div>
        </div>
        @endforeach
        <p style="margin-top: 10px; font-size: 10pt; color: #666;">
            Total de documentos: {{ $mantenimiento->documentos->count() }}
        </p>
    </div>
    @endif

    <div class="section-title">DATOS DE AUDITORÍA</div>
    <div class="info-section">
        <div class="info-row">
            <div class="info-label">Creado por:</div>
            <div class="info-value">{{ $mantenimiento->creadoPor->name ?? $mantenimiento->creadoPor->email ?? 'N/A' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Fecha de creación:</div>
            <div class="info-value">{{ $mantenimiento->created_at->format('d/m/Y H:i:s') }}</div>
        </div>
        @if($mantenimiento->editadoPor)
        <div class="info-row">
            <div class="info-label">Editado por:</div>
            <div class="info-value">{{ $mantenimiento->editadoPor->name ?? $mantenimiento->editadoPor->email ?? 'N/A' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Fecha de modificación:</div>
            <div class="info-value">{{ $mantenimiento->updated_at->format('d/m/Y H:i:s') }}</div>
        </div>
        @endif
    </div>

    <div class="footer">
        <p>Generado el {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>Sistema de Gestión de Puertas y Accesos</p>
    </div>
</body>

</html>
