function cargando() {
    if (typeof $("#cargando")[0] !== 'undefined') {
        $("#cargando").css("display", "flex");
    } else {
        $("body").append('<div id="cargando" style="display: flex;">Cargando...</div>');
    }
}
$(document).ready(function() {
    $('#terceros').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.22/i18n/Spanish.json'
        }
    });
    $(document).on('show.bs.modal', '.modal', function(event) {
        var zIndex = 1040 + (10 * $('.modal:visible').length);
        $(this).css('z-index', zIndex);
        setTimeout(function() {
            $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
        }, 0);
    });
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $("body").on("click", ".quitarConcepto", function(e) {
        e.preventDefault();
        $(".conceptoMasCont[data-id='" + $(this).attr("data-id") + "']").remove();
    });


    /* $("body").on("click", "#masConceptos", function(e) {
        e.preventDefault();
        cargando();
        var numConceptos = $("#numConceptos").val();
        numConceptos++;
        var url = "/grupoConcepto/getForm/masConceptos/" + numConceptos;

        $.ajax({
            type: 'GET',
            url: url,
            success: function(data) {
                $("#cargando").css("display", "none");

                $("#numConceptos").val(numConceptos)
                $('.conceptosCont').append(data);
            },
            error: function(data) {
                $("#cargando").css("display", "none");
                retornarAlerta(
                    data.responseJSON.exception,
                    data.responseJSON.message + ", en la linea: " + data.responseJSON.line,
                    'error',
                    'Aceptar'
                );
                console.log("error");
                console.log(data);
            }
        });
    }); */



    $("#addTercero").click(function(e) {
        e.preventDefault();
        cargando();
        $.ajax({
            type: 'GET',
            url: "/terceros/getForm/add",
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".respForm[data-para='terceros']").html(data);
                $('#tercerosModal').modal('show');
            },
            error: function(data) {
                $("#cargando").css("display", "none");
                retornarAlerta(
                    data.responseJSON.exception,
                    data.responseJSON.message + ", en la linea: " + data.responseJSON.line,
                    'error',
                    'Aceptar'
                );
                console.log("error");
                console.log(data);
            }
        });
    });

    $("body").on("submit", ".formGen", function(e) {
        e.preventDefault();
        cargando();
        $(".close").trigger('click');
        var formdata = new FormData(this);
        const privado = $("#privado").is(":checked") ? 1 : 0;
        formdata.append('privado', privado);
        $.ajax({
            type: 'POST',
            url: $(this).attr("action"),
            cache: false,
            processData: false,
            contentType: false,
            data: formdata,
            success: function(data) {
                $("#cargando").css("display", "none");
                if (data.success) {
                    retornarAlerta(
                        '¡Hecho!',
                        data.mensaje,
                        'success',
                        'Aceptar'
                    );
                    window.location.reload();
                } else {
                    $("#infoErrorForm").css("display", "block");
                    $("#infoErrorForm").html(data.mensaje);
                }
            },
            error: function(data) {
                $("#cargando").css("display", "none");
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

    $(".editar").click((e) => {
        e.preventDefault();
        const idTercero = e.target.attributes.dataId.value;
        cargando();
        $.ajax({
            type: 'GET',
            url: `/terceros/datosTerceroXId/${idTercero}`,
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".respForm[data-para='terceros']").html(data);
                $('#tercerosModal').modal('show');
            },
            error: function(data) {
                $("#cargando").css("display", "none");
                retornarAlerta(
                    data.responseJSON.exception,
                    data.responseJSON.message + ", en la linea: " + data.responseJSON.line,
                    'error',
                    'Aceptar'
                );
                console.log("error");
                console.log(data);
            }
        });
    });

    $("body").on("submit", ".formEdit", function(e) {
        e.preventDefault();
        cargando();
        $(".close").trigger('click');
        const idEditar = $("#idTercero").val();
        var formdata = new FormData(this);
        const privado = $("#privado").is(":checked") ? 1 : 0;
        formdata.append('privado', privado);
        $.ajax({
            type: 'POST',
            url: $(this).attr("action") + '/' + idEditar,
            cache: false,
            processData: false,
            contentType: false,
            data: formdata,
            success: function(data) {
                $("#cargando").css("display", "none");
                if (data.success) {
                    retornarAlerta(
                        '¡Hecho!',
                        data.mensaje,
                        'success',
                        'Aceptar'
                    );
                    window.location.reload();
                } else {
                    $("#infoErrorForm").css("display", "block");
                    $("#infoErrorForm").html(data.mensaje);
                }
            },
            error: function(data) {
                $("#cargando").css("display", "none");
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

    $("body").on("click", ".detalle", (e) => {
        e.preventDefault();
        cargando();
        const idTercero = e.target.attributes.dataId.value;
        $.ajax({
            type: 'GET',
            url: `/terceros/detalleTercero/${idTercero}`,
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".respForm[data-para='terceros']").html(data);
                $('#tercerosModal').modal('show');
            },
            error: function(data) {
                $("#cargando").css("display", "none");
                retornarAlerta(
                    data.responseJSON.exception,
                    data.responseJSON.message + ", en la linea: " + data.responseJSON.line,
                    'error',
                    'Aceptar'
                );
                console.log("error");
                console.log(data);
            }
        });
    });

    $("body").on("click", ".eliminar", (e) => {
        const confirmar = confirm('¿Está seguro de realizar esta acción?');
        e.preventDefault();
        if (confirmar) {
            cargando();
            const idTercero = e.target.attributes.dataId.value;
            $.ajax({
                type: 'POST',
                url: `/terceros/eliminarTercero/${idTercero}`,
                cache: false,
                processData: false,
                contentType: false,
                success: function(data) {
                    $("#cargando").css("display", "none");
                    if (data.success) {
                        window.location.reload();
                    } else {
                        $("#infoErrorForm").css("display", "block");
                        $("#infoErrorForm").html(data.mensaje);
                    }
                },
                error: function(data) {
                    $("#cargando").css("display", "none");
                    console.log(data);
                }
            });
        }
    });

    $("body").on("click", ".adicional_ubis", (e) => {
        let idDom = e.target.attributes.dataId.value;
        idDom = parseInt(idDom);
        idDom++;
        e.target.attributes.dataId.value = idDom;
        $.ajax({
            type: 'GET',
            url: `/terceros/ubiTercero/newUbi/${idDom}`,
            cache: false,
            processData: false,
            contentType: false,
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".ubicaciones").append(data);
            },
            error: function(data) {
                $("#cargando").css("display", "none");
                console.log(data);
            }
        });
    });
    $("body").on("click", ".elim_ub", (e) => {
        const id = e.target.attributes['data-id'].value;
        $(`.ubicacion_${id}`).remove();
    });
});

function traerDOMUbicaciones() {
    $.ajax({
        type: 'GET',
        url: `/terceros/ubiTercero/0/${idTercero}/2/${idUbi}`,
        cache: false,
        processData: false,
        contentType: false,
        success: function(data) {
            $("#cargando").css("display", "none");
            $(".ubicaciones").append(data);
        },
        error: function(data) {
            $("#cargando").css("display", "none");
            console.log(data);
        }
    });
}