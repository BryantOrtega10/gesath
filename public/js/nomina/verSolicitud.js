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

    $("body").on("click", ".verDetalle", function(e) {
        e.preventDefault();
        cargando();

        const dataid = $(this).attr("data-id");
        $.ajax({
            type: 'GET',
            url: "/nomina/cargarInfoxBoucher/" + dataid,
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".detalleBoucher[data-id='" + dataid + "']").html(data);
                $(".detalleBoucher[data-id='" + dataid + "']").addClass("activo");
                $(".verDetalle[data-id=" + dataid + "]").addClass("ocultarDetalle");
                $(".verDetalle[data-id=" + dataid + "]").html("Ocultar Detalle");
                $(".verDetalle[data-id=" + dataid + "]").removeClass("verDetalle");
            },
            error: function(data) {
                console.log("error");
                console.log(data);
            }
        });
    });
    $("body").on("click", ".ocultarDetalle", function(e) {
        const dataid = $(this).attr("data-id");
        $(".detalleBoucher[data-id='" + dataid + "']").html("");
        $(".detalleBoucher[data-id='" + dataid + "']").removeClass("activo");
        $(".ocultarDetalle[data-id=" + dataid + "]").addClass("verDetalle");
        $(".ocultarDetalle[data-id=" + dataid + "]").html("Ver Detalle");
        $(".ocultarDetalle[data-id=" + dataid + "]").removeClass("ocultarDetalle");
    });

    $("body").on("click", ".verComoCalculo", function(e) {
        e.preventDefault();
        cargando();
        $.ajax({
            type: 'GET',
            url: $(this).attr("href"),
            cache: false,
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".resComoCalculoModal").html(data);
                $('#comoCalculoModal').modal('show');
            },
            error: function(data) {
                console.log("error");
                console.log(data);
            }
        });
    });

    $("body").on("click", ".recalcular", function(e) {
        e.preventDefault();
        cargando();
        const dataid = $(this).attr("data-id");
        $.ajax({
            type: 'GET',
            url: $(this).attr("href"),
            cache: false,
            success: function(data) {
                $("#cargando").css("display", "none");
                if (data.success) {
                    $(".netoPagar[data-id='" + dataid + "']").html(data.netoPagar);
                    $("#totalNomina").html(data.totalNomina);

                    if (typeof $(".verDetalle[data-id='" + dataid + "']")[0] !== 'undefined') {
                        $(".verDetalle[data-id='" + dataid + "']").trigger("click");
                    } else {
                        $(".ocultarDetalle[data-id='" + dataid + "']").trigger("click");
                        $(".verDetalle[data-id='" + dataid + "']").trigger("click");
                    }
                } else {
                    alert(data.error);
                }


            },
            error: function(data) {
                console.log("error");
                console.log(data);
            }
        });
    });

    $("body").on("click", ".recalcularNomina", function(e) {
        e.preventDefault();
        cargando();
        $.ajax({
            type: 'GET',
            url: $(this).attr("href"),
            cache: false,
            success: function(data) {
                $("#cargando").css("display", "none");
                if (data.success) {
                    alert("Nomina recalculada correctamente");
                    window.location.reload();
                } else {
                    alert(data.error);
                }


            },
            error: function(data) {
                console.log("error");
                console.log(data);
            }
        });
    });







    $("body").on("submit", "#formModificarSolicitud", function(e) {
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
                    alert("Solicitud modificada correctamente");
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
                console.log("error");
                console.log(data);
            }
        });
    });
    $("body").on("submit", "#formModificarSolicitud2", function(e) {
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
                    alert("Solicitud modificada correctamente");
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
                console.log("error");
                console.log(data);
            }
        });
    });

    $(".recargar").click(function(e) {
        e.preventDefault();
        window.open($(this).attr("data-url"), "_self");
    });
    $(".enviarCorreo").click(function(e) {
        e.preventDefault();
        cargando();
        $.ajax({
            type: 'GET',
            url: $(this).attr("href"),
            cache: false,
            success: function(data) {
                $("#cargando").css("display", "none");
                if (data.success) {
                    alert("Correo enviado correctamente");
                    window.location.reload();
                } else {
                    alert(data.error);
                }


            },
            error: function(data) {
                console.log("error");
                console.log(data);
            }
        });

    });
});