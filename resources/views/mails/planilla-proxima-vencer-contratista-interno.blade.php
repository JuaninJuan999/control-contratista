<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Alerta interna planilla SS independiente</title>
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
                        <td style="background:#b45309;padding:20px 28px;">
                            <p style="margin:0;font-size:18px;font-weight:bold;color:#ffffff;">Alerta interna — Planilla SS independiente</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px;">
                            <p style="margin:0 0 16px;font-size:15px;">Equipo de Seguridad y Salud en el Trabajo,</p>

                            @include('mails._alerta_planilla_hito', ['hito' => $hito, 'diasRestantes' => $diasRestantes])

                            <p style="margin:0 0 16px;font-size:15px;">
                                El contratista interno <strong>{{ $contratista->nombres_apellidos }}</strong>
                                @if (\App\Support\AlertaPlanillaHito::esVencida($hito))
                                    tiene la <strong>planilla de seguridad social independiente vencida</strong>.
                                @else
                                    tiene la <strong>planilla de seguridad social independiente próxima a vencer</strong>.
                                @endif
                            </p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:0 0 20px;background:#fffbeb;border:1px solid #fde68a;border-radius:6px;">
                                <tr>
                                    <td style="padding:16px 18px;font-size:14px;">
                                        <p style="margin:0 0 8px;"><strong>Contratista:</strong> {{ $contratista->nombres_apellidos }}</p>
                                        <p style="margin:0 0 8px;"><strong>Documento:</strong> {{ $contratista->tipo_documento }} {{ $contratista->numero_documento }}</p>
                                        @if ($empresa)
                                        <p style="margin:0 0 8px;"><strong>Empresa:</strong> {{ $empresa->nombre }}</p>
                                        @if ($empresa->nit)
                                        <p style="margin:0 0 8px;"><strong>NIT:</strong> {{ $empresa->nit }}</p>
                                        @endif
                                        @endif
                                        <p style="margin:0 0 8px;"><strong>Tipo planilla:</strong> Independiente</p>
                                        <p style="margin:0 0 8px;"><strong>Fecha límite SS:</strong> {{ $limite?->format('d/m/Y') }}</p>
                                        <p style="margin:0 0 8px;"><strong>Días restantes:</strong> {{ $diasRestantes }} día{{ $diasRestantes === 1 ? '' : 's' }}</p>
                                        <p style="margin:0;">
                                            <strong>Estado planilla actual:</strong>
                                            @if ($contratista->planillaSsAlDia())
                                                Adjuntada en el sistema
                                            @else
                                                Pendiente de adjuntar o vencida
                                            @endif
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            @if ($empresa && is_array($empresa->correos) && count($empresa->correos) > 0)
                            <p style="margin:0 0 8px;font-size:14px;"><strong>Correos de contacto de la empresa:</strong></p>
                            <ul style="margin:0 0 16px;padding-left:20px;font-size:14px;">
                                @foreach ($empresa->correos as $correo)
                                    <li>{{ $correo }}</li>
                                @endforeach
                            </ul>
                            @endif

                            <p style="margin:0;font-size:14px;">
                                <a href="{{ route('contratistas-internos.edit', $contratista) }}" style="color:#047857;font-weight:bold;">
                                    Editar contratista interno en el sistema
                                </a>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:14px 28px;background:#fafafa;border-top:1px solid #e4e4e7;font-size:11px;color:#71717a;">
                            Alerta generada automáticamente por {{ config('app.name') }}.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
