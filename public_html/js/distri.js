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
    $("body").on("change", "#fkTipoTercero", function(e) {
        $(".elementoTercero").removeClass("activo");;
        if ($(this).val() == "8") {
            $(".elementoTercero").addClass("activo");
        }
    });


    $("#addDistri").click(function(e) {
        e.preventDefault();
        if (typeof $("#cargando")[0] !== 'undefined') {
            $("#cargando").css("display", "flex");
        } else {
            $("body").append('<div id="cargando" style="display: flex;">Cargando...</div>');
        }
        $.ajax({
            type: 'GET',
            url: "/nomina/distri/add",
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".respForm").html(data);
                $('#distriModal').modal('show');
            },
            error: function(data) {
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
    $(".copiarDistri").click(function(e) {
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
                $('#distriModal').modal('show');
            },
            error: function(data) {
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

    $(".editarDistriEm").click(function(e) {
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
                $('#distriModal').modal('show');
            },
            error: function(data) {
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