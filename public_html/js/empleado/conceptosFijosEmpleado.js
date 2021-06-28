function cargando() {
    if (typeof $("#cargando")[0] !== 'undefined') {
        $("#cargando").css("display", "flex");
    } else {
        $("body").append('<div id="cargando" style="display: flex;">Cargando...</div>');
    }
}
$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    var camposOpciones = {
        opcionales: ["conFiFechaFin?", "conFiPorcentaje?", "conFiFechaInicioCambio", "conValorCambio"],
        opcionalesAVeces: []
    };
    $(".separadorMiles").inputmask({ alias: "currency", removeMaskOnSubmit: true });
    $("#formConceptosFijos .btnSubmitGen").click(function(e) {
        e.preventDefault();
        const numeroCampos = $("#formConceptosFijos input, #formConceptosFijos select").length;
        var numeroCamposVacios = 0;
        $.each($("#formConceptosFijos input, #formConceptosFijos select"), function(i, field) {
            var field = $(this);
            var validar = true;
            if (field.prop("id") == "") {
                validar = false;
            } else {
                if (field.val() == "") {
                    if (camposOpciones.opcionales.indexOf(field.prop("id")) === -1) {
                        camposOpciones.opcionales.forEach(opcionSola => {
                            if (opcionSola.indexOf("?")) {
                                var campoPosibleOpcional = opcionSola.substring(0, opcionSola.length - 1);
                                if (field.prop("id").includes(campoPosibleOpcional)) {
                                    validar = false;
                                }

                            }
                        });

                        if (!validar) {
                            camposOpciones.opcionalesAVeces.forEach(opcionalAveces => {
                                if (opcionalAveces.tipoBloqueo == 1) {
                                    if (opcionalAveces.camposQueSonOb.includes(field.prop("id"))) {
                                        opcionalAveces.camposCambia.forEach(campoCambio => {
                                            campoCambio.valorCambia.forEach(valorCambio => {
                                                console.log(valorCambio);
                                                if ($("#" + campoCambio.campo).val() == valorCambio && validar) {
                                                    validar = false;
                                                }
                                            });
                                        });
                                    }
                                } else if (opcionalAveces.tipoBloqueo == 2) {
                                    opcionalAveces.camposQueSonOb.forEach(campoOb => {
                                        var campoPosible = campoOb.substring(0, campoOb.length - 1);
                                        if (field.prop("id").includes(campoPosible)) {
                                            var numeroFilaCampo = field.prop("id").substring(campoPosible.length);
                                            if (numeroFilaCampo > opcionalAveces.minimo) {
                                                var numeroCamposLLenos = 0;
                                                opcionalAveces.camposCambia.forEach(campoCambio => {
                                                    var campoCambioF = campoCambio.substring(0, campoCambio.length - 1);
                                                    campoCambioF = campoCambioF + numeroFilaCampo;
                                                    if ($("#" + campoCambioF).val() != "") {
                                                        numeroCamposLLenos++;
                                                    }
                                                });
                                                if (numeroCamposLLenos == 0) {
                                                    validar = false;
                                                }
                                            }
                                        }
                                    });
                                }
                            });
                        }
                    } else {
                        validar = false;

                    }
                }
            }
            field.parent().removeClass("campoObligatorio");
            if (validar && field.val() == "") {
                field.parent().addClass("campoObligatorio");
                numeroCamposVacios++;
            }
            if ((numeroCampos - 1) == i) {
                if (numeroCamposVacios > 0) {
                    $('#camposVaciosModal').modal('show');
                    $("#btnContinuarCamposVacios").attr("data-accion", "enviarFormulario");
                    $("#btnContinuarCamposVacios").attr("data-form", "#formInfoEmpleado");
                } else {
                    $("#formConceptosFijos").submit();
                }
            }
        });
    });
    $.each($("#formConceptosFijos input, #formConceptosFijos select"), function(i, field) {
        var field = $(this);
        var id = field.prop("id");

        if (id != "") {
            var label = $('label[for=' + id + ']');
            if (camposOpciones.opcionales.indexOf(id) === -1) {
                label.html(label.html() + " *");
            }
        }
    });
    $("body").on("click", ".masConceptosFijos", function() {
        var numAfiliacion = $(this).attr("data-num");
        numAfiliacion++;
        cargando();
        $(this).attr("data-num", numAfiliacion);
        $.ajax({
            type: 'GET',
            url: "/empleado/cargarConceptosFijos/" + numAfiliacion,
            success: function(data) {
                $("#cargando").css("display", "none");

                $(".conceptosCont").append(data);
                $(".separadorMiles").inputmask({ alias: "currency", removeMaskOnSubmit: true });

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

    $("body").on("click", ".quitarConceptoFijo", function(e) {
        e.preventDefault();
        var dataid = $(this).attr("data-id");
        $(this).attr("data-num", dataid);
        $(".conceptoFijo[data-id='" + dataid + "']").remove();

    });
    $("body").on("submit", "#formConceptosFijos", function(e) {
        e.preventDefault();
        cargando();
        $("#continuarIgual").removeClass("activo");
        $("#continuarIgual").attr("data-id", "");
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
                $(".separadorMiles").inputmask({ alias: "currency", removeMaskOnSubmit: true });
                if (data.success) {
                    window.open("/empleado/", "_self");
                } else {
                    if (data.tipoRestriccion == "1") {
                        $("#continuarIgual").addClass("activo");
                        $("#continuarIgual").attr("data-id", data.idcondicion);
                    }

                    $("#respError").html(data.respuesta);
                    $('#errorEmpleadoModal').modal('show');
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

    $("body").on("click", "#continuarIgual", function() {
        if (typeof $("#pasarAlerta")[0] !== 'undefined') {
            $("#pasarAlerta").val($("#pasarAlerta").val() + $(this).attr("data-id") + ",");
        } else {
            $("#formConceptosFijos").append('<input type="hidden" name="pasarAlerta" id="pasarAlerta" value="' + $(this).attr("data-id") + '" >');
        }
        $("#formConceptosFijos").submit();
    });
    $("body").on("click", ".btnCambioConceto", function() {
        var dataid = $(this).attr("data-id");
        $(".cambioConcepto[data-id='" + dataid + "']").addClass("activoFlex");
    });

});