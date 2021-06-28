$("body").on("submit", "#iniciarSesion", function(e) {
    e.preventDefault();
    const aceptoTermino = $("#aceptoTermino")[0].checked;
    if (aceptoTermino) {
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
    } else {
        retornarAlerta(
            '¡Error!',
            "Debes aceptar la política de tratamiento y protección de datos personales",
            'error',
            'Aceptar'
        );
    }
});
$("body").on("click", ".ojo-password", function(e) {

    if ($("#" + $(this).attr("data-para")).prop("type") == "password") {
        $(this).find("i").removeClass("fa-eye");
        $(this).find("i").addClass("fa-eye-slash");
        $("#" + $(this).attr("data-para")).prop("type", "text");
    } else {
        $(this).find("i").removeClass("fa-eye-slash");
        $(this).find("i").addClass("fa-eye");
        $("#" + $(this).attr("data-para")).prop("type", "password");
    }
});