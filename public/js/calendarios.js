function cargando() {
    if (typeof $("#cargando")[0] !== 'undefined') {
        $("#cargando").css("display", "flex");
    } else {
        $("body").append('<div id="cargando" style="display: flex;">Cargando...</div>');
    }
}
$(document).ready(function() {
    $("#calendarios").DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.22/i18n/Spanish.json'
        }
    });
    
    $(document).on('show.bs.modal', '.modal', function(event) {
        var zIndex = 1040 + (10 * $('.modal:visible').length);
        $(this).css('z-index', zIndex);
        setTimeout(function() {
            $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
        }, 0);
    });

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $("#editCalendario").click((e) => {
        e.preventDefault();
        cargando();
        $.ajax({
            type: 'GET',
            url: "/calendario/datosCalendarioEditar",
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".respForm[data-para='calendarios']").html(data);
                $('#calendariosModal').modal('show');
            },
            error: function(data) {
                console.log("error");
                console.log(data);
            }
        });
    });

    $("#verCalendario").click((e) => {
        e.preventDefault();
        const idCalendario = e.target.attributes.dataId.value;
        cargando();
        $.ajax({
            type: 'GET',
            url: `/calendario/datosCalendarioVer`,
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".respForm[data-para='calendarios']").html(data);
                $('#calendariosModal').modal('show');
            },
            error: function(data) {
                console.log("error");
                console.log(data);
            }
        });
    });
});