@php
    use App\Support\AlertaPlanillaHito;
    $esVencida = AlertaPlanillaHito::esVencida($hito ?? '');
    $etiquetaHito = AlertaPlanillaHito::etiqueta($hito ?? '');
@endphp

<p style="margin:0 0 16px;font-size:15px;">
    @if ($esVencida)
        <strong>Tipo de alerta:</strong> {{ $etiquetaHito }}.
        La vigencia venció hace {{ abs($diasRestantes) }} día{{ abs($diasRestantes) === 1 ? '' : 's' }}.
    @else
        <strong>Tipo de alerta:</strong> {{ $etiquetaHito }}.
        Quedan {{ $diasRestantes }} día{{ $diasRestantes === 1 ? '' : 's' }} para la fecha límite.
    @endif
</p>
