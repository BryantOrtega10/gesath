$(document).ready(function() {
    $("body").on("click", ".openMenu", function(e) {
        $(this).removeClass("openMenu");
        $(this).addClass("closeMenu");
        $(".menuLateral").addClass("active");

    });
    $("body").on("click", ".closeMenu", function(e) {
        $(this).addClass("openMenu");
        $(this).removeClass("closeMenu");
        $(".menuLateral").removeClass("active");
    });

    $("body").on("click", ".form-group label", function(e) {
        $(this).parent().addClass("focusGroup");
    });
    $("body").on("focus", ".form-group input", function(e) {
        $(this).parent().addClass("focusGroup");
    });
    $("body").on("blur", ".form-group input", function(e) {
        $(this).parent().removeClass("focusGroup");
        $(this).parent().removeClass("hasText");
        if ($(this).val() != "") {
            $(this).parent().addClass("hasText");
        }
    });
    $("body").on("change", ".form-group input", function(e) {
        $(this).parent().removeClass("focusGroup");
        $(this).parent().removeClass("hasText");
        if ($(this).val() != "") {
            $(this).parent().addClass("hasText");
        }
    });
    $("body").on("focus", ".form-group select", function(e) {
        $(this).parent().addClass("focusGroup");
    });
    $("body").on("blur", ".form-group select", function(e) {
        $(this).parent().removeClass("focusGroup");
        $(this).parent().removeClass("hasText");
        if ($(this).val() != "") {
            $(this).parent().addClass("hasText");
        }

    });
    $("body").on("change", ".form-group select", function(e) {
        $(this).parent().removeClass("focusGroup");
        $(this).parent().removeClass("hasText");
        if ($(this).val() != "") {
            $(this).parent().addClass("hasText");
        }

    });
    $("body").on("keyup", "#buscarMenu", function(e) {
        $.ajax({
            type: 'GET',
            url: "/menu/buscar/" + $(this).val(),
            success: function(data) {
                $(".respMenu").html(data);
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
    })

});