<script>
    (function () {
        function togglePanel(panel, trigger, chevron, expanded) {
            if (!panel || !trigger) return;
            panel.hidden = !expanded;
            panel.classList.toggle('hidden', !expanded);
            trigger.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            if (chevron) {
                chevron.classList.toggle('rotate-90', expanded);
            }
        }

        document.querySelectorAll('[data-contratista-toggle]').forEach(function (fila) {
            fila.addEventListener('click', function (event) {
                if (event.target.closest('[data-acciones-contratista]') || event.target.closest('form') || event.target.closest('a') || event.target.closest('button')) {
                    return;
                }

                var id = fila.getAttribute('data-contratista-toggle');
                var panel = document.querySelector('[data-contratista-panel="' + id + '"]');
                var chevron = fila.querySelector('.contratista-chevron');
                var abierto = fila.getAttribute('aria-expanded') === 'true';

                document.querySelectorAll('[data-contratista-toggle]').forEach(function (otra) {
                    if (otra === fila) return;
                    var otroId = otra.getAttribute('data-contratista-toggle');
                    togglePanel(
                        document.querySelector('[data-contratista-panel="' + otroId + '"]'),
                        otra,
                        otra.querySelector('.contratista-chevron'),
                        false
                    );
                    otra.classList.remove('bg-emerald-50');
                });

                togglePanel(panel, fila, chevron, !abierto);
                fila.classList.toggle('bg-emerald-50', !abierto);
            });
        });
    })();
</script>
