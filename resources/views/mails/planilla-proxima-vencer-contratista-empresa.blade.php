<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recordatorio planilla SS contratista</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f4f5;font-family:Arial,Helvetica,sans-serif;color:#18181b;line-height:1.6;">
    @php
        $empresa = $contratista->empresa;
        $limite = $contratista->limiteEfectivo();
    @endphp
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
                            <p style="margin:0 0 16px;font-size:15px;">Estimado(a) representante@if ($empresa) de <strong>{{ $empresa->nombre }}</strong>@endif,</p>

                            @include('mails._alerta_planilla_hito', ['hito' => $hito, 'diasRestantes' => $diasRestantes])

                            <p style="margin:0 0 16px;font-size:15px;">
                                Le informamos que la vigencia de la <strong>planilla de seguridad social</strong> del contratista
                                <strong>{{ $contratista->nombres_apellidos }}</strong> (planilla <strong>independiente</strong>)
                                @if (\App\Support\AlertaPlanillaHito::esVencida($hito))
                                    <strong>ya venció</strong> hace {{ abs($diasRestantes) }} día{{ abs($diasRestantes) === 1 ? '' : 's' }}.
                                @else
                                    se encuentra <strong>próxima a vencer</strong>.
                                @endif
                            </p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:0 0 20px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;">
                                <tr>
                                    <td style="padding:16px 18px;font-size:14px;">
                                        <p style="margin:0 0 8px;"><strong>Contratista:</strong> {{ $contratista->nombres_apellidos }}</p>
                                        <p style="margin:0 0 8px;"><strong>Documento:</strong> {{ $contratista->tipo_documento }} {{ $contratista->numero_documento }}</p>
                                        @if ($empresa)
                                        <p style="margin:0 0 8px;"><strong>Empresa:</strong> {{ $empresa->nombre }}</p>
                                        @endif
                                        <p style="margin:0 0 8px;"><strong>Fecha límite SS:</strong> {{ $limite?->format('d/m/Y') }}</p>
                                        <p style="margin:0;"><strong>Días restantes:</strong> {{ $diasRestantes }} día{{ $diasRestantes === 1 ? '' : 's' }}</p>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 16px;font-size:15px;">
                                Solicitamos gestionar oportunamente la renovación y remitir la nueva planilla de seguridad social
                                al área de SST de Colbeef, o actualizar el registro en el sistema de control de contratistas.
                            </p>

                            <p style="margin:0;font-size:15px;">
                                Atentamente,<br>
                                <strong>Área de Seguridad y Salud en el Trabajo — Colbeef</strong>
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
