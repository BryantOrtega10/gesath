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


        opcionales: ["sApellido", "sNombre", "telFijo", "tallaCamisa", "tallaPantalon", "tallaZapatos", "otros", "tallaOtros", "correo2", "inputFoto", "libretaMilitar", "distritoMilitar", "tIdentificacionAnt", "numIdentificacionAnt", "segundoApellidoUpc?", "segundoNombreUpc?"],
        opcionalesAVeces: [{
                camposQueSonOb: ["nombreEmergencia?", "telefonoEmergencia?", "telefonoEmergencia?", "direccionEmergencia?", "paisEmergencia?", "deptoEmergencia?", "lugarEmergencia?"],
                camposCambia: ["nombreEmergencia?", "telefonoEmergencia?", "telefonoEmergencia?", "direccionEmergencia?", "paisEmergencia?", "deptoEmergencia?", "lugarEmergencia?"],
                tipoBloqueo: 2,
                minimo: 1
            }

        ]
    };

    $.each($("#formAgregarEmpleado input, #formAgregarEmpleado select"), function(i, field) {
        var field = $(this);
        var id = field.prop("id");

        if (id != "") {
            var label = $('label[for=' + id + ']');

            if (camposOpciones.opcionales.indexOf(id) === -1) {
                label.html(label.html() + " *");



            }

        }
    });

    $("#formAgregarEmpleado .btnSubmitGen").click(function(e) {
        e.preventDefault();
        const numeroCampos = $("#formAgregarEmpleado input, #formAgregarEmpleado select").length;
        var numeroCamposVacios = 0;
        $.each($("#formAgregarEmpleado input, #formAgregarEmpleado select"), function(i, field) {
            var field = $(this);
            var validar = true;
            if (field.prop("id") == "" || field.prop("id").includes("segundoApellidoUpc") || field.prop("id").includes("segundoNombreUpc")) {
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
                    $("#btnContinuarCamposVacios").attr("data-form", "#formAgregarEmpleado");
                } else {
                    $("#formAgregarEmpleado").submit();
                }
            }
        });
    });




    $("body").on("change", "#genero", function(e) {
        if ($(this).val() != "1") {
            $("label[for='libretaMilitar']").html("Libreta Militar");
            $("label[for='distritoMilitar']").html("Distrito Militar - Clase");
            $("label[for='libretaMilitar']").parent().removeClass("campoObligatorio");
            $("label[for='distritoMilitar']").parent().removeClass("campoObligatorio");
        } else {
            $("label[for='libretaMilitar']").html("Libreta Militar *");
            $("label[for='distritoMilitar']").html("Distrito Militar - Clase *");

        }

    });



    $("body").on("blur", "#formAgregarEmpleado #numIdentificacion", function(e) { verificarDocumento(e) });
    $("body").on("blur", "#formAgregarEmpleado #tIdentificacion", function(e) { verificarDocumento(e) });

    function verificarDocumento(e) {
        e.preventDefault();
        cargando();

        if ($("#numIdentificacion").val() != $("#numIdentificacionAnt").val() || $("#tIdentificacion").val() != $("#tIdentificacionAnt").val()) {
            $.ajax({
                type: 'POST',
                data: { numIdentificacion: $("#numIdentificacion").val(), tIdentificacion: $("#tIdentificacion").val() },
                url: '/empleado/verificarDocumento',
                cache: false,
                success: function(data) {

                    $("#cargando").css("display", "none");
                    if (!data.success) {
                        $("#respMensaje").html(data.respuesta);
                        $('#mensajeEmpleadoModal').modal('show');
                        $('#aceptarMensajeEmpleado').prop("href", data.link);
                    }
                },
                error: function(data) {
                    console.log("error");
                    console.log(data);
                }
            });
        }




    }


    $("body").on("change", "#inputFoto", function(e) {

        var input = $(this)[0];
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var image = new Image();
                image.src = e.target.result;

                image.onload = function() {
                    $('#foto').prop('src', this.src);
                };
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            $('#foto').prop('src', "/img/foto.png");
        }

    });


    $("body").on("submit", "#formAgregarEmpleado", function(e) {
        e.preventDefault();
        if (typeof $("#cargando")[0] !== 'undefined') {
            $("#cargando").css("display", "flex");
        } else {
            $("body").append('<div id="cargando" style="display: flex;">Cargando...</div>');
        }
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
                    window.open("/empleado/formModificar/" + data.idempleado + "?destino=infoLab", "_self");
                } else {
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

    $("body").on("change", "#paisExpedicion", function() {
        cargando();
        var valorUbicacion = $(this).val();
        $("#deptoExpedicion").html('<option value=""></option>');
        $("#lugarExpedicion").html('<option value=""></option>');
        $("#deptoExpedicion").trigger("change");
        $("#lugarExpedicion").trigger("change");

        if (valorUbicacion != "") {
            $.ajax({
                type: 'GET',
                url: "/ubicaciones/obtenerHijos/" + valorUbicacion,
                success: function(data) {
                    $("#cargando").css("display", "none");
                    $("#deptoExpedicion").html(data.opciones);
                },
                error: function(data) {
                    console.log("error");
                    console.log(data);
                }
            });
        }
    });



    $("body").on("change", "#deptoExpedicion", function() {
        cargando();
        var valorUbicacion = $(this).val();
        $("#lugarExpedicion").html('<option value=""></option>');
        $("#lugarExpedicion").trigger("change");
        if (valorUbicacion != "") {
            $.ajax({
                type: 'GET',
                url: "/ubicaciones/obtenerHijos/" + valorUbicacion,
                success: function(data) {
                    $("#cargando").css("display", "none");
                    $("#lugarExpedicion").html(data.opciones);
                },
                error: function(data) {
                    console.log("error");
                    console.log(data);
                }
            });
        }
    });


    $("body").on("change", "#paisNacimiento", function() {
        cargando();
        var valorUbicacion = $(this).val();
        $("#deptoNacimiento").html('<option value=""></option>');
        $("#lugarNacimiento").html('<option value=""></option>');
        $("#deptoNacimiento").trigger("change");
        $("#lugarNacimiento").trigger("change");
        if (valorUbicacion != "") {
            $.ajax({
                type: 'GET',
                url: "/ubicaciones/obtenerHijos/" + valorUbicacion,
                success: function(data) {
                    $("#cargando").css("display", "none");
                    $("#deptoNacimiento").html(data.opciones);
                },
                error: function(data) {
                    console.log("error");
                    console.log(data);
                }
            });
        }
    });



    $("body").on("change", "#deptoNacimiento", function() {
        cargando();
        var valorUbicacion = $(this).val();
        $("#lugarNacimiento").html('<option value=""></option>');
        $("#lugarNacimiento").trigger("change");
        if (valorUbicacion != "") {
            $.ajax({
                type: 'GET',
                url: "/ubicaciones/obtenerHijos/" + valorUbicacion,
                success: function(data) {
                    $("#cargando").css("display", "none");
                    $("#lugarNacimiento").html(data.opciones);
                },
                error: function(data) {
                    console.log("error");
                    console.log(data);
                }
            });
        }
    });

    $("body").on("change", "#paisResidencia", function() {
        cargando();
        var valorUbicacion = $(this).val();
        $("#deptoResidencia").html('<option value=""></option>');
        $("#lugarResidencia").html('<option value=""></option>');
        $("#deptoResidencia").trigger("change");
        $("#lugarResidencia").trigger("change");
        if (valorUbicacion != "") {
            $.ajax({
                type: 'GET',
                url: "/ubicaciones/obtenerHijos/" + valorUbicacion,
                success: function(data) {
                    $("#cargando").css("display", "none");
                    $("#deptoResidencia").html(data.opciones);
                },
                error: function(data) {
                    console.log("error");
                    console.log(data);
                }
            });
        }
    });



    $("body").on("change", "#deptoResidencia", function() {
        cargando();
        var valorUbicacion = $(this).val();
        $("#lugarResidencia").html('<option value=""></option>');
        $("#lugarResidencia").trigger("change");
        if (valorUbicacion != "") {
            $.ajax({
                type: 'GET',
                url: "/ubicaciones/obtenerHijos/" + valorUbicacion,
                success: function(data) {
                    $("#cargando").css("display", "none");
                    $("#lugarResidencia").html(data.opciones);
                },
                error: function(data) {
                    console.log("error");
                    console.log(data);
                }
            });
        }
    });

    $("body").on("change", ".paisEmergencia", function() {
        cargando();
        var valorUbicacion = $(this).val();
        var dataid = $(this).attr("data-id");

        $(".deptoEmergencia[data-id='" + dataid + "']").html('<option value=""></option>');
        $(".lugarEmergencia[data-id='" + dataid + "']").html('<option value=""></option>');
        $(".deptoEmergencia[data-id='" + dataid + "']").trigger("change");
        $(".lugarEmergencia[data-id='" + dataid + "']").trigger("change");
        if (valorUbicacion != "") {
            $.ajax({
                type: 'GET',
                url: "/ubicaciones/obtenerHijos/" + valorUbicacion,
                success: function(data) {
                    $("#cargando").css("display", "none");
                    $(".deptoEmergencia[data-id='" + dataid + "']").html(data.opciones);
                },
                error: function(data) {
                    console.log("error");
                    console.log(data);
                }
            });
        }
    });



    $("body").on("change", ".deptoEmergencia", function() {
        cargando();
        var valorUbicacion = $(this).val();
        var dataid = $(this).attr("data-id");
        $(".lugarEmergencia[data-id='" + dataid + "']").html('<option value=""></option>');
        $(".lugarEmergencia[data-id='" + dataid + "']").trigger("change");
        if (valorUbicacion != "") {
            $.ajax({
                type: 'GET',
                url: "/ubicaciones/obtenerHijos/" + valorUbicacion,
                success: function(data) {
                    $("#cargando").css("display", "none");
                    $(".lugarEmergencia[data-id='" + dataid + "']").html(data.opciones);
                },
                error: function(data) {
                    console.log("error");
                    console.log(data);
                }
            });
        }
    });


    $("body").on("change", ".paisUpc", function() {
        cargando();
        var valorUbicacion = $(this).val();
        var dataid = $(this).attr("data-id");

        $(".deptoUpc[data-id='" + dataid + "']").html('<option value=""></option>');
        $(".lugarUpc[data-id='" + dataid + "']").html('<option value=""></option>');
        $(".deptoUpc[data-id='" + dataid + "']").trigger("change");
        $(".lugarUpc[data-id='" + dataid + "']").trigger("change");
        if (valorUbicacion != "") {
            $.ajax({
                type: 'GET',
                url: "/ubicaciones/obtenerHijos/" + valorUbicacion,
                success: function(data) {
                    $("#cargando").css("display", "none");
                    $(".deptoUpc[data-id='" + dataid + "']").html(data.opciones);
                },
                error: function(data) {
                    console.log("error");
                    console.log(data);
                }
            });
        }
    });



    $("body").on("change", ".deptoUpc", function() {
        cargando();
        var valorUbicacion = $(this).val();
        var dataid = $(this).attr("data-id");
        $(".lugarUpc[data-id='" + dataid + "']").html('<option value=""></option>');
        $(".lugarUpc[data-id='" + dataid + "']").trigger("change");
        if (valorUbicacion != "") {
            $.ajax({
                type: 'GET',
                url: "/ubicaciones/obtenerHijos/" + valorUbicacion,
                success: function(data) {
                    $("#cargando").css("display", "none");
                    $(".lugarUpc[data-id='" + dataid + "']").html(data.opciones);
                },
                error: function(data) {
                    console.log("error");
                    console.log(data);
                }
            });
        }
    });

    $("body").on("click", ".masUpcAdicional", function() {
        cargando();
        var numPersona = $(this).attr("data-num");
        numPersona++;
        $(this).attr("data-num", numPersona);
        $.ajax({
            type: 'GET',
            url: "/empleado/cargarUpcAdicional/" + numPersona,
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".upcAdicionalCont").append(data);
            },
            error: function(data) {
                console.log("error");
                console.log(data);
            }
        });
    });


    $("body").on("click", ".masPersonasVive", function() {
        cargando();
        var numPersona = $(this).attr("data-num");
        numPersona++;
        $(this).attr("data-num", numPersona);
        $.ajax({
            type: 'GET',
            url: "/empleado/cargarPersonasVive/" + numPersona,
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".personaViveCont").append(data);
            },
            error: function(data) {
                console.log("error");
                console.log(data);
            }
        });
    });

    $("body").on("click", ".quitarPersonaVive", function(e) {
        e.preventDefault();
        cargando();
        var dataid = $(this).attr("data-id");

        $(this).attr("data-num", dataid);
        $(".personaV[data-id='" + dataid + "']").remove();
    });

    $("body").on("click", ".masContactoEmer", function() {
        cargando();
        var numContacto = $(this).attr("data-num");
        numContacto++;
        $(this).attr("data-num", numContacto);
        $.ajax({
            type: 'GET',
            url: "/empleado/cargarContactoEmergencia/" + numContacto,
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".emergenciaCont").append(data);
            },
            error: function(data) {
                console.log("error");
                console.log(data);
            }
        });
    });

    $("body").on("click", ".quitarContactoEmergencia", function(e) {
        e.preventDefault();
        cargando();
        var dataid = $(this).attr("data-id");

        $(this).attr("data-num", dataid);
        $(".emergencia[data-id='" + dataid + "']").remove();
    });




});