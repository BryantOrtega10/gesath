function solicitudAjax(url, tipo, parametros, funcionRta, funcionError) {
    if (typeof parametros === 'undefined' && parametros == null) {
        parametros = new FormData();
    }
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        type: tipo,
        url: url,
        data: parametros,
        cache: false,
        processData: false,
        contentType: false,
        success: (data) => {
            funcionRta(data);
        },
        error: (data) => {
            if (typeof funcionRta !== 'undefined' && parametros != null) {
                funcionError(data);
            } else {
                console.log('Error');
                console.log(data);
            }
        }
    });
}