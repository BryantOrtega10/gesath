function cargando() {
    if (typeof $("#cargando")[0] !== 'undefined') {
        $("#cargando").css("display", "flex");
    } else {
        $("body").append('<div id="cargando" style="display: flex;">Cargando...</div>');
    }
}
$(document).ready(function() {
    function cargarAjax() {
        if (parseInt($("#realizarConsulta").val()) == 1) {
            cargando();
            const idCargaDatosPasados = $("#idCargaDatosPasados").val();
            $.ajax({
                type: 'GET',
                url: "/datosPasadosVac/subir/" + idCargaDatosPasados,
                success: function(data) {
                    $("#cargando").css("display", "none");
                    if (data.success) {
                        if (data.seguirSubiendo) {
                            $("#realizarConsulta").val("1");
                            setTimeout(() => {
                                cargarAjax();
                            }, 500);
                        } else {
                            $("#realizarConsulta").val("0");
                            window.location.reload();
                        }
                        $(".progress-bar").css("width", data.porcentaje);
                        $(".progress-bar").html(data.porcentaje);
                        $("#datosCargados").html(data.mensaje);
                    }

                },
                error: function(data) {
                    console.log("error");
                    console.log(data);
                }
            });
        }
    }

    $("body").on("click", ".modificar", function(e) {
        e.preventDefault();
        const dataId = $(this).attr("data-id");

        $(".ocultoModificacion[data-id='" + dataId + "']").addClass("activo");
        $(".mostrarModificacion[data-id='" + dataId + "']").addClass("activo");

    });

    $("body").on("click", ".cancelar", function(e) {
        e.preventDefault();
        const dataId = $(this).attr("data-id");
        $(".ocultoModificacion[data-id='" + dataId + "']").removeClass("activo");
        $(".mostrarModificacion[data-id='" + dataId + "']").removeClass("activo");
    });

    $("body").on("click", ".modificarEnvio", function(e) {
        e.preventDefault();
        const dataId = $(this).attr("data-id");
        $("#idDatoPasado").val(dataId);
        $("#fecha").val($("#fecha_" + dataId).val());
        $("#fechaInicial").val($("#fechaInicial_" + dataId).val());
        $("#fechaFinal").val($("#fechaFinal_" + dataId).val());
        $("#dias").val($("#dias_" + dataId).val());
        $("#formMod").submit();
    });

    $("body").on("submit", "#formMod", function(e) {
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
                    alert(data.mensaje);
                }
            },
            error: function(data) {
                console.log("error");
                console.log(data);
            }
        });
    });

    cargarAjax();

});