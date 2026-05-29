<script>
    window.resaltarFilaBusqueda = function (elemento) {
        if (!elemento) {
            return;
        }

        elemento.classList.add('busqueda-resaltado');
        elemento.scrollIntoView({ behavior: 'smooth', block: 'center' });
    };
</script>
