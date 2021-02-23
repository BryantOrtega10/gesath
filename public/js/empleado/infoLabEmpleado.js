$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });




    var camposOpciones = {
        opcionales: ["password", "infoOtroDocumento", "infoLocalidad"],
        opcionalesAVeces: [{
                camposQueSonOb: ["infoEntidadFinanciera", "infoNoCuenta", "infoTipoCuenta"],
                camposCambia: [{ campo: "infoFormaPago", valorCambia: ["Cheque", "Efectivo", "Otra forma pago"] }],
                tipoBloqueo: 1
            },
            {
                camposQueSonOb: ["infoOtraFormaPago"],
                camposCambia: [{ campo: "infoFormaPago", valorCambia: ["Cheque", "Efectivo", "Transferencia"] }],
                tipoBloqueo: 1
            },
            {
                camposQueSonOb: ["infoFechaFin", "infoTipoDuracionContrato", "infoDuracionContrato"],
                camposCambia: [{ campo: "infoTipoContrato", valorCambia: ["2"] }],
                tipoBloqueo: 1
            },
            {
                camposQueSonOb: ["infoFechaFinN", "infoTipoDuracionContratoN", "infoDuracionContratoN"],
                camposCambia: [{ campo: "infoTipoContratoN", valorCambia: ["2"] }],
                tipoBloqueo: 1
            },
            {
                camposQueSonOb: ["infoPorcentajeRetencion"],
                camposCambia: [{ campo: "infoProcedimientoRetencion", valorCambia: ["TABLA"] }],
                tipoBloqueo: 1
            },
            {
                camposQueSonOb: ["infoPersonaVive?", "infoTIdentificacion?", "infoNumIdentificacion?"],
                camposCambia: [{ campo: "infoTipoBeneficio?", valorCambia: ["1", "2", "3", "5"] }],
                tipoBloqueo: 3
            },
            {
                camposQueSonOb: ["info2PersonaVive?", "info2TIdentificacion?", "info2NumIdentificacion?", "info2Genero?", "info2DireccionPersona?", "info2PaisPersona?", "info2DeptoPersona?", "info2LugarPersona?"],
                camposCambia: [{ campo: "infoTipoBeneficio?", valorCambia: ["1", "2", "3", "4"] }],
                tipoBloqueo: 3
            }

        ]
    };

    $.each($("#formInfoEmpleado input, #formInfoEmpleado select"), function(i, field) {
        var field = $(this);
        var id = field.prop("id");

        if (id != "") {
            var label = $('label[for=' + id + ']');
            if (camposOpciones.opcionales.indexOf(id) === -1) {
                label.html(label.html() + " *");
            }
        }
    });
    $("#formInfoEmpleado .btnSubmitGen").click(function(e) {
        e.preventDefault();
        const numeroCampos = $("#formInfoEmpleado input, #formInfoEmpleado select").length;
        var numeroCamposVacios = 0;
        $.each($("#formInfoEmpleado input, #formInfoEmpleado select"), function(i, field) {
            var field = $(this);
            var validar = true;
            if (field.prop("id") == "") {
                validar = false;
            } else {
                if (field.val() == "") {
                    if (camposOpciones.opcionales.indexOf(field.prop("id")) === -1) {
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
                            } else if (opcionalAveces.tipoBloqueo == 3) {
                                opcionalAveces.camposQueSonOb.forEach(campoOb => {
                                    var campoPosible = campoOb.substring(0, campoOb.length - 1);
                                    if (field.prop("id").includes(campoPosible)) {
                                        var numeroFilaCampo = field.prop("id").substring(campoPosible.length);

                                        opcionalAveces.camposCambia.forEach(campoCambio => {
                                            campoCambio.valorCambia.forEach(valorCambio => {
                                                let idCampo = campoCambio.campo.substring(0, campoCambio.campo.length - 1)
                                                if ($("#" + idCampo + "" + numeroFilaCampo).val() == valorCambio && validar) {
                                                    validar = false;
                                                }
                                            });
                                        });


                                    }
                                });
                            }
                        });
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
                    $("#formInfoEmpleado").submit();
                }
            }
        });
    });



    $("body").on("click", ".masCentroCosto", function() {
        cargando();
        var numCentro = $(this).attr("data-num");
        numCentro++;
        var idEmpresa = $("#infoEmpresa").val();
        if (idEmpresa == "") {
            alert("Selecciona primero una empresa");
        } else {
            $(this).attr("data-num", numCentro);
            $.ajax({
                type: 'GET',
                url: "/empleado/cargarCentroCosto?num=" + numCentro + "&empresa=" + idEmpresa,
                success: function(data) {
                    $("#cargando").css("display", "none");
                    $(".centroCostoCont").append(data);
                    const numeroCentros = $(".centroCosto").length;
                    let porcentajeCadaUno = Math.floor(100 / numeroCentros);
                    let verificacion = 0;
                    $.each($(".centroCosto input"), function(i, field) {
                        $(field).val(porcentajeCadaUno + "%");
                        verificacion += porcentajeCadaUno;
                        if (verificacion != 100 && i == (numeroCentros - 1)) {
                            $("#infoPorcentaje1").val((porcentajeCadaUno + 100 - verificacion) + "%");
                        }
                    });
                },
                error: function(data) {
                    console.log("error");
                    console.log(data);
                }
            });
        }
    });
    $("body").on("click", ".quitarCentroCosto", function(e) {
        e.preventDefault();
        var dataid = $(this).attr("data-id");
        $(this).attr("data-num", dataid);
        $(".centroCosto[data-id='" + dataid + "']").remove();
        const numeroCentros = $(".centroCosto").length;
        let porcentajeCadaUno = Math.floor(100 / numeroCentros);
        let verificacion = 0;
        $.each($(".centroCosto input"), function(i, field) {
            $(field).val(porcentajeCadaUno + "%");
            verificacion += porcentajeCadaUno;
            if (verificacion != 100 && i == (numeroCentros - 1)) {
                $("#infoPorcentaje1").val((porcentajeCadaUno + 100 - verificacion) + "%");
            }
        });
    });
    $("body").on("click", ".masBeneficiosTributarios", function() {
        var numBeneficio = $(this).attr("data-num");
        var idEmpleado = $(this).attr("data-idEmpleado");
        numBeneficio++;
        cargando();
        $(this).attr("data-num", numBeneficio);
        $.ajax({
            type: 'GET',
            url: "/empleado/cargarBeneficiosTributarios/" + numBeneficio + "/" + idEmpleado,
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".beneficiosCont").append(data);
                $(".separadorMiles").inputmask({ alias: "currency", removeMaskOnSubmit: true });

            },
            error: function(data) {
                console.log("error");
                console.log(data);
            }
        });
    });

    $("body").on("click", ".quitarBeneficio", function(e) {
        e.preventDefault();
        var dataid = $(this).attr("data-id");
        $(this).attr("data-num", dataid);
        $(".beneficioTrib[data-id='" + dataid + "']").remove();

    });

    $("body").on("change", "#infoEmpresa", function(e) {
        e.preventDefault();

        $("#infoNomina").html('<option value=""></option>');
        $("#infoNomina").trigger("change");
        $("#infoCentroCosto1").html('<option value=""></option>');
        $("#infoCentroCosto1").trigger("change");


        const idEmpresa = $(this).val();
        if (idEmpresa != "") {
            cargando();



            $.ajax({
                type: 'GET',
                url: "/empleado/cargarDatosPorEmpresa/" + idEmpresa,
                success: function(data) {
                    $("#cargando").css("display", "none");
                    $("#infoNomina").html(data.opcionesNomina);
                    $("#infoCentroCosto1").html(data.opcionesCentroCosto);
                    $("#infoPorcentaje1").val("100%");
                    $("#infoUsuario").val($("#numIdentificacion").val() + data.dominio);


                    $.each($(".centroCosto"), function(i, field) {
                        if (i != 0) {
                            $(field).remove();
                        }
                    });





                },
                error: function(data) {
                    console.log("error");
                    console.log(data);
                }
            });
        }


    });

    $("body").on("change", "#infoPaisLabora", function() {
        cargando();
        var valorUbicacion = $(this).val();
        $("#infoDeptoLabora").html('<option value=""></option>');
        $("#infoLugarLabora").html('<option value=""></option>');
        $("#infoDeptoLabora").trigger("change");
        $("#infoLugarLabora").trigger("change");
        $("#infoLocalidad").html('<option value=""></option>');
        $("#infoLocalidad").trigger("change");
        if (valorUbicacion != "") {
            $.ajax({
                type: 'GET',
                url: "/ubicaciones/obtenerHijos/" + valorUbicacion,
                success: function(data) {
                    $("#cargando").css("display", "none");
                    $("#infoDeptoLabora").html(data.opciones);
                },
                error: function(data) {
                    console.log("error");
                    console.log(data);
                }
            });
        }
    });



    $("body").on("change", "#infoDeptoLabora", function() {
        cargando();
        var valorUbicacion = $(this).val();
        $("#infoLugarLabora").html('<option value=""></option>');
        $("#infoLugarLabora").trigger("change");
        $("#infoLocalidad").html('<option value=""></option>');
        $("#infoLocalidad").trigger("change");
        if (valorUbicacion != "") {
            $.ajax({
                type: 'GET',
                url: "/ubicaciones/obtenerHijos/" + valorUbicacion,
                success: function(data) {
                    $("#cargando").css("display", "none");
                    $("#infoLugarLabora").html(data.opciones);
                },
                error: function(data) {
                    console.log("error");
                    console.log(data);
                }
            });
        }
    });

    $("body").on("change", "#infoLugarLabora", function() {
        cargando();
        var valorUbicacion = $(this).val();
        $("#infoLocalidad").html('<option value=""></option>');
        $("#infoLocalidad").trigger("change");

        if (valorUbicacion != "") {
            $.ajax({
                type: 'GET',
                url: "/ubicaciones/obtenerHijos/" + valorUbicacion,
                success: function(data) {
                    $("#cargando").css("display", "none");
                    $("#infoLocalidad").html(data.opciones);
                },
                error: function(data) {
                    console.log("error");
                    console.log(data);
                }
            });
        }
    });
    $("body").on("change", "#infoFormaPago", function() {
        $(".tipoPagoTransferencia").removeClass("activoFlex");
        $(".tipoPagoOtro").removeClass("activoFlex");
        if ($(this).val() == "Transferencia") {
            $(".tipoPagoTransferencia").addClass("activoFlex");
        } else if ($(this).val() == "Otra forma pago") {
            $(".tipoPagoOtro").addClass("activoFlex");
        }
    });
    $("body").on("change", "#infoProcedimientoRetencion", function() {
        $(".porcentajeRetencion").removeClass("activo");
        if ($(this).val() == "PORCENTAJE") {
            $(".porcentajeRetencion").addClass("activo");
        }

    });




    $("body").on("change", "#infoDuracionContrato", function() {
        cambiarFechaFinalContrato();
    });

    $("body").on("change", "#infoFechaIngreso", function() {
        cambiarFechaFinalContrato();
    });

    $("body").on("change", "#infoTipoDuracionContrato", function() {
        cambiarFechaFinalContrato();
    });





    function cambiarFechaFinalContrato() {
        let duracion = $("#infoDuracionContrato").val();
        let tipo = $("#infoTipoDuracionContrato").val();
        let fechaInicio = $("#infoFechaIngreso").val();
        if (duracion != "" && tipo != "" && fechaInicio != "") {
            let fechaFin = new Date(fechaInicio);
            duracion = parseInt(duracion);
            if (tipo == "MES") {
                fechaFin.setMonth(fechaFin.getMonth() + duracion);

            } else {
                fechaFin.setDate(fechaFin.getDate() + duracion);
            }
            var dias = ("0" + fechaFin.getDate()).slice(-2);
            var meses = ("0" + (fechaFin.getMonth() + 1)).slice(-2);

            var fechaTxt = fechaFin.getFullYear() + "-" + (meses) + "-" + (dias);

            $('#infoFechaFin').val(fechaTxt);
            $('#infoFechaFin').trigger("blur");

        }
    }


    $("body").on("change", "#infoDuracionContratoN", function() {
        cambiarFechaFinalContratoN();
    });
    $("body").on("change", "#infoFechaFin", function() {
        cambiarFechaFinalContratoN();
    });
    $("body").on("change", "#infoTipoDuracionContratoN", function() {
        cambiarFechaFinalContratoN();
    });

    function cambiarFechaFinalContratoN() {
        let duracion = $("#infoDuracionContratoN").val();
        let tipo = $("#infoTipoDuracionContratoN").val();
        let fechaInicio = $("#infoFechaFin").val();
        if (duracion != "" && tipo != "" && fechaInicio != "") {
            let fechaFin = new Date(fechaInicio);
            duracion = parseInt(duracion);
            fechaFin.setDate(fechaFin.getDate() + 1);
            if (tipo == "MES") {
                fechaFin.setMonth(fechaFin.getMonth() + duracion);

            } else {
                fechaFin.setDate(fechaFin.getDate() + duracion);
            }
            var dias = ("0" + fechaFin.getDate()).slice(-2);
            var meses = ("0" + (fechaFin.getMonth() + 1)).slice(-2);

            var fechaTxt = fechaFin.getFullYear() + "-" + (meses) + "-" + (dias);

            $('#infoFechaFinN').val(fechaTxt);
            $('#infoFechaFinN').trigger("blur");

        }
    }

    $("body").on("change", ".infoNumMesesBeneficio", function() {
        var dataid = $(this).attr("data-id");
        calcularValorMensualBeneficio(dataid);

    });
    $("body").on("change", ".valorTotalBeneficio", function() {
        var dataid = $(this).attr("data-id");
        calcularValorMensualBeneficio(dataid);
    });

    function calcularValorMensualBeneficio(dataId) {
        const valorBeneficio = $(".valorTotalBeneficio[data-id='" + dataId + "']").inputmask('unmaskedvalue');
        const numMeses = $(".infoNumMesesBeneficio[data-id='" + dataId + "']").inputmask('unmaskedvalue');
        const valorMes = valorBeneficio / numMeses;
        //$(".infoValorMensual[data-id='"+dataId+"']").inputmask("setvalue", valorMes);
        $(".infoValorMensual[data-id='" + dataId + "']").val(valorMes);
        $(".infoValorMensual[data-id='" + dataId + "']").trigger("blur");

    }

    $("body").on("click", ".generar_pass", function(e) {
        const idUsu = $('.idEmpleado').val();
        solicitudAjax(`/empleado/dataEmpContrasenia/${idUsu}`, 'GET', null,
            (data) => {
                if (data.success) {
                    const dataEmp = data.info;
                    const randomSpecial = getRandomString(1);
                    const palabraRandom = getRandomWord();
                    const fechaNacEmp = dataEmp.fechaNacimiento;
                    const numIdenEmple = dataEmp.numeroIdentificacion;
                    const fechaNacSplit = fechaNacEmp.split('-');
                    const passNuevo = numIdenEmple + randomSpecial + fechaNacSplit[0] + randomSpecial + palabraRandom;
                    $(".pass_usu").focus();
                    $(".pass_usu").val(passNuevo);
                    console.log(passNuevo);

                } else {
                    retornarAlerta(
                        '¡Error!',
                        data.mensaje,
                        'error',
                        'Aceptar'
                    );
                }
            },
            (error) => {

            }
        );
    });

    $("body").on("submit", "#formInfoEmpleado", function(e) {
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
                    window.open("/empleado/formModificar/" + data.idempleado + "?destino=afil", "_self");
                } else {
                    $(".separadorMiles").inputmask({ alias: "currency", removeMaskOnSubmit: true });
                    $("#respError").html(data.respuesta);
                    $('#errorEmpleadoModal').modal('show');
                }
            },
            error: function(data) {
                console.log("error");
                console.log(data);
            }
        });
    });

    $("#btnNuevoContrato").click(function(e) {
        e.preventDefault();
        $(".nuevoContrato").addClass("activo");
    });
    $("#btnCambioCotizante").click(function(e) {
        e.preventDefault();
        $(".nuevoTipoCotizante").addClass("activo");
    });


    $("body").on("change", ".infoTipoBeneficio", function(e) {
        e.preventDefault();
        let dataid = $(this).attr("data-id");
        $(".infoPersonaBeneficio1[data-id='" + dataid + "']").removeClass("activo");
        $(".infoPersonaBeneficio2[data-id='" + dataid + "']").removeClass("activo");
        if ($(this).val() == "4") {
            $(".infoPersonaBeneficio1[data-id='" + dataid + "']").addClass("activo");
        } else if ($(this).val() == "5") {
            $(".infoPersonaBeneficio2[data-id='" + dataid + "']").addClass("activo");
        }
    });

});

function getRandomString(length) {
    var randomChars = '$#%&';
    var result = '';
    for (var i = 0; i < length; i++) {
        result += randomChars.charAt(Math.floor(Math.random() * randomChars.length));
    }
    return result;
}

function getRandomWord() {
    const things = ['trabajo', 'nomina', 'empresa'];
    const thing = things[Math.floor(Math.random() * things.length)];
    return thing;
}