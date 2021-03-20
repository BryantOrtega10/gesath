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

    $.ajax({
        type: 'GET',
        url: "/notificaciones/numeroNotificaciones",
        success: function(data) {
            if (data.numNoVistos > 0) {
                $(".numNotificaciones").html(data.numNoVistos);
            } else {
                $(".numNotificaciones").css("display", "none");
            }
        },
        error: function(data) {
            console.log("error");
            console.log(data);
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
                console.log("error");
                console.log(data);
            }
        });
    })

});