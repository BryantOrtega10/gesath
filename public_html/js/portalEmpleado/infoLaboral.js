$(document).ready(() => {

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

    $("body").on("click", ".certificado_laboral", (e) => {
        $("#certificadoLaboral").submit();
    });

    $("body").on("click", ".info_laboral", (e) => {
        const idUsu = $("#idUsu").val();
        solicitudAjax(`/portal/infoLaboral/${idUsu}`, 'GET', null,
            (data) => {
                $(".respForm[data-para='portalEmple']").html(data);
                $('#portalEmpleModal').modal('show');
            }, (err) => {
                console.log(err);
            }
        );
    });

    $("body").on("click", ".vacaciones_emple", (e) => {
        const idUsu = $("#idUsu").val();
        solicitudAjax(`/portal/diasVacacionesDisponibles/${idUsu}`, 'GET', null,
            (data) => {
                $(".respForm[data-para='portalEmple']").html(data);
                $('#portalEmpleModal').modal('show');
            }, (err) => {
                console.log(err);
            }
        );
    });

    $("body").on("click", ".perfil_emple", (e) => {
        const idUsu = $("#idUsu").val();
        solicitudAjax(`/portal/datosEmple/${idUsu}`, 'GET', null,
            (data) => {
                $(".respForm[data-para='portalEmple']").html(data);
                $('#portalEmpleModal').modal('show');
            }, (err) => {
                console.log(err);
            }
        );
    });

    $("body").on("click", ".certificado_dosveinte", (e) => {
        const idUsu = $("#idUsu").val();
        solicitudAjax(`/portal/traerFormularios220`, 'GET', null,
            (data) => {
                $(".respForm[data-para='portalEmple']").html(data);
                $('#portalEmpleModal').modal('show');
                const fechaActual = moment(new Date(), 'YYYY-MM-DD');
                const fechaDiaHoy = fechaActual.format('YYYY-MM-DD');
                $('#empresa').val($('.certificado_dosveinte').attr('data-idempresa'));
                $('#infoNomina').val($('.certificado_dosveinte').attr('data-nomina'));
                $('#idEmpleado').val($('.certificado_dosveinte').attr('data-idempleado'));
                $('#fechaExp').val(fechaDiaHoy);
                $('#reporte').val('PDF');
                $('#agenteRetenedor').val($('.certificado_dosveinte').attr('data-agente'));
            }, (err) => {
                console.log(err);
            }
        );
    });

    $("body").on("click", ".comprobantes_pago", (e) => {
        const idEmpleado = $(".comprobantes_pago").attr('data-idempleado');
        solicitudAjax(`/portal/vistaComprobantes/${idEmpleado}`, 'GET', null,
            (data) => {
                $(".respForm[data-para='portalEmple']").html(data);
                $('#portalEmpleModal').modal('show');
            }, (err) => {
                console.log(err);
            }
        );
    });

    $("body").on("submit", ".formPerfilEmple", (e) => {
        e.preventDefault();
        const idUsu = $("#idEmpleado").val();
        const formData = new FormData($('.formPerfilEmple')[0]);
        solicitudAjax(`/portal/ediDatosEmple/${idUsu}`, 'POST', formData,
            (data) => {
                if (data.success) {
                    retornarAlerta(
                        '¡Hecho!',
                        data.mensaje,
                        'success',
                        'Aceptar'
                    );
                } else {
                    retornarAlerta(
                        '¡Error!',
                        data.mensaje,
                        'error',
                        'Aceptar'
                    );
                }
                $('#portalEmpleModal').modal('hide');
            }, (err) => {
                console.log(err);
            }
        );
    });

    $("body").on("click", ".cambiar_pass", (e) => {
        e.preventDefault();
        const idUsu = $("#idUsu").val();
        solicitudAjax(`/portal/getVistaPass/${idUsu}`, 'GET', null,
            (data) => {
                $(".respForm[data-para='portalEmple']").html(data);
                $('#portalEmpleModal').modal('show');
            }, (err) => {
                $("#cargando").css("display", "none");
                retornarAlerta(
                    err.responseJSON.exception,
                    err.responseJSON.message + ", en la linea: " + err.responseJSON.line,
                    'error',
                    'Aceptar'
                );
                console.log("error");
                console.log(err);
            }
        );
    });

    $("body").on("submit", ".formActPass", (e) => {
        e.preventDefault();
        const idEmple = $("#idEmple").val();
        const formData = new FormData($(".formActPass")[0]);
        solicitudAjax(`/portal/cambiarContrasenia/${idEmple}`, 'POST', formData,
            (data) => {
                if (data.success) {
                    retornarAlerta(
                        '¡Hecho!',
                        data.mensaje,
                        'success',
                        'Aceptar'
                    );
                    $('#portalEmpleModal').modal('toggle');
                } else {
                    retornarAlerta(
                        '¡Error!',
                        data.mensaje,
                        'error',
                        'Aceptar'
                    );
                }
            }, (err) => {
                const error = err.responseJSON;
                if (error.error_code === 'VALIDATION_ERROR') {
                    mostrarErrores(error.errors);
                } else {
                    $("#cargando").css("display", "none");
                    retornarAlerta(
                        err.responseJSON.exception,
                        err.responseJSON.message + ", en la linea: " + err.responseJSON.line,
                        'error',
                        'Aceptar'
                    );
                    console.log("error");
                    console.log(err);
                }
            }
        );
    });

    $("body").on("change", "#pais", (e) => {
        const idUbi = $("#pais option:selected").val();
        traerUbicacionesFk('#deptos', idUbi);
    });

    $("body").on("change", "#deptos", (e) => {
        const idUbi = $("#deptos option:selected").val();
        traerUbicacionesFk('#fkUbicacion', idUbi);
    });
});

function traerUbicacionesFk(domAppend, idUbi) {
    solicitudAjax(`/ubicaciones/obtenerHijos/${idUbi}`, 'GET', null,
        (data) => {
            console.log(data);
            $(domAppend).empty();
            $(domAppend).append(data.opciones);
        }, (err) => {
            console.log(error);
        }
    );
}