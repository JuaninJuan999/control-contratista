<script>
    (function () {
        if (window.inicializarEmpresaBusqueda) return;

        function normalizar(texto) {
            return (texto || '')
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '');
        }

        window.inicializarEmpresaBusqueda = function (contenedor) {
            if (!contenedor || contenedor.dataset.empresaBusquedaInit === '1') return;

            var input = contenedor.querySelector('[data-empresa-busqueda-input]');
            var hidden = contenedor.querySelector('[data-empresa-busqueda-valor]');
            var lista = contenedor.querySelector('[data-empresa-busqueda-lista]');
            if (!input || !hidden || !lista) return;

            var opciones = [];
            try {
                opciones = JSON.parse(contenedor.getAttribute('data-opciones') || '[]');
            } catch (e) {
                opciones = [];
            }

            var indiceActivo = -1;

            function emitirCambio() {
                hidden.dispatchEvent(new Event('change', { bubbles: true }));
            }

            function cerrarLista() {
                lista.classList.add('hidden');
                lista.innerHTML = '';
                indiceActivo = -1;
                input.setAttribute('aria-expanded', 'false');
            }

            function seleccionar(opcion) {
                if (!opcion) return;
                hidden.value = String(opcion.id);
                input.value = opcion.nombre;
                cerrarLista();
                emitirCambio();
            }

            function renderLista(filtro) {
                var termino = normalizar(filtro);
                var coincidencias = opciones.filter(function (op) {
                    return termino === '' || normalizar(op.nombre).indexOf(termino) !== -1;
                });

                lista.innerHTML = '';
                indiceActivo = -1;

                if (coincidencias.length === 0) {
                    var vacio = document.createElement('li');
                    vacio.className = 'px-3 py-2 text-xs text-zinc-500';
                    vacio.textContent = 'Sin coincidencias';
                    lista.appendChild(vacio);
                } else {
                    coincidencias.forEach(function (op, idx) {
                        var item = document.createElement('li');
                        item.className = 'cursor-pointer px-3 py-2 text-zinc-900 hover:bg-emerald-50';
                        item.textContent = op.nombre;
                        item.setAttribute('role', 'option');
                        item.dataset.indice = String(idx);
                        item.addEventListener('mousedown', function (event) {
                            event.preventDefault();
                            seleccionar(op);
                        });
                        lista.appendChild(item);
                    });
                }

                lista.classList.remove('hidden');
                input.setAttribute('aria-expanded', 'true');
            }

            function resaltarActivo() {
                var items = lista.querySelectorAll('[role="option"]');
                items.forEach(function (el, idx) {
                    el.classList.toggle('bg-emerald-100', idx === indiceActivo);
                });
                if (indiceActivo >= 0 && items[indiceActivo]) {
                    items[indiceActivo].scrollIntoView({ block: 'nearest' });
                }
            }

            input.addEventListener('focus', function () {
                renderLista(input.value);
            });

            input.addEventListener('input', function () {
                hidden.value = '';
                renderLista(input.value);
            });

            input.addEventListener('keydown', function (event) {
                var items = lista.querySelectorAll('[role="option"]');
                if (event.key === 'ArrowDown') {
                    event.preventDefault();
                    if (lista.classList.contains('hidden')) renderLista(input.value);
                    indiceActivo = Math.min(indiceActivo + 1, items.length - 1);
                    resaltarActivo();
                } else if (event.key === 'ArrowUp') {
                    event.preventDefault();
                    indiceActivo = Math.max(indiceActivo - 1, 0);
                    resaltarActivo();
                } else if (event.key === 'Enter') {
                    if (indiceActivo >= 0 && items[indiceActivo]) {
                        event.preventDefault();
                        var nombre = items[indiceActivo].textContent;
                        var op = opciones.find(function (o) { return o.nombre === nombre; });
                        seleccionar(op);
                    }
                } else if (event.key === 'Escape') {
                    cerrarLista();
                }
            });

            document.addEventListener('click', function (event) {
                if (!contenedor.contains(event.target)) {
                    cerrarLista();
                }
            });

            contenedor.dataset.empresaBusquedaInit = '1';
        };

        document.querySelectorAll('[data-empresa-busqueda]').forEach(window.inicializarEmpresaBusqueda);
    })();
</script>
