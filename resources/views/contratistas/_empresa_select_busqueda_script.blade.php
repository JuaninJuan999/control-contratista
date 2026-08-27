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
            var permitirTodas = contenedor.getAttribute('data-permitir-todas') === '1';
            var listaFlotante = contenedor.getAttribute('data-lista-flotante') === '1';
            var listaAbierta = false;

            function emitirCambio() {
                hidden.dispatchEvent(new Event('change', { bubbles: true }));
            }

            function posicionarLista() {
                if (!listaFlotante) {
                    return;
                }

                var rect = input.getBoundingClientRect();
                var minAncho = Math.max(rect.width, 256);
                var maxAncho = Math.min(480, window.innerWidth - 16);
                var ancho = Math.min(Math.max(minAncho, lista.scrollWidth || minAncho), maxAncho);
                var left = rect.left;

                if (left + ancho > window.innerWidth - 8) {
                    left = Math.max(8, window.innerWidth - ancho - 8);
                }

                lista.style.top = (rect.bottom + 4) + 'px';
                lista.style.left = left + 'px';
                lista.style.width = ancho + 'px';
            }

            function anclarListaFlotante() {
                if (!listaFlotante || lista.parentElement === document.body) {
                    return;
                }

                document.body.appendChild(lista);
            }

            function restaurarLista() {
                if (!listaFlotante || lista.parentElement === contenedor) {
                    return;
                }

                contenedor.appendChild(lista);
                lista.style.top = '';
                lista.style.left = '';
                lista.style.width = '';
            }

            function cerrarLista() {
                lista.classList.add('hidden');
                lista.innerHTML = '';
                indiceActivo = -1;
                listaAbierta = false;
                input.setAttribute('aria-expanded', 'false');
                restaurarLista();
            }

            function mostrarLista() {
                if (listaFlotante) {
                    anclarListaFlotante();
                    posicionarLista();
                    requestAnimationFrame(posicionarLista);
                }

                lista.classList.remove('hidden');
                listaAbierta = true;
                input.setAttribute('aria-expanded', 'true');
            }

            function seleccionar(opcion) {
                if (! opcion && ! permitirTodas) {
                    return;
                }

                hidden.value = opcion ? String(opcion.id) : '';
                input.value = opcion ? opcion.nombre : '';
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

                if (permitirTodas) {
                    var itemTodas = document.createElement('li');
                    itemTodas.className = 'cursor-pointer px-3 py-2 text-zinc-600 hover:bg-emerald-50';
                    itemTodas.textContent = 'Todas';
                    itemTodas.setAttribute('role', 'option');
                    itemTodas.dataset.todas = '1';
                    itemTodas.addEventListener('mousedown', function (event) {
                        event.preventDefault();
                        seleccionar(null);
                    });
                    lista.appendChild(itemTodas);
                }

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

                mostrarLista();
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
                        if (items[indiceActivo].dataset.todas === '1') {
                            seleccionar(null);
                        } else {
                            var nombre = items[indiceActivo].textContent;
                            var op = opciones.find(function (o) { return o.nombre === nombre; });
                            seleccionar(op);
                        }
                    }
                } else if (event.key === 'Escape') {
                    cerrarLista();
                }
            });

            document.addEventListener('click', function (event) {
                if (contenedor.contains(event.target) || lista.contains(event.target)) {
                    return;
                }

                cerrarLista();
            });

            window.addEventListener('resize', function () {
                if (listaAbierta) {
                    posicionarLista();
                }
            });

            window.addEventListener('scroll', function () {
                if (listaAbierta) {
                    posicionarLista();
                }
            }, true);

            contenedor.dataset.empresaBusquedaInit = '1';
        };

        document.querySelectorAll('[data-empresa-busqueda]').forEach(window.inicializarEmpresaBusqueda);
    })();
</script>
