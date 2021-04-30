$("body").on("submit", "#iniciarSesion", function(e) {
    e.preventDefault();
    var formdata = new FormData(this);
    $.ajax({
        type: 'POST',
        url: $(this).attr("action"),
        cache: false,
        processData: false,
        contentType: false,
        data: formdata,
        success: function(data) {
            if (data.success) {
                switch (data.rol) {
                    case 2:
                    case 3:
                        window.location.href = '/empleado';
                        break;
                    case 1:
                        window.location.href = '/portal';
                        break;
                }
            } else {
                retornarAlerta(
                    '¡Error!',
                    data.mensaje,
                    'error',
                    'Aceptar'
                );
            }
        },
        error: function(data) {
            const error = data.responseJSON;
            if (error.error_code === 'VALIDATION_ERROR') {
                mostrarErrores(error.errors);
            } else {
                retornarAlerta(
                    data.responseJSON.exception,
                    data.responseJSON.message + ", en la linea: " + data.responseJSON.line,
                    'error',
                    'Aceptar'
                );
                console.log("error");
                console.log(data);
            }
        }
    });
});