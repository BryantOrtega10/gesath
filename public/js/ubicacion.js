function cargando() {
    if (typeof $("#cargando")[0] !== 'undefined') {
        $("#cargando").css("display", "flex");
    } else {
        $("body").append('<div id="cargando" style="display: flex;">Cargando...</div>');
    }
}
$(document).ready(function() {
    $('#ubicaciones').DataTable();
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $("body").on("change", "#ciudad", function() {
        $("#codigo").val($(this).val());
    });
    $("body").on("change", "#depto", function() {
        cargando();
        var depto = $(this).val();
        $("#codigo").val(depto);
        if (depto != "" && $("#ciudad").length) {
            $.ajax({
                type: 'GET',
                url: "/ubicacion/obtenerHijos/" + depto,
                success: function(data) {
                    $("#ciudad").html(data.opciones);
                },
                error: function(data) {
                    console.log("error");
                    console.log(data);
                }
            });
        }
    });

    $("body").on("change", "#pais", function() {
        cargando();
        var pais = $(this).val();
        $("#codigo").val(pais);
        if (pais != "" && $("#depto").length) {
            $.ajax({
                type: 'GET',
                url: "/ubicacion/obtenerHijos/" + pais,
                success: function(data) {
                    $("#depto").html(data.opciones);
                },
                error: function(data) {
                    console.log("error");
                    console.log(data);
                }
            });
        }
    });

    $("body").on("change", "#tUbicacion", function() {
        cargando();
        var tUbicacion = $(this).val();

        if (tUbicacion != "") {
            $.ajax({
                type: 'GET',
                url: "/ubicacion/cambioTUbicacion/" + tUbicacion,
                success: function(data) {
                    $(".resTipoUbicacion").html(data.html);
                },
                error: function(data) {
                    console.log("error");
                    console.log(data);
                }
            });
        }

    });

    $("#addVariable").click(function(e) {
        e.preventDefault();
        if (typeof $("#cargando")[0] !== 'undefined') {
            $("#cargando").css("display", "flex");
        } else {
            $("body").append('<div id="cargando" style="display: flex;">Cargando...</div>');
        }
        $.ajax({
            type: 'GET',
            url: "/ubicacion/getForm/add",
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".respForm").html(data);
                $('#ubicacionModal').modal('show');
            },
            error: function(data) {
                console.log("error");
                console.log(data);
            }
        });
    });
    $(".editar").click(function(e) {
        e.preventDefault();
        if (typeof $("#cargando")[0] !== 'undefined') {
            $("#cargando").css("display", "flex");
        } else {
            $("body").append('<div id="cargando" style="display: flex;">Cargando...</div>');
        }
        $.ajax({
            type: 'GET',
            url: $(this).attr("href"),
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".respForm").html(data);
                $('#ubicacionModal').modal('show');
            },
            error: function(data) {
                console.log("error");
                console.log(data);
            }
        });
    });
    $("body").on("submit", ".formGen", function(e) {
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
                    window.location.reload();
                } else {
                    $("#infoErrorForm").css("display", "block");
                    $("#infoErrorForm").html(data.mensaje);
                }
            },
            error: function(data) {
                console.log("error");
                console.log(data);
            }
        });
    });
});