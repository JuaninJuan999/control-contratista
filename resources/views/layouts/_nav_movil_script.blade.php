<script>
    (function () {
        var btn = document.getElementById('nav-movil-btn');
        var panel = document.getElementById('nav-movil-panel');

        if (! btn || ! panel) {
            return;
        }

        function cerrar() {
            panel.classList.add('hidden');
            panel.hidden = true;
            btn.setAttribute('aria-expanded', 'false');
            btn.querySelector('[data-icon="abrir"]').classList.remove('hidden');
            btn.querySelector('[data-icon="cerrar"]').classList.add('hidden');
        }

        btn.addEventListener('click', function () {
            if (! panel.hidden) {
                cerrar();

                return;
            }

            panel.classList.remove('hidden');
            panel.hidden = false;
            btn.setAttribute('aria-expanded', 'true');
            btn.querySelector('[data-icon="abrir"]').classList.add('hidden');
            btn.querySelector('[data-icon="cerrar"]').classList.remove('hidden');
        });

        document.addEventListener('click', function (event) {
            if (panel.hidden) {
                return;
            }

            if (! btn.contains(event.target) && ! panel.contains(event.target)) {
                cerrar();
            }
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth >= 1024) {
                cerrar();
            }
        });
    })();
</script>
