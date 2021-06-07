$(document).ready(function() {
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


});