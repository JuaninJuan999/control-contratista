<script>
    (function () {
        if (window.__camposAdicionalesContratistaInit) {
            return;
        }
        window.__camposAdicionalesContratistaInit = true;

        function toggleCamposAdicionales(select) {
            var root = select.closest('.campos-adicionales-contratista');
            if (!root) {
                return;
            }

            if (select.classList.contains('js-manipulador-select')) {
                root.querySelectorAll('.js-manipulador-campos').forEach(function (bloque) {
                    bloque.classList.toggle('hidden', select.value !== '1');
                });
            }

            if (select.classList.contains('js-licencia-select')) {
                root.querySelectorAll('.js-licencia-campos').forEach(function (bloque) {
                    bloque.classList.toggle('hidden', select.value !== '1');
                });
            }
        }

        function actualizarEstadoLicencia(fila) {
            var fechaInput = fila.querySelector('.js-licencia-cat-fecha');
            var badge = fila.querySelector('.js-licencia-cat-estado');

            if (!fechaInput || !badge) {
                return;
            }

            var fecha = fechaInput.value;

            badge.classList.remove('hidden', 'bg-emerald-100', 'text-emerald-800', 'bg-red-100', 'text-red-800');

            if (!fecha) {
                badge.classList.add('hidden');
                badge.textContent = '';

                return;
            }

            var hoy = new Date();
            hoy.setHours(0, 0, 0, 0);
            var vencimiento = new Date(fecha + 'T00:00:00');
            var vigente = vencimiento >= hoy;

            badge.textContent = vigente ? 'VIGENTE' : 'VENCIDA';
            badge.classList.add(vigente ? 'bg-emerald-100' : 'bg-red-100');
            badge.classList.add(vigente ? 'text-emerald-800' : 'text-red-800');
        }

        function panelVencimientos(root) {
            return root ? root.querySelector('.js-licencia-vencimientos-panel') : null;
        }

        function filaVencimiento(panel, categoria) {
            return panel ? panel.querySelector('.js-licencia-vencimiento-item[data-categoria="' + categoria + '"]') : null;
        }

        function actualizarVacio(panel) {
            if (!panel) {
                return;
            }

            var vacio = panel.querySelector('.js-licencia-vencimientos-vacio');
            if (!vacio) {
                return;
            }

            var visibles = panel.querySelectorAll('.js-licencia-vencimiento-item:not(.hidden)');

            vacio.classList.toggle('hidden', visibles.length > 0);
        }

        function toggleFilaLicencia(checkbox) {
            var root = checkbox.closest('.campos-adicionales-contratista');
            var panel = panelVencimientos(root);
            var fila = filaVencimiento(panel, checkbox.getAttribute('data-categoria'));

            if (!fila) {
                return;
            }

            var fechaInput = fila.querySelector('.js-licencia-cat-fecha');
            var badge = fila.querySelector('.js-licencia-cat-estado');

            if (checkbox.checked) {
                fila.classList.remove('hidden');
                actualizarEstadoLicencia(fila);
                actualizarVacio(panel);

                return;
            }

            fila.classList.add('hidden');

            if (fechaInput) {
                fechaInput.value = '';
            }

            if (badge) {
                badge.classList.add('hidden');
                badge.textContent = '';
            }

            actualizarVacio(panel);
        }

        document.querySelectorAll('.js-licencia-vencimiento-item').forEach(function (fila) {
            actualizarEstadoLicencia(fila);
        });

        document.addEventListener('change', function (event) {
            if (event.target.classList.contains('js-manipulador-select') || event.target.classList.contains('js-licencia-select')) {
                toggleCamposAdicionales(event.target);
            }

            if (event.target.classList.contains('js-licencia-cat-check')) {
                toggleFilaLicencia(event.target);
            }
        });

        document.addEventListener('input', function (event) {
            if (event.target.classList.contains('js-licencia-cat-fecha')) {
                var fila = event.target.closest('.js-licencia-vencimiento-item');
                if (fila) {
                    actualizarEstadoLicencia(fila);
                }
            }
        });
    })();
</script>
