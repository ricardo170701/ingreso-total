<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Generador QR (Básico) - Escaner Total</title>
    <style>
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, "Noto Sans", "Liberation Sans", sans-serif;
            margin: 0;
            background: #0b1220;
            color: #e5e7eb;
        }

        .wrap {
            max-width: 980px;
            margin: 0 auto;
            padding: 28px 16px;
        }

        .card {
            background: #0f172a;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 14px;
            padding: 18px;
        }

        h1 {
            font-size: 20px;
            margin: 0 0 10px;
        }

        p {
            margin: 0 0 14px;
            color: #cbd5e1;
        }

        label {
            display: block;
            font-weight: 600;
            margin: 10px 0 6px;
        }

        input {
            width: 100%;
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            background: #0b1020;
            color: #e5e7eb;
        }

        .row {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }

        .col {
            flex: 1 1 360px;
        }

        button {
            margin-top: 10px;
            padding: 10px 14px;
            border-radius: 10px;
            border: 0;
            background: #22c55e;
            color: #052e16;
            font-weight: 700;
            cursor: pointer;
        }

        button:hover {
            filter: brightness(0.95);
        }

        .err {
            margin-top: 10px;
            padding: 10px 12px;
            border-radius: 10px;
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(239, 68, 68, 0.35);
            color: #fecaca;
        }

        .qrbox {
            margin-top: 12px;
            background: #ffffff;
            border-radius: 12px;
            padding: 14px;
            display: inline-block;
        }

        .muted {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 10px;
        }

        code {
            background: rgba(148, 163, 184, 0.12);
            padding: 2px 6px;
            border-radius: 6px;
        }
    </style>
</head>

<body>
    <div class="wrap">
        <div class="card">
            <h1>Generador de QR (básico)</h1>
            <p>Escribe un texto/código y el sistema genera un QR en SVG para pruebas rápidas con el lector.</p>

            <div class="row">
                <div class="col">
                    <form method="POST" action="{{ route('qr.tool.generate') }}">
                        @csrf
                        <label for="codigo">Código / Texto</label>
                        <input id="codigo" name="codigo" value="{{ old('codigo', $codigo) }}" placeholder="Ej: 0123456789ABCDEF..." />
                        <button type="submit">Generar QR</button>
                    </form>

                    @if ($errors->any())
                    <div class="err">
                        @foreach ($errors->all() as $e)
                        <div>{{ $e }}</div>
                        @endforeach
                    </div>
                    @endif

                    <div class="muted">
                        Tip: si quieres probar el flujo real del sistema, pega aquí el <code>data.token</code> que retorna <code>POST /api/qrs</code>.
                    </div>
                </div>

                <div class="col">
                    @if ($svg)
                    <div class="qrbox">
                        {!! $svg !!}
                    </div>
                    <div class="muted">Contenido actual: <code>{{ $codigo }}</code></div>
                    @else
                    <div class="muted">Aún no has generado ningún QR.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</body>

</html>
