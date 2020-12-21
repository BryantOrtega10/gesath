function cargando() {
    if (typeof $("#cargando")[0] !== 'undefined') {
        $("#cargando").css("display", "flex");
    } else {
        $("body").append('<div id="cargando" style="display: flex;">Cargando...</div>');
    }
}
$(document).ready(function() {
    $("#cargos").DataTable();
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

    $("body").on("click", ".quitarConcepto", function(e) {
        e.preventDefault();
        $(".cargoMasCont[data-id='" + $(this).attr("data-id") + "']").remove();
    });

    $("#addCargo").click(function(e) {
        e.preventDefault();
        cargando();
        $.ajax({
            type: 'GET',
            url: "/cargos/getFormAdd",
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".respForm[data-para='cargos']").html(data);
                $('#cargosModal').modal('show');
            },
            error: function(data) {
                console.log("error");
                console.log(data);
            }
        });
    });
    $("body").on("submit", ".formGen", function(e) {
        e.preventDefault();
        cargando();
        $(".close").trigger('click');
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
                    retornarAlerta(
                        '¡Hecho!',
                        data.mensaje,
                        'success',
                        'Aceptar'
                    );
                    window.location.reload();
                } else {
                    $("#infoErrorForm").css("display", "block");
                    $("#infoErrorForm").html(data.mensaje);
                }
            },
            error: function(data) {
                const error = data.responseJSON;
                if (error.error_code === 'VALIDATION_ERROR') {
                    mostrarErrores(error.errors);
                } else {
                    console.log("error");
                }
            }
        });
    });

    $(".editar").click((e) => {
        e.preventDefault();
        const idCargo = e.target.attributes.dataId.value;
        cargando();
        $.ajax({
            type: 'GET',
            url: `/cargos/datosCargoXId/${idCargo}`,
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".respForm[data-para='cargos']").html(data);
                $('#cargosModal').modal('show');
            },
            error: function(data) {
                console.log("error");
                console.log(data);
            }
        });
    });

    $("body").on("submit", ".formEdit", function(e) {
        e.preventDefault();
        cargando();
        $(".close").trigger('click');
        const idEditar = $("#idcargo").val();
        var formdata = new FormData(this);
        const exentoParaf = $("#exento")[0].checked;
        if (exentoParaf) {
            formdata.append('exento', 1);
        } else {
            formdata.append('exento', 0);
        }
        $.ajax({
            type: 'POST',
            url: `${$(this).attr("action")}/${idEditar}`,
            cache: false,
            processData: false,
            contentType: false,
            data: formdata,
            success: function(data) {
                if (data.success) {
                    retornarAlerta(
                        '¡Hecho!',
                        data.mensaje,
                        'success',
                        'Aceptar'
                    );
                    window.location.reload();
                } else {
                    $("#infoErrorForm").css("display", "block");
                    $("#infoErrorForm").html(data.mensaje);
                }
            },
            error: function(data) {
                const error = data.responseJSON;
                if (error.error_code === 'VALIDATION_ERROR') {
                    mostrarErrores(error.errors);
                } else {
                    console.log("error");
                }
            }
        });
    });

    $("body").on("click", ".detalle", (e) => {
        e.preventDefault();
        cargando();
        const idCargo = e.target.attributes.dataId.value;
        $.ajax({
            type: 'GET',
            url: `/cargos/detalleCargo/${idCargo}`,
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".respForm[data-para='cargos']").html(data);
                $('#cargosModal').modal('show');
            },
            error: function(data) {
                console.log("error");
                console.log(data);
            }
        });
    });

    $("body").on("click", ".eliminar", (e) => {
        const confirmar = confirm('¿Está seguro de realizar esta acción?');
        e.preventDefault();
        cargando();
        const idCargo = e.target.attributes.dataId.value;
        $.ajax({
            type: 'POST',
            url: `/cargos/eliminarCargo/${idCargo}`,
            cache: false,
            processData: false,
            contentType: false,
            success: function(data) {
                if (data.success) {
                    window.location.reload();
                } else {
                    $("#infoErrorForm").css("display", "block");
                    $("#infoErrorForm").html(data.mensaje);
                }
            },
            error: function(data) {
                console.log(data);
            }
        });
    });
});