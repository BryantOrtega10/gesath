function cargando() {
    if (typeof $("#cargando")[0] !== 'undefined') {
        $("#cargando").css("display", "flex");
    } else {
        $("body").append('<div id="cargando" style="display: flex;">Cargando...</div>');
    }
}
$(document).ready(function() {
    $("#grupo_concepto").DataTable();

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
        $(".conceptoMasCont[data-id='" + $(this).attr("data-id") + "']").remove();
    });


    $("body").on("click", "#masConceptos", function(e) {
        e.preventDefault();
        cargando();
        var numConceptos = $("#numConceptos").val();
        numConceptos++;
        var url = "/grupoConcepto/getForm/masConceptos/" + numConceptos;

        $.ajax({
            type: 'GET',
            url: url,
            success: function(data) {
                $("#cargando").css("display", "none");

                $("#numConceptos").val(numConceptos)
                $('.conceptosCont').append(data);
            },
            error: function(data) {
                console.log("error");
                console.log(data);
            }
        });
    });



    $("#addGrupoConcepto").click(function(e) {
        e.preventDefault();
        cargando();
        $.ajax({
            type: 'GET',
            url: "/grupoConcepto/getForm/add",
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".respForm[data-para='grupoConcepto']").html(data);
                $('#grupoConceptoModal').modal('show');
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
                console.log("error");
                console.log(data);
            }
        });
    });

    $(".editar").click((e) => {
        e.preventDefault();
        const idCentroCosto = e.target.attributes.dataid.value;
        cargando();
        $.ajax({
            type: 'GET',
            url: `/grupoConcepto/edit/${idCentroCosto}`,
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".respForm[data-para='grupoConcepto']").html(data);
                $('#grupoConceptoModal').modal('show');
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
        const idCentroCosto = e.target.attributes.dataid.value;

        $.ajax({
            type: 'GET',
            url: `/grupoConcepto/detail/${idCentroCosto}`,
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".respForm[data-para='grupoConcepto']").html(data);
                $('#grupoConceptoModal').modal('show');
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
            const idCentroCosto = e.target.attributes.dataid.value;
            $.ajax({
                type: 'POST',
                url: `/grupoConcepto/delete/${idCentroCosto}`,
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

    $("body").on("click", ".adicional_con", (e) => {
        let idDom = e.target.attributes.dataId.value;
        idDom = parseInt(idDom);
        idDom++;
        e.target.attributes.dataId.value = idDom;
        $.ajax({
            type: 'GET',
            url: `/grupoConcepto/grupoCon/newGrupoCon/${idDom}`,
            cache: false,
            processData: false,
            contentType: false,
            success: function(data) {
                $(".conceptos").append(data);
            },
            error: function(data) {
                console.log(data);
            }
        });
    });
    $("body").on("click", ".elim_con", (e) => {
        const id = e.target.attributes['data-id'].value;
        $(`.conceptos_${id}`).remove();
    });
});