<form id="form-logout-inactividad" action="{{ route('logout') }}" method="POST" class="hidden">
    @csrf
    <input type="hidden" name="inactividad" value="1">
</form>

<script>
    (function () {
        var limiteMs = {{ (int) config('usabilidad.inactividad_segundos', 900) }} * 1000;
        var form = document.getElementById('form-logout-inactividad');

        if (!form || !limiteMs || limiteMs < 60000) {
            return;
        }

        var timer = null;

        function reiniciar() {
            clearTimeout(timer);
            timer = setTimeout(function () {
                form.submit();
            }, limiteMs);
        }

        ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart', 'click'].forEach(function (evento) {
            document.addEventListener(evento, reiniciar, { passive: true });
        });

        reiniciar();
    })();
</script>
