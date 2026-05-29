<script>
    (function () {
        var fechaInput = document.getElementById('fecha_ultima_ir');
        var vigenciaInput = document.getElementById('vigencia_dias');
        var pv = document.getElementById('preview-vencimiento');
        var pd = document.getElementById('preview-dias');
        var pe = document.getElementById('preview-estado');
        if (!fechaInput || !vigenciaInput || !pv || !pd || !pe) return;

        function stripTime(d) { return new Date(d.getFullYear(), d.getMonth(), d.getDate()); }
        function addDays(date, days) { var d = stripTime(date); d.setDate(d.getDate() + days); return d; }
        function formatDMY(d) { return d.getDate() + '/' + (d.getMonth() + 1) + '/' + d.getFullYear(); }

        function refresh() {
            var iso = fechaInput.value;
            var vig = parseInt(vigenciaInput.value, 10);
            if (!iso || !vig || vig < 1) {
                pv.textContent = '—'; pd.textContent = '—'; pe.textContent = '—'; pe.className = 'mt-0.5 font-bold';
                return;
            }
            var parts = iso.split('-');
            if (parts.length !== 3) return;
            var inicio = stripTime(new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2])));
            var venc = addDays(inicio, vig);
            var hoy = stripTime(new Date());
            var diffDias = Math.round((venc.getTime() - hoy.getTime()) / 86400000);
            pv.textContent = formatDMY(venc);
            pd.textContent = String(diffDias);
            if (diffDias >= 0) { pe.textContent = 'VIGENTE'; pe.className = 'mt-0.5 font-bold text-emerald-700'; }
            else { pe.textContent = 'VENCIDA'; pe.className = 'mt-0.5 font-bold text-red-700'; }
        }

        fechaInput.addEventListener('change', refresh);
        fechaInput.addEventListener('input', refresh);
        vigenciaInput.addEventListener('input', refresh);
        refresh();
    })();
</script>
