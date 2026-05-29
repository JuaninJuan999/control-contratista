<script>
(function () {
    var contenedor = document.getElementById('busqueda-global');
    var input = document.getElementById('busqueda-global-input');
    var panel = document.getElementById('busqueda-global-resultados');

    if (!contenedor || !input || !panel) {
        return;
    }

    var indice = @json($busquedaGlobalIndice ?? []);
    var limite = 15;
    var indiceActivo = -1;
    var panelAbierto = false;

    var etiquetasTipo = {
        empresa: 'Empresa',
        contratista_externo: 'Externo',
        contratista_interno: 'Interno',
        vehiculo: 'Vehículo',
    };

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function normalizar(texto) {
        return (texto || '').toLowerCase().trim();
    }

    function posicionarPanel() {
        var rect = input.getBoundingClientRect();
        panel.style.top = (rect.bottom + 4) + 'px';
        panel.style.left = rect.left + 'px';
        panel.style.width = rect.width + 'px';
    }

    function cerrarPanel() {
        panel.hidden = true;
        panel.classList.add('hidden');
        panel.innerHTML = '';
        indiceActivo = -1;
        panelAbierto = false;
    }

    function abrirPanel() {
        posicionarPanel();
        panel.hidden = false;
        panel.classList.remove('hidden');
        panelAbierto = true;
    }

    function filtrarLocal(q) {
        var termino = normalizar(q);
        var terminoPlaca = termino.replace(/\s+/g, '');

        if (termino.length < 2) {
            return [];
        }

        var coincidencias = [];

        for (var i = 0; i < indice.length; i++) {
            var item = indice[i];
            var buscar = item.buscar || '';

            if (buscar.indexOf(termino) !== -1 || (terminoPlaca && buscar.indexOf(terminoPlaca) !== -1)) {
                coincidencias.push(item);
            }
        }

        return coincidencias;
    }

    function renderResultados(items, total) {
        if (!items.length) {
            panel.innerHTML = '<p class="px-3 py-4 text-center text-xs text-zinc-500">Sin resultados</p>';
            abrirPanel();
            return;
        }

        panel.innerHTML = items.slice(0, limite).map(function (item, i) {
            var tipo = etiquetasTipo[item.tipo] || item.tipo;
            var url = String(item.url || '').replace(/"/g, '&quot;');

            return '<button type="button" role="option" data-indice="' + i + '" data-url="' + url + '" '
                + 'class="busqueda-opcion flex w-full cursor-pointer flex-col gap-0.5 px-3 py-2 text-left hover:bg-emerald-50 focus:bg-emerald-50 focus:outline-none">'
                + '<span class="flex items-center gap-2">'
                + '<span class="truncate text-sm font-medium text-zinc-900">' + escapeHtml(item.label) + '</span>'
                + '<span class="shrink-0 rounded bg-zinc-100 px-1.5 py-0.5 text-[10px] font-bold uppercase text-zinc-600">' + escapeHtml(tipo) + '</span>'
                + '</span>'
                + '<span class="truncate text-xs text-zinc-500">' + escapeHtml(item.sublabel || '') + '</span>'
                + '</button>';
        }).join('');

        if (total > limite) {
            panel.innerHTML += '<p class="border-t border-zinc-100 px-3 py-2 text-center text-[11px] text-zinc-500">'
                + total + ' coincidencias — refine el término para ver menos</p>';
        }

        abrirPanel();
    }

    function buscar() {
        var q = input.value.trim();

        if (q.length < 2) {
            cerrarPanel();
            return;
        }

        var coincidencias = filtrarLocal(q);
        renderResultados(coincidencias, coincidencias.length);
    }

    function irA(url) {
        if (url) {
            window.location.assign(url);
        }
    }

    function irAPrimeraOpcion() {
        var opciones = panel.querySelectorAll('.busqueda-opcion');

        if (indiceActivo >= 0 && opciones.length) {
            irA(opciones[indiceActivo].getAttribute('data-url'));
            return;
        }

        if (opciones.length) {
            irA(opciones[0].getAttribute('data-url'));
        }
    }

    function seleccionarOpcion(btn) {
        if (!btn) {
            return;
        }

        irA(btn.getAttribute('data-url'));
    }

    input.addEventListener('input', buscar);

    input.addEventListener('focus', function () {
        if (input.value.trim().length >= 2) {
            buscar();
        }
    });

    panel.addEventListener('mousedown', function (event) {
        var btn = event.target.closest('.busqueda-opcion');

        if (!btn) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();
        seleccionarOpcion(btn);
    });

    panel.addEventListener('click', function (event) {
        var btn = event.target.closest('.busqueda-opcion');

        if (!btn) {
            return;
        }

        event.preventDefault();
        seleccionarOpcion(btn);
    });

    input.addEventListener('keydown', function (event) {
        var opciones = panel.querySelectorAll('.busqueda-opcion');

        if (event.key === 'Enter') {
            if (opciones.length) {
                event.preventDefault();
                irAPrimeraOpcion();
            }

            return;
        }

        if (!opciones.length) {
            return;
        }

        if (event.key === 'ArrowDown') {
            event.preventDefault();
            indiceActivo = Math.min(indiceActivo + 1, opciones.length - 1);
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            indiceActivo = Math.max(indiceActivo - 1, 0);
        } else if (event.key === 'Escape') {
            cerrarPanel();
            return;
        } else {
            return;
        }

        opciones.forEach(function (op, i) {
            op.classList.toggle('bg-emerald-50', i === indiceActivo);
        });

        if (indiceActivo >= 0) {
            opciones[indiceActivo].scrollIntoView({ block: 'nearest' });
        }
    });

    document.addEventListener('mousedown', function (event) {
        if (contenedor.contains(event.target) || panel.contains(event.target)) {
            return;
        }

        cerrarPanel();
    });

    window.addEventListener('resize', function () {
        if (panelAbierto) {
            posicionarPanel();
        }
    });

    window.addEventListener('scroll', function () {
        if (panelAbierto) {
            posicionarPanel();
        }
    }, true);
})();
</script>
