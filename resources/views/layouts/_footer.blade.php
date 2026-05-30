@php
    $colClass = ($footerOscuro ?? false) ? 'text-emerald-400' : 'text-emerald-700';
    $beefClass = ($footerOscuro ?? false) ? 'text-red-400' : 'text-red-600';
@endphp
<footer class="{{ $footerClass ?? 'border-t border-zinc-200/80 bg-white/90 py-4 text-center text-xs text-zinc-600 backdrop-blur-sm' }}">
    <p class="font-medium">
        &copy; {{ now()->year }}
        <span class="font-semibold {{ $colClass }}">Col</span><span class="font-semibold {{ $beefClass }}">beef</span>
        — Desarrollado por <strong class="font-bold">Juan Pablo Carreño Mendoza</strong>.
    </p>
</footer>
