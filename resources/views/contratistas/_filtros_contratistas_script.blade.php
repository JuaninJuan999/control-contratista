@php
    /** @var string $filtrosTipo  'externo' | 'interno' */
    $filtrosModulo = $filtrosTipo.'s';
@endphp
<script>
    (function () {
        var prefijo = @json($filtrosTipo);
        var modulo = @json($filtrosModulo);

        function id(campo) {
            return 'filtro-' + prefijo + '-' + campo;
        }

        function normalizar(texto) {
            return (texto || '').toLowerCase().trim();
        }

        function documentoNormalizado(texto) {
            return normalizar(texto).replace(/\s+/g, '');
        }

        function hayFiltrosActivos() {
            return normalizar(document.getElementById(id('nombre'))?.value)
                || (document.getElementById(id('tipo-documento'))?.value || '')
                || documentoNormalizado(document.getElementById(id('documento'))?.value)
                || normalizar(document.getElementById(id('arl'))?.value)
                || (document.getElementById(id('estado'))?.value || '');
        }

        function colapsarFila(fila) {
            if (!fila) {
                return;
            }

            var filaId = fila.getAttribute('data-contratista-toggle');
            var panel = document.querySelector('[data-contratista-panel="' + filaId + '"]');
            var chevron = fila.querySelector('.contratista-chevron');

            fila.setAttribute('aria-expanded', 'false');
            fila.classList.remove('bg-emerald-50');

            if (panel) {
                panel.hidden = true;
                panel.classList.add('hidden');
            }

            if (chevron) {
                chevron.classList.remove('rotate-90');
            }
        }

        function aplicarFiltros() {
            var nombre = normalizar(document.getElementById(id('nombre'))?.value);
            var tipoDocumento = document.getElementById(id('tipo-documento'))?.value || '';
            var documento = documentoNormalizado(document.getElementById(id('documento'))?.value);
            var arl = normalizar(document.getElementById(id('arl'))?.value);
            var estado = document.getElementById(id('estado'))?.value || '';
            var visibles = 0;
            var total = 0;

            document.querySelectorAll('tr.contratista-fila[data-filtro-modulo="' + modulo + '"]').forEach(function (fila) {
                total++;
                var coincide = true;

                if (nombre && (fila.getAttribute('data-filtro-nombre') || '').indexOf(nombre) === -1) {
                    coincide = false;
                }
                if (tipoDocumento && fila.getAttribute('data-filtro-tipo-documento') !== tipoDocumento) {
                    coincide = false;
                }
                if (documento && (fila.getAttribute('data-filtro-documento') || '').indexOf(documento) === -1) {
                    coincide = false;
                }
                if (arl && (fila.getAttribute('data-filtro-arl') || '').indexOf(arl) === -1) {
                    coincide = false;
                }
                if (estado && fila.getAttribute('data-filtro-estado') !== estado) {
                    coincide = false;
                }

                var filaId = fila.getAttribute('data-contratista-toggle');
                var panel = document.querySelector('[data-contratista-panel="' + filaId + '"]');

                if (coincide) {
                    fila.classList.remove('hidden');
                    visibles++;
                } else {
                    fila.classList.add('hidden');
                    colapsarFila(fila);
                    if (panel) {
                        panel.classList.add('hidden');
                        panel.hidden = true;
                    }
                }
            });

            var sinResultados = document.getElementById('filtro-' + modulo + '-sin-resultados');
            var resumen = document.getElementById('filtro-' + modulo + '-resumen');
            var btnLimpiar = document.getElementById('btn-limpiar-' + modulo);
            var hayFiltros = hayFiltrosActivos();

            if (sinResultados) {
                sinResultados.classList.toggle('hidden', visibles > 0 || !hayFiltros);
            }

            if (resumen) {
                if (hayFiltros) {
                    resumen.textContent = 'Mostrando ' + visibles + ' de ' + total + ' contratista' + (total === 1 ? '' : 's') + ' en esta página.';
                    resumen.classList.remove('hidden');
                } else {
                    resumen.classList.add('hidden');
                    resumen.textContent = '';
                }
            }

            if (btnLimpiar) {
                btnLimpiar.classList.toggle('hidden', !hayFiltros);
            }
        }

        function limpiarFiltros() {
            ['nombre', 'documento', 'arl'].forEach(function (campo) {
                var input = document.getElementById(id(campo));
                if (input) {
                    input.value = '';
                }
            });

            ['tipo-documento', 'estado'].forEach(function (campo) {
                var select = document.getElementById(id(campo));
                if (select) {
                    select.value = '';
                }
            });

            aplicarFiltros();
        }

        var btnFiltrar = document.getElementById('btn-filtrar-' + modulo);
        var btnLimpiar = document.getElementById('btn-limpiar-' + modulo);

        if (btnFiltrar) {
            btnFiltrar.addEventListener('click', aplicarFiltros);
        }

        if (btnLimpiar) {
            btnLimpiar.addEventListener('click', limpiarFiltros);
        }

        ['nombre', 'documento', 'arl'].forEach(function (campo) {
            var input = document.getElementById(id(campo));
            if (!input) {
                return;
            }
            input.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    aplicarFiltros();
                }
            });
        });

        ['tipo-documento', 'estado'].forEach(function (campo) {
            var select = document.getElementById(id(campo));
            if (!select) {
                return;
            }
            select.addEventListener('change', aplicarFiltros);
        });
    })();
</script>
