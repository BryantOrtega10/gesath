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

    $("body").on("change", "#nomina", function() {

        cargarFechasxNomina();
    });
    $("body").on("change", "#tipoliquidacion", function() {

        cargarFechasxNomina();
    });

    $("body").on("change", "#fechaInicio", function(e) {
        if ($("#periodo").val() != "") {
            let fechaInicio = new Date($(this).val());
            let duracion = parseInt($("#periodo").val());
            if (duracion != 15 && duracion != 30) {
                fechaInicio.setDate(fechaInicio.getDate() + duracion);
                var dias = ("0" + fechaInicio.getDate()).slice(-2);
                var meses = ("0" + (fechaInicio.getMonth() + 1)).slice(-2);
                var fechaTxt = fechaInicio.getFullYear() + "-" + (meses) + "-" + (dias);
                $('#fechaFin').val(fechaTxt);
                $('#fechaFin').trigger("blur");


                fechaInicio.setDate(fechaInicio.getDate() + 1);
                dias = ("0" + fechaInicio.getDate()).slice(-2);
                meses = ("0" + (fechaInicio.getMonth() + 1)).slice(-2);
                fechaTxt = fechaInicio.getFullYear() + "-" + (meses) + "-" + (dias);
                $('#fechaInicioProx').val(fechaTxt);
                $('#fechaInicioProx').trigger("blur");

                fechaInicio.setDate(fechaInicio.getDate() + duracion);
                dias = ("0" + fechaInicio.getDate()).slice(-2);
                meses = ("0" + (fechaInicio.getMonth() + 1)).slice(-2);
                fechaTxt = fechaInicio.getFullYear() + "-" + (meses) + "-" + (dias);
                $('#fechaFinProx').val(fechaTxt);
                $('#fechaFinProx').trigger("blur");

            }



        }

    });

    $("body").on("change", "#fecha", function() {
        if ($(this).val() != "") {

            var anioSelect = parseInt($(this).val().substring(0, 4));
            var mesSelect = parseInt($(this).val().substring(5, 7));

            var ultimoDiaProxMes = new Date(anioSelect, mesSelect + 1, 0);
            var primerDiaProxMes = new Date(anioSelect, mesSelect, 1);


            var ultimoDia = new Date(anioSelect, mesSelect, 0);
            var primerDia = new Date(anioSelect, mesSelect - 1, 1);

            console.log(ultimoDiaProxMes);
            console.log($(this).val().substring(5, 7));


            if ($("#periodo").val() == "15") {
                if ($(this).val().substring(8, 10) <= 15) {
                    var diaInter = new Date(anioSelect, mesSelect - 1, 15);

                    var diaInterProx = new Date(anioSelect, mesSelect - 1, 16);


                    $("#fechaInicio").val(primerDia.getFullYear() + "-" + ("0" + (primerDia.getMonth() + 1)).slice(-2) + "-" + ("0" + primerDia.getDate()).slice(-2));
                    $("#fechaInicio").trigger("change");

                    $("#fechaFin").val(diaInter.getFullYear() + "-" + ("0" + (diaInter.getMonth() + 1)).slice(-2) + "-" + ("0" + diaInter.getDate()).slice(-2));
                    $("#fechaFin").trigger("change");

                    $("#fechaInicioProx").val(diaInterProx.getFullYear() + "-" + ("0" + (diaInterProx.getMonth() + 1)).slice(-2) + "-" + ("0" + diaInterProx.getDate()).slice(-2));
                    $("#fechaInicioProx").trigger("change");

                    $("#fechaFinProx").val(ultimoDia.getFullYear() + "-" + ("0" + (ultimoDia.getMonth() + 1)).slice(-2) + "-" + ("0" + ultimoDia.getDate()).slice(-2));
                    $("#fechaFinProx").trigger("change");
                } else {
                    var diaInter = new Date(anioSelect, mesSelect - 1, 16);

                    var diaInterProx = new Date(anioSelect, mesSelect, 15);

                    $("#fechaInicio").val(diaInter.getFullYear() + "-" + ("0" + (diaInter.getMonth() + 1)).slice(-2) + "-" + ("0" + diaInter.getDate()).slice(-2));
                    $("#fechaInicio").trigger("change");

                    $("#fechaFin").val(ultimoDia.getFullYear() + "-" + ("0" + (ultimoDia.getMonth() + 1)).slice(-2) + "-" + ("0" + ultimoDia.getDate()).slice(-2));
                    $("#fechaFin").trigger("change");

                    $("#fechaInicioProx").val(primerDiaProxMes.getFullYear() + "-" + ("0" + (primerDiaProxMes.getMonth() + 1)).slice(-2) + "-" + ("0" + primerDiaProxMes.getDate()).slice(-2));
                    $("#fechaInicioProx").trigger("change");

                    $("#fechaFinProx").val(diaInterProx.getFullYear() + "-" + ("0" + (diaInterProx.getMonth() + 1)).slice(-2) + "-" + ("0" + diaInterProx.getDate()).slice(-2));
                    $("#fechaFinProx").trigger("change");

                }
            } else {


                $("#fechaInicio").val(primerDia.getFullYear() + "-" + ("0" + (primerDia.getMonth() + 1)).slice(-2) + "-" + ("0" + primerDia.getDate()).slice(-2));
                $("#fechaInicio").trigger("change");

                $("#fechaFin").val(ultimoDia.getFullYear() + "-" + ("0" + (ultimoDia.getMonth() + 1)).slice(-2) + "-" + ("0" + ultimoDia.getDate()).slice(-2));
                $("#fechaFin").trigger("change");

                $("#fechaInicioProx").val(primerDiaProxMes.getFullYear() + "-" + ("0" + (primerDiaProxMes.getMonth() + 1)).slice(-2) + "-" + ("0" + primerDiaProxMes.getDate()).slice(-2));
                $("#fechaInicioProx").trigger("change");

                $("#fechaFinProx").val(ultimoDiaProxMes.getFullYear() + "-" + ("0" + (ultimoDiaProxMes.getMonth() + 1)).slice(-2) + "-" + ("0" + ultimoDiaProxMes.getDate()).slice(-2));
                $("#fechaFinProx").trigger("change");

            }
        }

    });

    function cargarFechasxNomina() {
        $("#fecha").val("");
        $("#fecha").trigger("change");
        $(".respNomina").html("");

        $(".respTipoNomina").html("");
        const idNomina = $("#nomina").val();
        const idTipoliquidacion = $("#tipoliquidacion").val();
        if (idNomina != "" && idTipoliquidacion != "") {
            cargarEmpleadosxNomina(idNomina, idTipoliquidacion);
            cargando();
            $.ajax({
                type: 'GET',
                url: "/nomina/cargarFechaPagoxNomina/" + idNomina + "/" + idTipoliquidacion,
                success: function(data) {
                    $("#cargando").css("display", "none");
                    $(".respTipoNomina").html("Periodo: " + data.periodoNomina);



                    $("#periodo").val(data.periodo);
                    if (data.fechaPagoDeseada != "") {
                        $("#fecha").val(data.fechaPagoDeseada);
                        //$("#fecha").prop("max", data.fechaPagoDeseada);
                        $("#fecha").trigger("change");
                        $("#fechaFin").val(data.fechaPagoDeseada);
                        $("#fechaFin").trigger("change");
                        //$("#fecha").prop("min", data.fechaMinima);
                        $("#fechaInicio").val(data.fechaMinima);
                        $("#fechaInicio").trigger("change");
                        if (data.fechaProximoInicio != "") {
                            $("#fechaInicioProx").val(data.fechaProximoInicio);
                            $("#fechaInicioProx").trigger("change");
                        }
                        if (data.fechaProximoFin != "") {
                            $("#fechaFinProx").val(data.fechaProximoFin);
                            $("#fechaFinProx").trigger("change");
                        }
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
    }

    function cargarEmpleadosxNomina(idNomina, idTipoliquidacion) {
        cargando();
        $.ajax({
            type: 'GET',
            url: "/nomina/cargarEmpleadosxNomina/" + idNomina + "/" + idTipoliquidacion,
            success: function(data) {
                $("#cargando").css("display", "none");

                $(".respNomina").html(data);
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
    }

    $("body").on("change", "#tipoliquidacionPrima", function(e) {
        e.preventDefault();
        $(".fechaParaPrima").removeClass("activo");
        $(".porcentajeParaPrima").removeClass("activo");
        $(".valorFijoParaPrima").removeClass("activo");

        if ($(this).val() == "1") {
            $(".fechaParaPrima").addClass("activo");
        } else if ($(this).val() == "2") {
            $(".porcentajeParaPrima").addClass("activo");
        } else if ($(this).val() == "3") {
            $(".valorFijoParaPrima").addClass("activo");
        }
    })


    $("body").on("click", ".quitarEmpleadoNomina", function(e) {
        e.preventDefault();
        const dataId = $(this).attr("data-id");
        $("#excluirEmpleados").val($("#excluirEmpleados").val() + dataId + ",");
        $(".empleadoFila[data-id='" + dataId + "']").remove();
        console.log();
    })

    $("body").on("submit", "#formAgregarSolicitud", function(e) {
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
                    alert("Solicitud agregada correctamente");
                    window.open("/nomina/solicitudLiquidacion/", "_self");
                } else {
                    $(".print-error-msg-Liquida").find("ul").html('');
                    $(".print-error-msg-Liquida").css('display', 'block');
                    $.each(data.error, function(key, value) {
                        $(".print-error-msg-Liquida").find("ul").append('<li>' + value + '</li>');
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
    $(".separadorMiles").inputmask({ alias: "currency", removeMaskOnSubmit: true });
});