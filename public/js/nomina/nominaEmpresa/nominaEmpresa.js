function cargando() {
    if (typeof $("#cargando")[0] !== 'undefined') {
        $("#cargando").css("display", "flex");
    } else {
        $("body").append('<div id="cargando" style="display: flex;">Cargando...</div>');
    }
}
$(document).ready(function() {
    $("#nominas").DataTable({
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

    $("#addNominaEmpresa").click(function(e) {
        e.preventDefault();
        cargando();
        $.ajax({
            type: 'GET',
            url: `/empresa/nomina/formAdd/${$(this).attr("dataId")}`,
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".respForm[data-para='nominaEmpresa']").html(data);
                $('#nominaEmpresaModal').modal('show');
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
        const idCentroCosto = e.target.attributes.dataId.value;
        cargando();
        $.ajax({
            type: 'GET',
            url: `/empresa/nomina/edit/${idCentroCosto}`,
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".respForm[data-para='nominaEmpresa']").html(data);
                $('#nominaEmpresaModal').modal('show');
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
        var formdata = new FormData(this);
        $.ajax({
            type: 'POST',
            url: `${$(this).attr("action")}`,
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
        const idCentroCosto = e.target.attributes.dataId.value;

        $.ajax({
            type: 'GET',
            url: `/empresa/nomina/detail/${idCentroCosto}`,
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".respForm[data-para='nominaEmpresa']").html(data);
                $('#nominaEmpresaModal').modal('show');
            },
            error: function(data) {
                console.log("error");
                console.log(data);
            }
        });
    });

    $("body").on("click", ".eliminar", (e) => {
        const confirmar = confirm('¿Está seguro de realizar esta acción?');
        if (confirmar) {
            e.preventDefault();
            cargando();
            const idCentroCosto = e.target.attributes.dataId.value;
            $.ajax({
                type: 'POST',
                url: `/empresa/nomina/delete/${idCentroCosto}`,
                cache: false,
                processData: false,
                contentType: false,
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
                        retornarAlerta(
                            '¡Error!',
                            data.mensaje,
                            'error',
                            'Aceptar'
                        );
                        $("#infoErrorForm").css("display", "block");
                        $("#infoErrorForm").html(data.mensaje);
                    }
                },
                error: function(data) {
                    console.log(data);
                }
            });
        }
    });
});