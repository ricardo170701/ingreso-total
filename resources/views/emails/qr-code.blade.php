<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Código QR de acceso</title>
</head>

<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background-color:#f1f5f9;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="max-width:560px;background-color:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">
                    {{-- Cabecera verde institucional --}}
                    <tr>
                        <td style="background:linear-gradient(135deg,#008c3a 0%,#006a2d 100%);padding:28px 24px;text-align:center;">
                            @if(!empty($logoAbsolutePath))
                                <img
                                    src="{{ $message->embed($logoAbsolutePath) }}"
                                    alt="Gobernación del Meta"
                                    width="200"
                                    style="display:block;margin:0 auto 16px auto;max-width:200px;height:auto;border:0;"
                                />
                            @else
                                <p style="margin:0 0 8px 0;color:#ffffff;font-size:20px;font-weight:bold;letter-spacing:0.02em;">
                                    Gobernación del Meta
                                </p>
                            @endif
                            <p style="margin:0;color:rgba(255,255,255,0.95);font-size:15px;font-weight:600;">
                                Código QR de acceso
                            </p>
                        </td>
                    </tr>

                    {{-- Cuerpo --}}
                    <tr>
                        <td style="padding:28px 24px 8px 24px;">
                            <p style="margin:0 0 16px 0;color:#0f172a;font-size:16px;line-height:1.5;">
                                Hola <strong>{{ $userName }}</strong>,
                            </p>
                            <p style="margin:0 0 24px 0;color:#334155;font-size:16px;line-height:1.6;">
                                Meta te da la bienvenida a nuestras oficinas. Usa este código QR para accesar.
                            </p>

                            {{-- QR incrustado (PNG por CID; visible en la mayoría de clientes) --}}
                            <table role="presentation" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="center" style="padding:20px 16px;background-color:#f8fafc;border-radius:10px;border:1px solid #e2e8f0;">
                                        @if(!empty($qrPngBinary))
                                            <img
                                                src="{{ $message->embedData($qrPngBinary, 'codigo-qr-acceso.png', 'image/png') }}"
                                                alt="Código QR de acceso"
                                                width="240"
                                                height="240"
                                                style="display:block;margin:0 auto;border:0;outline:none;text-decoration:none;"
                                            />
                                        @elseif(!empty(trim($qrSvg ?? '')))
                                            <div style="max-width:280px;margin:0 auto;">
                                                {!! $qrSvg !!}
                                            </div>
                                        @endif
                                        <p style="margin:16px 0 0 0;font-size:14px;color:#64748b;">
                                            Código: <strong style="color:#0f172a;letter-spacing:0.04em;">{{ $qrToken }}</strong>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Aviso vigencia --}}
                    <tr>
                        <td style="padding:8px 24px 28px 24px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background-color:#e0f2fe;border-left:4px solid #0ea5e9;border-radius:6px;">
                                <tr>
                                    <td style="padding:16px 18px;">
                                        <p style="margin:0 0 8px 0;font-size:14px;font-weight:bold;color:#0c4a6e;">
                                            Importante
                                        </p>
                                        <ul style="margin:0;padding-left:20px;color:#0f172a;font-size:14px;line-height:1.55;">
                                            @if($expiresAt)
                                                <li style="margin-bottom:6px;">Este código QR expira el: <strong>{{ $expiresAt }}</strong></li>
                                            @else
                                                <li style="margin-bottom:6px;">Este código QR <strong>no tiene fecha de expiración en el mensaje</strong>; la vigencia la define la política del sistema.</li>
                                            @endif
                                            <li style="margin-bottom:6px;">Úsalo para acceder a las puertas autorizadas.</li>
                                            <li>Muéstralo al lector en la entrada.</li>
                                        </ul>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:20px 0 0 0;font-size:13px;color:#64748b;line-height:1.5;">
                                Si tienes alguna pregunta, contacta al administrador del sistema.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:16px 24px 24px 24px;border-top:1px solid #e2e8f0;text-align:center;">
                            <p style="margin:0;font-size:12px;color:#94a3b8;">
                                Correo automático — Escaner Total · Gobernación del Meta
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
