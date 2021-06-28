function cargando() {
    if (typeof $("#cargando")[0] !== 'undefined') {
        $("#cargando").css("display", "flex");
    } else {
        $("body").append('<div id="cargando" style="display: flex;">Cargando...</div>');
    }
}
$(document).ready(function() {
    $("#conceptos").DataTable({
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

    $("#addConcepto").click(function(e) {
        e.preventDefault();
        cargando();
        $.ajax({
            type: 'GET',
            url: "/concepto/getForm/add",
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".respForm[data-para='concepto']").html(data);
                $('#conceptoModal').modal('show');
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

    $("body").on("change", "#subTipo", function(e) {
        $(".elementoVariable").removeClass("activo");
        if ($(this).val() == "Tabla") {
            $(".elementoVariable").addClass("activo");
        } else if ($(this).val() == "Formula") {
            cargando();
            var url = "/concepto/getFormulaConcepto/";
            if (typeof $("#idConcepto")[0] !== 'undefined') {
                url = "/concepto/getFormulaConcepto/" + $("#idConcepto").val();
            }
            $.ajax({
                type: 'GET',
                url: url,
                success: function(data) {
                    $("#cargando").css("display", "none");
                    $(".respForm[data-para='formulaConcepto']").html(data);
                    $('#formulaConceptoModal').modal('show');
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
        }
    });

    $("body").on("change", "#msubTipo", function(e) {
        $(".elementoVariable").removeClass("activo");
        if ($(this).val() == "Tabla") {
            $(".elementoVariable").addClass("activo");
        } else if ($(this).val() == "Formula") {
            cargando();
            var url = "/concepto/getFormulaConcepto/" + $(this).attr("data-id");
            $.ajax({
                type: 'GET',
                url: url,
                success: function(data) {
                    $("#cargando").css("display", "none");
                    $(".respForm[data-para='formulaConcepto']").html(data);
                    $('#formulaConceptoModal').modal('show');
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
        }
    });

    $("body").on("click", ".modificarFormula", function(e) {
        e.preventDefault();
        cargando();
        var url = $(this).attr("href");
        $.ajax({
            type: 'GET',
            url: url,
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".respForm[data-para='formulaConcepto']").html(data);
                $('#formulaConceptoModal').modal('show');
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

    $("body").on("click", ".verFormula", function(e) {
        e.preventDefault();
        cargando();
        var url = $(this).attr("href");
        $.ajax({
            type: 'GET',
            url: url,
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".respForm[data-para='formulaConcepto']").html(data);
                $('#formulaConceptoModal').modal('show');
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


    $("body").on("change", "#tipoInicio", function() {

        $(".variableInicial").removeClass("activo");
        $(".valorInicial").removeClass("activo");
        $(".conceptoInicial").removeClass("activo");
        $(".grupoInicial").removeClass("activo");

        $(".variableInicial").addClass("oculto");
        $(".valorInicial").addClass("oculto");
        $(".conceptoInicial").addClass("oculto");
        $(".grupoInicial").addClass("oculto");

        $("." + $(this).val() + "Inicial").addClass("activo");
        $("." + $(this).val() + "Inicial").removeClass("oculto");
    });

    $("body").on("change", ".tipoFin", function() {
        const dataId = $(this).attr("data-id");
        $(".variableFin[data-id='" + dataId + "']").removeClass("activo");
        $(".valorFin[data-id='" + dataId + "']").removeClass("activo");
        $(".conceptoFin[data-id='" + dataId + "']").removeClass("activo");
        $(".grupoFin[data-id='" + dataId + "']").removeClass("activo");


        $(".variableFin[data-id='" + dataId + "']").addClass("oculto");
        $(".valorFin[data-id='" + dataId + "']").addClass("oculto");
        $(".conceptoFin[data-id='" + dataId + "']").addClass("oculto");
        $(".grupoFin[data-id='" + dataId + "']").addClass("oculto");


        $("." + $(this).val() + "Fin[data-id='" + dataId + "']").addClass("activo");
        $("." + $(this).val() + "Fin[data-id='" + dataId + "']").removeClass("oculto");
    });


    $("body").on("click", "#masOperaciones", function(e) {
        e.preventDefault();
        cargando();
        var numOperacion = $("#numOperacion").val();
        numOperacion++;
        var url = "/concepto/getFormulaConcepto/masFormulas/" + numOperacion;

        $.ajax({
            type: 'GET',
            url: url,
            success: function(data) {
                $("#cargando").css("display", "none");

                $("#numOperacion").val(numOperacion)
                $('.respMasOperaciones').append(data);
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

    $("body").on("click", ".editar", function(e) {
        e.preventDefault();
        cargando();
        var url = $(this).attr("href");
        $.ajax({
            type: 'GET',
            url: url,
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".respForm[data-para='concepto']").html(data);
                $('#conceptoModal').modal('show');
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

    $("body").on("click", ".ver", function(e) {
        e.preventDefault();
        cargando();
        var url = $(this).attr("href");
        $.ajax({
            type: 'GET',
            url: url,
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".respForm[data-para='concepto']").html(data);
                $('#conceptoModal').modal('show');
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


    $("body").on("click", ".quitarOperacion", function(e) {
        e.preventDefault();
        $(".operacionAdicional[data-id='" + $(this).attr("data-id") + "']").remove();
    });
    $("body").on("submit", "#formFormulaConcepto", function(e) {
        e.preventDefault();
        cargando();
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
                    $(".respFormulaConcepto").html(data.html);
                    $('#formulaConceptoModal').modal('hide');
                }
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
                    window.location.reload();
                } else {
                    $("#infoErrorForm").css("display", "block");
                    $("#infoErrorForm").html(data.mensaje);
                }
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
    $("body").on("click", ".recargar", function() {
        window.open('/concepto', '_self');
    });

    $("#filtrarEmpleado").submit(function(e) {
        e.preventDefault();
        window.open("/concepto?nombre=" + $("#busc_nombre").val() + "&naturaleza=" + $("#busc_naturaleza").val(), "_self");
    });

});