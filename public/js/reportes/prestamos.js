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
    $("body").on("change", "#infoEmpresa", function(e) {
        e.preventDefault();

        $("#agenteRetenedor").val($(this).find("option[value=" + $(this).val() + "]").html());
        $("#agenteRetenedor").trigger("change");
        $("#infoNomina").html('<option value=""></option>');
        $("#infoNomina").trigger("change");

        const idEmpresa = $(this).val();
        if (idEmpresa != "") {
            cargando();
            $.ajax({
                type: 'GET',
                url: "/empleado/cargarDatosPorEmpresa/" + idEmpresa,
                success: function(data) {
                    $("#cargando").css("display", "none");
                    $("#infoNomina").html(data.opcionesNomina);
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
    $("body").on("click", ".recargar", function() {
        cargando();
        $.ajax({
            type: 'GET',
            url: "/empleado/cargarFormEmpleadosxNomina?idNomina=" + $("#infoNomina").val(),
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



    $("body").on("click", "#busquedaEmpleado", function() {
        cargando();
        $.ajax({
            type: 'GET',
            url: "/empleado/cargarFormEmpleadosxNomina?idNomina=" + $("#infoNomina").val(),
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

    $("body").on("submit", "#filtrarEmpleado", function(e) {
        e.preventDefault();
        cargando();

        var formdata = $('#filtrarEmpleado').serialize();
        $.ajax({
            type: 'GET',
            url: $(this).attr("action"),
            data: formdata,
            success: function(data) {
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
    $("body").on("click", ".resFormBusEmpleado a.seleccionarEmpleado", function(e) {
        e.preventDefault();
        $("#nombreEmpleado").val($(this).html().trim());
        $("#nombreEmpleado").trigger("change");
        $("#idEmpleado").val($(this).attr("data-id"));
        $('#busquedaEmpleadoModal').modal('hide');

    });

    $("#tipoReporte").change(function(e) {
        e.preventDefault();
        const tipoReporte = $(this).val();
        $("#concepto").html('<option value=""></option>');
        $("#concepto").trigger("change");
        cargando();
        $.ajax({
            type: 'GET',
            url: '/reportes/conceptosPorTipo/' + tipoReporte,
            success: function(data) {
                $("#cargando").css("display", "none");
                $("#concepto").html(data.html);
                $("#concepto").trigger("change");
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


});