<script>
    (function () {
        if (window.__camposAdicionalesContratistaInit) {
            return;
        }
        window.__camposAdicionalesContratistaInit = true;

        function toggleCamposAdicionales(select) {
            var root = select.closest('.campos-adicionales-contratista');
            if (!root) {
                return;
            }

            if (select.classList.contains('js-manipulador-select')) {
                root.querySelectorAll('.js-manipulador-campos').forEach(function (bloque) {
                    bloque.classList.toggle('hidden', select.value !== '1');
                });
            }

            if (select.classList.contains('js-licencia-select')) {
                root.querySelectorAll('.js-licencia-campos').forEach(function (bloque) {
                    bloque.classList.toggle('hidden', select.value !== '1');
                });
            }
        }

        document.addEventListener('change', function (event) {
            if (event.target.classList.contains('js-manipulador-select') || event.target.classList.contains('js-licencia-select')) {
                toggleCamposAdicionales(event.target);
            }
        });
    })();
</script>
