function cargando() {
    if (typeof $("#cargando")[0] !== 'undefined') {
        $("#cargando").css("display", "flex");
    } else {
        $("body").append('<div id="cargando" style="display: flex;">Cargando...</div>');
    }
}
$(document).ready(function() {
    $("#usuarios").DataTable({
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

    $("#addUsuario").click(function(e) {
        e.preventDefault();
        cargando();
        $.ajax({
            type: 'GET',
            url: "/usuarios/getFormAdd",
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".respForm[data-para='usuarios']").html(data);
                $('#usuariosModal').modal('show');
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
                    retornarAlerta(
                        '¡Error!',
                        data.mensaje,
                        'error',
                        'Aceptar'
                    );
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
        const idUsuario = e.target.attributes.dataId.value;
        cargando();
        $.ajax({
            type: 'GET',
            url: `/usuarios/datosUsuarioXId/${idUsuario}`,
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".respForm[data-para='usuarios']").html(data);
                $('#usuariosModal').modal('show');
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
        var formdata = new FormData(this);
        if ($("#password").val() == "") {
            formdata.delete("password");
        }
        $.ajax({
            type: 'POST',
            url: `${$(this).attr("action")}`,
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
                    retornarAlerta(
                        '¡Error!',
                        data.mensaje,
                        'error',
                        'Aceptar'
                    );
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
        const idUsuario = e.target.attributes.dataId.value;
        $.ajax({
            type: 'GET',
            url: `/usuarios/detalleUsuario/${idUsuario}`,
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".respForm[data-para='usuarios']").html(data);
                $('#usuariosModal').modal('show');
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
        cargando();
        const idUsuario = e.target.attributes.dataId.value;
        $.ajax({
            type: 'POST',
            url: `/usuarios/eliminarUsuario/${idUsuario}`,
            cache: false,
            processData: false,
            contentType: false,
            success: function(data) {
                if (data.success) {
                    window.location.reload();
                } else {
                    $("#infoErrorForm").css("display", "block");
                    $("#infoErrorForm").html(data.mensaje);
                }
            },
            error: function(data) {
                console.log(data);
            }
        });
    });

    $("body").on("click", ".hab_deshab", (e) => {
        const confirmar = confirm('¿Está seguro de realizar esta acción?');
        e.preventDefault();
        cargando();
        const idUsuario = e.target.attributes.dataId.value;
        const estAct = e.target.attributes.dataActivo.value;
        $.ajax({
            type: 'POST',
            url: `/usuarios/habDesHabUsu/${idUsuario}/${estAct}`,
            cache: false,
            processData: false,
            contentType: false,
            success: function(data) {
                if (data.success) {
                    window.location.reload();
                } else {
                    $("#infoErrorForm").css("display", "block");
                    $("#infoErrorForm").html(data.mensaje);
                }
            },
            error: function(data) {
                console.log(data);
            }
        });
    });

    $(".cambiar_pass").click((e) => {
        e.preventDefault();
        const idUsuario = e.target.attributes.dataId.value;
        cargando();
        $.ajax({
            type: 'GET',
            url: `/usuarios/getVistaPass/${idUsuario}`,
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".respForm[data-para='usuarios']").html(data);
                $('#usuariosModal').modal('show');
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

    $("body").on("submit", ".formActPass", function(e) {
        e.preventDefault();
        cargando();
        $(".close").trigger('click');
        var formdata = new FormData(this);
        $.ajax({
            type: 'POST',
            url: `${$(this).attr("action")}`,
            cache: false,
            processData: false,
            contentType: false,
            data: formdata,
            success: function(data) {
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


    $("body").on("change", "#fkRol", (e) => {
        if ($("#fkRol option:selected").val() == "2") {
            $(".cont_empresas").addClass("activo");
            $(".cont_permisos").addClass("activo");
        } else {
            $(".cont_empresas").removeClass("activo");
            $(".cont_permisos").removeClass("activo");
        }
    });

    $("body").on("click", ".addEmpresa", function(e) {
        e.preventDefault();
        var empresas = $("#numEmpresa").val();
        var url = $(this).attr("href") + "/" + empresas;
        empresas++;

        cargando();
        $.ajax({
            type: 'GET',
            url: url,
            success: function(data) {
                $("#cargando").css("display", "none");
                $("#numEmpresa").val(empresas);
                $(".cont_empresas_add").append(data);
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
    $("body").on("click", ".quitarEmpresa", function(e) {
        e.preventDefault();
        $(".filaEmpresa[data-id='" + $(this).attr("data-id") + "']").remove();
    });

    $("body").on("change", ".permisos_lv1 input[type='checkbox']", function(e) {
        $(this).parent().parent().find($("input[type='checkbox']")).prop("checked", $(this).prop("checked"));
    });

});