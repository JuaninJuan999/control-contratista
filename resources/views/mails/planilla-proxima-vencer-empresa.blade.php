<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recordatorio planilla de seguridad social</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f4f5;font-family:Arial,Helvetica,sans-serif;color:#18181b;line-height:1.6;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#f4f4f5;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="max-width:600px;background:#ffffff;border:1px solid #e4e4e7;border-radius:8px;overflow:hidden;">
                    <tr>
                        <td style="background:#047857;padding:20px 28px;">
                            <p style="margin:0;font-size:18px;font-weight:bold;color:#ffffff;">Colbeef — Seguridad y Salud en el Trabajo</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px;">
                            <p style="margin:0 0 16px;font-size:15px;">Estimado(a) representante de <strong>{{ $empresa->nombre }}</strong>,</p>

                            <p style="margin:0 0 16px;font-size:15px;">
                                Por medio del presente, le informamos que la vigencia de la <strong>planilla de seguridad social</strong>
                                asociada a su empresa se encuentra <strong>próxima a vencer</strong>.
                            </p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:0 0 20px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;">
                                <tr>
                                    <td style="padding:16px 18px;font-size:14px;">
                                        <p style="margin:0 0 8px;"><strong>Fecha límite de vigencia:</strong> {{ $empresa->limite->format('d/m/Y') }}</p>
                                        <p style="margin:0 0 8px;"><strong>Periodo:</strong> {{ $empresa->periodoVigenciaEtiqueta() }}</p>
                                        @if ($empresa->nit)
                                        <p style="margin:0 0 8px;"><strong>NIT:</strong> {{ $empresa->nit }}</p>
                                        @endif
                                        <p style="margin:0;"><strong>Días restantes:</strong> {{ $diasRestantes }} día{{ $diasRestantes === 1 ? '' : 's' }}</p>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 16px;font-size:15px;">
                                Con el fin de mantener su registro al día en nuestro sistema de control de contratistas,
                                le solicitamos <strong>comunicarse con el área de Seguridad y Salud en el Trabajo (SST) de Colbeef</strong>
                                y remitir oportunamente la <strong>nueva planilla de seguridad social</strong> correspondiente al próximo periodo de vigencia.
                            </p>

                            <p style="margin:0 0 16px;font-size:15px;">
                                Agradecemos su pronta gestión para evitar inconvenientes en el acceso y continuidad de sus actividades como empresa contratista.
                            </p>

                            <p style="margin:0;font-size:15px;">
                                Atentamente,<br>
                                <strong>Área de Seguridad y Salud en el Trabajo — Colbeef</strong><br>
                                <span style="color:#52525b;font-size:13px;">Sistema de Control de Contratistas</span>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:14px 28px;background:#fafafa;border-top:1px solid #e4e4e7;font-size:11px;color:#71717a;">
                            Este es un mensaje automático. Por favor, no responda directamente a este correo.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
