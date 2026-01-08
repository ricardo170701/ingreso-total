<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperación de Contraseña</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <h1 style="color: #008c3a; margin: 0;">Escáner Total</h1>
        <p style="color: #666; margin: 5px 0 0 0;">Gobernación del Meta</p>
    </div>

    <div style="background-color: #ffffff; padding: 30px; border: 1px solid #e0e0e0; border-radius: 8px;">
        <h2 style="color: #333; margin-top: 0;">Recuperación de Contraseña</h2>
        
        <p>Hola <strong>{{ $user->name }}</strong>,</p>
        
        <p>Hemos recibido una solicitud para restablecer la contraseña de tu cuenta.</p>
        
        <p>Haz clic en el siguiente botón para restablecer tu contraseña:</p>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $resetUrl }}" 
               style="display: inline-block; padding: 12px 30px; background-color: #008c3a; color: #ffffff; text-decoration: none; border-radius: 5px; font-weight: bold;">
                Restablecer Contraseña
            </a>
        </div>
        
        <p style="color: #666; font-size: 14px;">
            O copia y pega este enlace en tu navegador:<br>
            <a href="{{ $resetUrl }}" style="color: #008c3a; word-break: break-all;">{{ $resetUrl }}</a>
        </p>
        
        <p style="color: #999; font-size: 12px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0;">
            <strong>Importante:</strong> Este enlace expirará en 60 minutos. Si no solicitaste este cambio, puedes ignorar este correo.
        </p>
    </div>

    <div style="text-align: center; margin-top: 20px; color: #999; font-size: 12px;">
        <p>© {{ date('Y') }} Gobernación del Meta. Todos los derechos reservados.</p>
    </div>
</body>
</html>

