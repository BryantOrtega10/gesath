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

    $("body").on("change", ".afiliacionTipoAfilicacion", function() {
        cargando();
        var valorAfiliacion = $(this).val();
        var data_id = $(this).attr("data-id");
        $("#afiliacionEntidad" + data_id).html('<option value=""></option>');
        $("#afiliacionEntidad" + data_id).trigger("change");
        if (valorAfiliacion != "") {
            $.ajax({
                type: 'GET',
                url: "/empleado/cargarEntidadesAfiliacion/" + valorAfiliacion,
                success: function(data) {
                    $("#cargando").css("display", "none");
                    $("#afiliacionEntidad" + data_id).html(data.opcionesAfiliacion);
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




    $("body").on("click", ".masAfiliaciones", function() {
        var numAfiliacion = $(this).attr("data-num");
        numAfiliacion++;
        cargando();
        $(this).attr("data-num", numAfiliacion);
        $.ajax({
            type: 'GET',
            url: "/empleado/cargarAfiliaciones/" + numAfiliacion,
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".afiliacionesCont").append(data);

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
    $("body").on("click", ".quitarAfiliacion", function(e) {
        e.preventDefault();
        var dataid = $(this).attr("data-id");
        //$(this).attr("data-num",dataid);
        $(".afiliacion[data-id='" + dataid + "']").remove();

    });
    $("body").on("click", ".quitarAfiliacion2", function(e) {
        e.preventDefault();
        var dataid = $(this).attr("data-id");
        //$(this).attr("data-num",dataid);        
        $("#idsAfiliacionEliminar").val($("#idsAfiliacionEliminar").val() + $("#idAfiliacion" + dataid).val() + ",");
        $(".afiliacion[data-id='" + dataid + "']").remove();
    });

    //
    $("body").on("click", ".btnCambioAfiliacion", function() {
        var dataid = $(this).attr("data-id");

        $(".cambioAfiliacion[data-id='" + dataid + "']").addClass("activoFlex");
    });

    $("body").on("submit", "#formAfiliacionEmpleado", function(e) {
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
                    window.open("/empleado/formModificar/" + data.idempleado + "?destino=concFij", "_self");
                } else {
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
});