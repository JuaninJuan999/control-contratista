<script>
    (function () {
        function normalizar(texto) {
            return (texto || '').toLowerCase().trim().replace(/\s+/g, '');
        }

        function hayFiltrosActivos() {
            return normalizar(document.getElementById('filtro-vehiculo-placa')?.value)
                || (document.getElementById('filtro-vehiculo-empresa')?.value || '')
                || (document.getElementById('filtro-vehiculo-soat')?.value || '')
                || (document.getElementById('filtro-vehiculo-tecnomecanica')?.value || '')
                || (document.getElementById('filtro-vehiculo-inspeccion')?.value || '');
        }

        function aplicarFiltrosVehiculos() {
            var placa = normalizar(document.getElementById('filtro-vehiculo-placa')?.value);
            var empresa = document.getElementById('filtro-vehiculo-empresa')?.value || '';
            var soat = document.getElementById('filtro-vehiculo-soat')?.value || '';
            var tecno = document.getElementById('filtro-vehiculo-tecnomecanica')?.value || '';
            var inspeccion = document.getElementById('filtro-vehiculo-inspeccion')?.value || '';
            var visibles = 0;
            var total = 0;

            document.querySelectorAll('tr.vehiculo-fila').forEach(function (fila) {
                total++;
                var coincide = true;

                if (placa && (fila.getAttribute('data-filtro-placa') || '').indexOf(placa) === -1) {
                    coincide = false;
                }
                if (empresa && fila.getAttribute('data-filtro-empresa') !== empresa) {
                    coincide = false;
                }
                if (soat && fila.getAttribute('data-filtro-soat') !== soat) {
                    coincide = false;
                }
                if (tecno && fila.getAttribute('data-filtro-tecnomecanica') !== tecno) {
                    coincide = false;
                }
                if (inspeccion && fila.getAttribute('data-filtro-inspeccion') !== inspeccion) {
                    coincide = false;
                }

                if (coincide) {
                    fila.classList.remove('hidden');
                    visibles++;
                } else {
                    fila.classList.add('hidden');
                }
            });

            var sinResultados = document.getElementById('filtro-vehiculos-sin-resultados');
            var resumen = document.getElementById('filtro-vehiculos-resumen');
            var btnLimpiar = document.getElementById('btn-limpiar-vehiculos');
            var hayFiltros = hayFiltrosActivos();

            if (sinResultados) {
                sinResultados.classList.toggle('hidden', visibles > 0 || !hayFiltros);
            }

            if (resumen) {
                if (hayFiltros) {
                    resumen.textContent = 'Mostrando ' + visibles + ' de ' + total + ' vehículo' + (total === 1 ? '' : 's') + ' en esta página.';
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

        function limpiarFiltrosVehiculos() {
            var placa = document.getElementById('filtro-vehiculo-placa');
            if (placa) {
                placa.value = '';
            }

            ['filtro-vehiculo-empresa', 'filtro-vehiculo-soat', 'filtro-vehiculo-tecnomecanica', 'filtro-vehiculo-inspeccion'].forEach(function (id) {
                var campo = document.getElementById(id);
                if (campo) {
                    campo.value = '';
                }
            });

            aplicarFiltrosVehiculos();
        }

        var btnFiltrar = document.getElementById('btn-filtrar-vehiculos');
        var btnLimpiar = document.getElementById('btn-limpiar-vehiculos');

        if (btnFiltrar) {
            btnFiltrar.addEventListener('click', aplicarFiltrosVehiculos);
        }

        if (btnLimpiar) {
            btnLimpiar.addEventListener('click', limpiarFiltrosVehiculos);
        }

        var placaInput = document.getElementById('filtro-vehiculo-placa');
        if (placaInput) {
            placaInput.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    aplicarFiltrosVehiculos();
                }
            });
        }

        ['filtro-vehiculo-empresa', 'filtro-vehiculo-soat', 'filtro-vehiculo-tecnomecanica', 'filtro-vehiculo-inspeccion'].forEach(function (id) {
            var campo = document.getElementById(id);
            if (!campo) {
                return;
            }
            campo.addEventListener('change', aplicarFiltrosVehiculos);
        });
    })();
</script>
