function mostrarErrores(errores) {
    eliminarWarnings();
    Object.keys(errores).forEach((i, el) => {
        // i = indice; el = elementoError
        $('label[for="' + i + '"]').addClass('error');
        $('#' + i).addClass('error');
        elemNext = ($('#' + i).attr('type') === 'checkbox') ? $('label[for="' + i + '"]') : $('#' + i);
        $(elemNext).after('<div class="margenArriba_alerta kill_alerta alert alert-danger alert-dismissible fade show" role="alert">' + errores[i] + '<button type="button" class="close ' + i + '" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">&times;</span> </button></div>');
        $(elemNext).bind('click', function() {
            $('label[for="' + i + '"]').removeClass('error');
            $('#' + i).removeClass('error');
            $('button.' + i).trigger('click');
        });
    });
    /* setTimeout(() => {
        $(".close").trigger('click');
    }, 4000); */
}

function eliminarWarnings() {
    $("label").removeClass('error');
    $("input").removeClass('error');
    $("select").removeClass('error');
    $("textarea").removeClass('error');
    $(".kill_alerta").remove();
}