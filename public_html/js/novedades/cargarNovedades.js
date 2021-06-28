function cargando() {
    if (typeof $("#cargando")[0] !== 'undefined') {
        $("#cargando").css("display", "flex");
    } else {
        $("body").append('<div id="cargando" style="display: flex;">Cargando...</div>');
    }
}
$(document).ready(function() {
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
    var camposOpciones = {
        opcionales: ["conFiFechaFin?", "conFiPorcentaje?"],
        opcionalesAVeces: []
    };
    $("#formCargarNovedades .btnSubmitGen").click(function(e) {
        e.preventDefault();
        const numeroCampos = $("#formCargarNovedades input, #formCargarNovedades select").length;
        var numeroCamposVacios = 0;
        $.each($("#formCargarNovedades input, #formCargarNovedades select"), function(i, field) {
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
                if (numeroCamposVacios == 0) {
                    $("#formCargarNovedades").submit();
                }
            }
        });
    });

    $.each($("#formCargarNovedades input, #formCargarNovedades select"), function(i, field) {
        var field = $(this);
        var id = field.prop("id");

        if (id != "") {
            var label = $('label[for=' + id + ']');
            if (camposOpciones.opcionales.indexOf(id) === -1) {
                label.html(label.html() + " *");
            }
        }
    });

    $("body").on("change", "#nomina", function() {
        $("#fecha").val("");
        $("#fecha").trigger("change");
        $(".respTipoNomina").html("");
        $(".respNovedades").html("");
        const idNomina = $(this).val();
        if (idNomina != "") {
            cargando();
            $.ajax({
                type: 'GET',
                url: "/nomina/cargarFechaxNomina/" + idNomina,
                success: function(data) {
                    $("#cargando").css("display", "none");
                    $(".respTipoNomina").html("Periodo: " + data.periodoNomina);
                    $("#fecha").val(data.fechaInicioDeseada);
                    $("#fecha").trigger("change");

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

    $("body").on("change", "#tipo_novedad", function() {

        const idTipo_novedad = $(this).val();
        $("#tipo_reporte").html('<option value=""></option>');
        $("#resp_tipoReporte").removeClass("activo");
        desbloquearCampos();
        if (idTipo_novedad != "") {
            cargando();
            $.ajax({
                type: 'GET',
                url: "/novedades/cargarFormxTipoNov/" + idTipo_novedad,
                success: function(data) {
                    $("#cargando").css("display", "none");
                    if (data.tipo == 1) {
                        $("#resp_tipoReporte").addClass("activo");
                        $("#tipo_reporte").html(data.opciones);
                        $("#formCargarNovedades").submit();

                    } else if (data.tipo == 2) {
                        $("#formCargarNovedades").submit();

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
        }
    });


    $("body").on("change", "#tipo_reporte", function() {
        desbloquearCampos();
        $("#formCargarNovedades").submit();

    });

    $("body").on("click", ".recargar", function() {
        cargando();
        $.ajax({
            type: 'GET',
            url: "/empleado/cargarFormEmpleadosxNomina?idNomina=" + $("#nomina").val(),
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".resFormBusEmpleado").html(data);
                $('#busquedaEmpleadoModal').modal('show');
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

    var dataIdEmpleado = 0;

    $("body").on("click", ".busquedaEmpleado", function() {
        cargando();
        dataIdEmpleado = $(this).attr("data-id");
        $.ajax({
            type: 'GET',
            url: "/empleado/cargarFormEmpleadosxNomina?idNomina=" + $("#nomina").val(),
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".resFormBusEmpleado").html(data);
                $('#busquedaEmpleadoModal').modal('show');
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
    $("body").on("click", ".resFormBusEmpleado a.seleccionarEmpleado", function(e) {
        e.preventDefault();
        $("#nombreEmpleado" + dataIdEmpleado).val($(this).html().trim());
        $("#nombreEmpleado" + dataIdEmpleado).trigger("change");
        $("#idEmpleado" + dataIdEmpleado).val($(this).attr("data-id"));

        $('#busquedaEmpleadoModal').modal('hide');

    });
    $("body").on("submit", "#filtrarEmpleado", function(e) {
        e.preventDefault();
        cargando();

        var formdata = $('#filtrarEmpleado').serialize();
        $.ajax({
            type: 'GET',
            url: $(this).attr("action"),
            data: formdata,
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".resFormBusEmpleado").html(data);
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

    $("body").on("submit", "#filtrarCodigo", function(e) {
        e.preventDefault();
        cargando();

        var formdata = $('#filtrarCodigo').serialize();

        $.ajax({
            type: 'GET',
            url: $(this).attr("action"),
            data: formdata,
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".resFormBusCodDiagnostico").html(data);
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
    var dataIdCodDiag = 0;
    $("body").on("click", ".busquedaCodDiagnostico", function() {
        cargando();
        dataIdCodDiag = $(this).attr("data-id");
        $.ajax({
            type: 'GET',
            url: "/varios/codigosDiagnostico",
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".resFormBusCodDiagnostico").html(data);
                $('#busquedaCodDiagnosticoModal').modal('show');
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

    $("body").on("click", ".resFormBusCodDiagnostico .pagination a", function(e) {
        e.preventDefault();
        cargando();
        $.ajax({
            type: 'GET',
            url: $(this).attr("href"),
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".resFormBusCodDiagnostico").html(data);
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

    $("body").on("click", ".resFormBusEmpleado .pagination a", function(e) {
        e.preventDefault();
        cargando();
        $.ajax({
            type: 'GET',
            url: $(this).attr("href"),
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".resFormBusEmpleado").html(data);
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

    $("body").on("click", ".resFormBusCodDiagnostico a.seleccionarCodigo", function(e) {
        e.preventDefault();
        $("#codigoDiagnostico" + dataIdCodDiag).val($(this).html().trim());
        $("#codigoDiagnostico" + dataIdCodDiag).trigger("change");
        $("#idCodigoDiagnostico" + dataIdCodDiag).val($(this).attr("data-id"));

        $('#busquedaCodDiagnosticoModal').modal('hide');

    });


    $("body").on("change", ".concepto", function(e) {
        var concepto = $(this).val();
        const dataIdConcepto = $(this).attr("data-id");
        if ($(".tipoAfiliacion").length > 0) {
            e.preventDefault();
            cargando();
            $.ajax({
                type: 'GET',
                url: '/novedades/tipoAfiliacionxConcepto/' + $("#tipo_novedad").val() + '/' + concepto,
                success: function(data) {
                    $("#cargando").css("display", "none");
                    $("#tipoAfiliacion" + dataIdConcepto).val(data.actividad);
                    $("#tipoAfiliacion" + dataIdConcepto).trigger("change");
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
    $("body").on("change", ".tipoAfiliacion", function(e) {
        var tipoAfiliacion = $(this).val();
        const dataIdTipoAfiliacion = $(this).attr("data-id");
        var idEmpleado = $(".idEmpleado[data-id='" + dataIdTipoAfiliacion + "']").val();
        if ($("#terceroEntidad" + dataIdTipoAfiliacion).length > 0) {
            e.preventDefault();
            cargando();
            $.ajax({
                type: 'GET',
                url: '/novedades/entidadxTipoAfiliacion/' + tipoAfiliacion + '/' + idEmpleado,
                success: function(data) {
                    $("#cargando").css("display", "none");
                    $("#terceroEntidad" + dataIdTipoAfiliacion).val(data.nombreTercero);
                    $("#idTerceroEntidad" + dataIdTipoAfiliacion).val(data.idTercero);
                    if ($("#tipoAfiliacion" + dataIdTipoAfiliacion).val() == "3") {
                        $("#naturaleza" + dataIdTipoAfiliacion).val("Enfermedad General o Maternidad");
                        $("#naturaleza" + dataIdTipoAfiliacion).trigger("change");
                    }

                    $("#terceroEntidad" + dataIdTipoAfiliacion).trigger("change");
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

    $("body").on("submit", "#formDatosNovedad", function(e) {
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
                if ($.isEmptyObject(data.error)) {
                    if (data.success) {
                        alert("Novedad ingresada exitosamente");
                        window.location.reload();
                    }
                } else {
                    $(".print-error-msg-DatosNovedad").find("ul").html('');
                    $(".print-error-msg-DatosNovedad").css('display', 'block');
                    $.each(data.error, function(key, value) {
                        $(".print-error-msg-DatosNovedad").find("ul").append('<li>' + value + '</li>');
                    });

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

    $("body").on("change", ".fechaRetiro", function() {
        var dataid = $(this).attr("data-id");
        $("#fechaRetiroReal" + dataid).val($(this).val());
        $('#fechaRetiroReal' + dataid).trigger("blur");
    });

    var dataIdIncap = 0;
    $("body").on("change", ".fechaInicial", function() {
        dataIdIncap = $(this).attr("data-id");
        cambiarFechaFinal();
    });

    $("body").on("change", ".dias", function() {
        dataIdIncap = $(this).attr("data-id");
        cambiarFechaFinal();
    });

    function cambiarFechaFinal() {
        var fecha = new Date($("#fechaInicial" + dataIdIncap).val());
        fecha.setDate(fecha.getDate() + parseInt($("#dias" + dataIdIncap).val()));
        var dias = ("0" + fecha.getDate()).slice(-2);
        var meses = ("0" + (fecha.getMonth() + 1)).slice(-2);

        var fechaTxt = fecha.getFullYear() + "-" + (meses) + "-" + (dias);

        $('#fechaFinal' + dataIdIncap).val(fechaTxt);
        $('#fechaFinal' + dataIdIncap).trigger("blur");

        $('#fechaRealI' + dataIdIncap).val($("#fechaInicial" + dataIdIncap).val());
        $('#fechaRealI' + dataIdIncap).trigger("blur");

        $('#fechaRealF' + dataIdIncap).val(fechaTxt);
        $('#fechaRealF' + dataIdIncap).trigger("blur");
    }

    var dataIdVac = 0;
    $("body").on("change", ".fechaInicialVaca", function() {
        dataIdVac = $(this).attr("data-id");
        cambiarFechaFinalConCalendario();
    });

    $("body").on("change", ".diasVaca", function() {
        dataIdVac = $(this).attr("data-id");
        cambiarFechaFinalConCalendario();
    });

    $("body").on("change", ".formVacaciones input[name='idEmpleado[]']", function() {
        dataIdVac = $(this).attr("data-id");
        cambiarFechaFinalConCalendario();
    });

    function cambiarFechaFinalConCalendario() {
        cargando();
        $.ajax({
            type: 'GET',
            url: '/novedades/fechaConCalendario/?fecha=' + $("#fechaInicialVaca" + dataIdVac).val() + "&dias=" + $("#diasVaca" + dataIdVac).val() + "&idEmpleado=" + $("#idEmpleado" + dataIdVac).val(),
            cache: false,
            success: function(data) {
                $("#cargando").css("display", "none");
                if (data.success) {

                    $('#fechaFinalVaca' + dataIdVac).val(data.fecha);
                    $('#fechaFinalVaca' + dataIdVac).trigger("blur");
                    $("#diasCalendario" + dataIdVac).val(data.diasCalendario);
                    $("#diasCal" + dataIdVac).html("Días calendario: " + data.diasCalendario);

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
    }



    $("body").on("submit", "#formCargarNovedades", function(e) {
        e.preventDefault();
        cargando();
        desbloquearCampos();
        $(".masNovedades").removeClass("activo");
        $(".resetNovedades").removeClass("activo");
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

                if (data != "") {
                    bloquearCampos();
                    $(".masNovedades").addClass("activo");
                    $(".resetNovedades").addClass("activo");
                }
                if ($("#idRow").val() == 0) {
                    $(".respNovedades").append(data);
                } else {
                    $(".contAdicional").append(data);
                }

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

    $(".eliminar").click(function(e) {
        if (confirm("En verdad desea eliminar esta novedad?")) {
            e.preventDefault();
            cargando();
            $.ajax({
                type: 'GET',
                url: '/novedades/eliminarNovedad/' + $(this).attr("data-id"),
                success: function(data) {
                    $("#cargando").css("display", "none");
                    if (data.success) {
                        window.location.reload();
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
        }


    });
    $("body").on("submit", "#formEliminarNovedades", function(e) {
        if (confirm("En verdad desea eliminar estas novedades?")) {
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
                        alert("Novedades eliminadas correctamente");
                        window.location.reload();
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
        }

    });

    $("body").on("click", ".masNovedades", function(e) {
        desbloquearCampos();
        $("#idRow").val(parseInt($("#idRow").val()) + 1);
        $("#formCargarNovedades").submit();
    })


    $(".resetNovedades").click(function() {
        $(".respNovedades").html("");
        desbloquearCampos();
        $(".masNovedades").removeClass("activo");
        $(".resetNovedades").removeClass("activo");

        $("#idRow").val(0);
    });

    $(".recargarPage").click(function() {
        window.open("/novedades/listaNovedades/", "_self");
    });

    $("body").on("click", ".quitarNovedadAdicional", function(e) {
        const dataid = $(this).attr("data-id");
        $(".novedadAdicional[data-id='" + dataid + "']").remove();
    })


    function bloquearCampos() {
        $("#empresa").prop("disabled", true);
        $("#nomina").prop("disabled", true);
        $("#fecha").prop("disabled", true);
        $("#tipo_novedad").prop("disabled", true);
        $("#tipo_reporte").prop("disabled", true);

    }

    function desbloquearCampos() {
        $("#empresa").prop("disabled", false);
        $("#nomina").prop("disabled", false);
        $("#fecha").prop("disabled", false);
        $("#tipo_novedad").prop("disabled", false);
        $("#tipo_reporte").prop("disabled", false);
    }
    $("body").on("change", "#empresa", function(e) {
        e.preventDefault();
        const idEmpresa = $(this).val();
        $("#nomina").html('<option value=""></option>');
        $("#nomina").trigger("change");
        if (idEmpresa != "") {
            cargando();
            $.ajax({
                type: 'GET',
                url: "/empleado/cargarDatosPorEmpresa/" + idEmpresa,
                success: function(data) {
                    $("#cargando").css("display", "none");
                    $("#nomina").html(data.opcionesNomina);
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
});