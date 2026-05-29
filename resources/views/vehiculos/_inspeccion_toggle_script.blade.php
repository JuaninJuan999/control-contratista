<script>
    (function () {
        if (window.__inspeccionSanitariaToggleInit) {
            return;
        }
        window.__inspeccionSanitariaToggleInit = true;

        document.addEventListener('change', function (event) {
            if (!event.target.classList || !event.target.classList.contains('js-inspeccion-select')) {
                return;
            }
            var root = event.target.closest('[data-inspeccion-root]');
            if (!root) {
                return;
            }
            root.querySelectorAll('.js-inspeccion-campos').forEach(function (bloque) {
                bloque.classList.toggle('hidden', event.target.value !== '1');
            });
        });
    })();
</script>
