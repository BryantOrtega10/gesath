$(document).ready(() => {
    let errorTit;
    let ic;
    $("body").on("submit", "#solicitar_rec_pass", (e) => {
        e.preventDefault();
        const formData = new FormData($("#solicitar_rec_pass")[0]);
        solicitudAjax(`/enviar_correo_rec_pass`, 'POST', formData,
            (data) => {
                if (!data.success) {
                    errorTit = '¡Error!';
                    ic = 'error';
                } else {
                    errorTit = '¡Hecho!';
                    ic = 'success';
                }
                retornarAlerta(
                    errorTit,
                    data.mensaje,
                    ic,
                    'Aceptar'
                );
                // window.location.href = "/";
            }, (err) => {
                retornarAlerta(
                    '¡Error!',
                    data.mensaje,
                    'error',
                    'Aceptar'
                );
                console.log(err);
            }
        );
    });

    $("body").on("submit", "#act_pass", function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        solicitudAjax(`/act_pass`, 'POST', formData,
            (data) => {
                if (!data.success) {
                    errorTit = '¡Error!';
                    ic = 'error';
                } else {
                    errorTit = '¡Hecho!';
                    ic = 'success';
                }
                retornarAlerta(
                    errorTit,
                    data.mensaje,
                    ic,
                    'Aceptar'
                );
                // window.location.href = "/";
            }, (err) => {
                const error = err.responseJSON;
                if (error.error_code === 'VALIDATION_ERROR') {
                    mostrarErrores(error.errors);
                } else {
                    retornarAlerta(
                        err.responseJSON.exception,
                        data.responseJSON.message + ", en la linea: " + err.responseJSON.line,
                        'err',
                        'Aceptar'
                    );
                    console.log("error");
                    console.log(err);
                }
            }
        );
    });
});