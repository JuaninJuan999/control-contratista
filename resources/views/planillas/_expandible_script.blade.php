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

        document.querySelectorAll('[data-planilla-toggle]').forEach(function (fila) {
            fila.addEventListener('click', function (event) {
                if (event.target.closest('form') || event.target.closest('a') || event.target.closest('button') || event.target.closest('select') || event.target.closest('input')) {
                    return;
                }

                var id = fila.getAttribute('data-planilla-toggle');
                var panel = document.querySelector('[data-planilla-panel="' + id + '"]');
                var chevron = fila.querySelector('.planilla-chevron');
                var abierto = fila.getAttribute('aria-expanded') === 'true';

                document.querySelectorAll('[data-planilla-toggle]').forEach(function (otra) {
                    if (otra === fila) return;
                    var otroId = otra.getAttribute('data-planilla-toggle');
                    togglePanel(
                        document.querySelector('[data-planilla-panel="' + otroId + '"]'),
                        otra,
                        otra.querySelector('.planilla-chevron'),
                        false
                    );
                    otra.classList.remove('bg-emerald-50');
                });

                togglePanel(panel, fila, chevron, !abierto);
                fila.classList.toggle('bg-emerald-50', !abierto);
            });
        });

        (function abrirDesdeUrl() {
            var params = new URLSearchParams(window.location.search);
            var abrir = params.get('abrir');
            if (!abrir) return;

            setTimeout(function () {
                var fila = document.querySelector('[data-planilla-toggle="' + abrir + '"]');
                if (!fila) return;
                if (fila.getAttribute('aria-expanded') !== 'true') {
                    fila.click();
                }
                fila.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }, 120);
        })();
    })();
</script>
