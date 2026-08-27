@php
    $variant = $variant ?? 'nav';
@endphp

<a
    href="{{ config('app.workbeef_url') }}"
    class="workcolbeef-link {{ $variant === 'login' ? 'workcolbeef-link--login' : 'workcolbeef-link--nav' }}"
    aria-label="Ir a WorkColbeef"
>
    <span class="text-emerald-700">Work</span><span class="text-red-600">Colbeef</span>
</a>
