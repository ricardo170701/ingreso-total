<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Código QR de Acceso</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background-color: #1e293b;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }

        .content {
            background-color: #f8fafc;
            padding: 30px;
            border-radius: 0 0 8px 8px;
        }

        .qr-container {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
        }

        .info-box {
            background-color: #e0f2fe;
            border-left: 4px solid #0ea5e9;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 12px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Escaner Total</h1>
        <p>Código QR de Acceso</p>
    </div>

    <div class="content">
        <p>Hola <strong>{{ $userName }}</strong>,</p>

        <p>Se ha generado un código QR para tu acceso al edificio. Para funcionarios, el QR estará activo hasta tu fecha de expiración (si aplica) o hasta que se marque como inactivo. Para visitantes, el QR es válido 24 h por defecto; si se indicó Fecha inicio/fin, rige ese rango.</p>

        <div class="qr-container">
            {!! $qrSvg !!}
            <p style="margin-top: 15px; font-size: 14px; color: #64748b;">
                Código: <strong>{{ $qrToken }}</strong>
            </p>
        </div>

        <div class="info-box">
            <p style="margin: 0;"><strong>⚠️ Importante:</strong></p>
            <ul style="margin: 10px 0 0 20px; padding: 0;">
                @if($expiresAt)
                <li>Este código QR expira el: <strong>{{ $expiresAt }}</strong></li>
                @else
                <li>Este código QR <strong>no expira</strong> (contrato indefinido). El acceso se mantendrá activo hasta que se marque como inactivo.</li>
                @endif
                <li>Usa este código para acceder a las puertas autorizadas</li>
                <li>Muestra el código QR al lector en la entrada</li>
            </ul>
        </div>

        <p>Si tienes alguna pregunta, contacta con el administrador del sistema.</p>
    </div>

    <div class="footer">
        <p>Este es un correo automático, por favor no respondas.</p>
        <p>&copy; {{ date('Y') }} Escaner Total - Sistema de Control de Accesos</p>
    </div>
</body>

</html>
