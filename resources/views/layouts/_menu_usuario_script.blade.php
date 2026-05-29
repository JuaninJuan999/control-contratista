<script>
(function () {
    var contenedor = document.getElementById('menu-usuario');
    var boton = document.getElementById('menu-usuario-btn');
    var panel = document.getElementById('menu-usuario-panel');

    if (!contenedor || !boton || !panel) {
        return;
    }

    function cerrarMenu() {
        panel.hidden = true;
        panel.classList.add('hidden');
        boton.setAttribute('aria-expanded', 'false');
    }

    function abrirMenu() {
        panel.hidden = false;
        panel.classList.remove('hidden');
        boton.setAttribute('aria-expanded', 'true');
    }

    function alternarMenu() {
        if (panel.hidden) {
            abrirMenu();
        } else {
            cerrarMenu();
        }
    }

    boton.addEventListener('click', function (event) {
        event.stopPropagation();
        alternarMenu();
    });

    document.addEventListener('mousedown', function (event) {
        if (!contenedor.contains(event.target)) {
            cerrarMenu();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            cerrarMenu();
        }
    });
})();
</script>
