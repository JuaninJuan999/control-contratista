<script>
    (function () {
        if ('scrollRestoration' in history) {
            history.scrollRestoration = 'manual';
        }

        var scrollKey = 'cc-scroll-' + window.location.pathname;
        var scrollRestaurado = false;
        var scrollPendiente = null;

        function restaurarScrollGuardado() {
            var guardado = sessionStorage.getItem(scrollKey);
            if (guardado === null) {
                return false;
            }

            sessionStorage.removeItem(scrollKey);
            var y = parseInt(guardado, 10);
            if (isNaN(y)) {
                return false;
            }

            scrollRestaurado = true;
            scrollPendiente = y;

            function aplicarScroll() {
                if (scrollPendiente === null) {
                    return;
                }
                window.scrollTo(0, scrollPendiente);
            }

            aplicarScroll();
            requestAnimationFrame(aplicarScroll);
            window.addEventListener('load', aplicarScroll, { once: true });

            return true;
        }

        document.addEventListener('submit', function (event) {
            var form = event.target;
            if (!(form instanceof HTMLFormElement)) {
                return;
            }

            if (!form.closest('tr.contratista-fila')) {
                return;
            }

            sessionStorage.setItem(scrollKey, String(window.scrollY));
        }, true);

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

        (function abrirDesdeUrl() {
            restaurarScrollGuardado();

            var params = new URLSearchParams(window.location.search);
            var abrir = params.get('abrir');
            if (!abrir) return;

            setTimeout(function () {
                var fila = document.querySelector('[data-contratista-toggle="' + abrir + '"]');
                if (!fila) return;

                if (!scrollRestaurado) {
                    fila.scrollIntoView({ block: 'center', behavior: 'instant' in window ? 'instant' : 'auto' });
                }

                if (fila.getAttribute('aria-expanded') !== 'true') {
                    fila.click();
                }

                setTimeout(function () {
                    if (window.resaltarFilaBusqueda && !scrollRestaurado) {
                        window.resaltarFilaBusqueda(fila);
                    } else if (scrollRestaurado) {
                        fila.classList.add('busqueda-resaltado');
                    }
                }, 80);
            }, scrollRestaurado ? 0 : 120);
        })();
    })();
</script>
