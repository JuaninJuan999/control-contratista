@php
    $variant = $variant ?? 'nav';
@endphp

@if ($variant === 'login')
    <a
        href="{{ config('app.workbeef_url') }}"
        class="inline-flex items-center rounded-xl border border-white/80 bg-white/95 px-5 py-2.5 text-base font-bold shadow-[0_0_0_1px_rgba(16,185,129,0.4),0_0_16px_rgba(16,185,129,0.25),0_0_16px_rgba(220,38,38,0.2)] backdrop-blur-md transition hover:border-emerald-200 hover:bg-white hover:shadow-[0_0_0_2px_rgba(16,185,129,0.55),0_0_22px_rgba(16,185,129,0.4),0_0_22px_rgba(220,38,38,0.3)]"
        aria-label="Ir a WorkColbeef"
    >
        <span class="text-emerald-700">Work</span><span class="text-red-600">Col</span><span class="text-red-600">beef</span>
    </a>
@else
    <a
        href="{{ config('app.workbeef_url') }}"
        class="shrink-0 rounded-lg px-2 py-1.5 font-semibold transition hover:bg-zinc-100 md:px-3 md:py-2"
    >
        <span class="text-emerald-700">Work</span><span class="text-red-600">Col</span><span class="text-red-600">beef</span>
    </a>
@endif
