function cargando() {
    if (typeof $("#cargando")[0] !== 'undefined') {
        $("#cargando").css("display", "flex");
    } else {
        $("body").append('<div id="cargando" style="display: flex;">Cargando...</div>');
    }
}
$(document).ready(function() {
    $("#codigos").DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.22/i18n/Spanish.json'
        },
        "ajax": {
            "url": "/codigos/traerCodigos",
            "dataSrc": "",
            "type": "get",
            "dataType": 'JSON'
        },
        "autoWidth": false,
        responsive: true,
        "deferRender": true,
        columns: [{
            'data': 'idCodDiagnostico',
            "orderable": true,
            'searchable': true,
        }, {
            'data': 'nombre',
            "orderable": true,
            'searchable': true
        }, {
            'data': 'idCodDiagnostico',
            "orderable": false,
            'searchable': false,
            "render": (data, type, full, meta) => {
                return `
                    <div class="dropdown">
                        <i class="fas fa-ellipsis-v dropdown-toggle" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false" id="dropdownMenuButton"></i>
                        <div class="dropdown-menu"  aria-labelledby="dropdownMenuButton">
                            <a data-id ="${ data }" class="dropdown-item detalle"><i class="far fa-eye"></i> Ver código</a>
                            <a data-id ="${ data }" class="dropdown-item editar"><i class="fas fa-edit"></i> Editar código</a>
                            <a data-id ="${ data }" class="dropdown-item color_rojo eliminar"><i class="fas fa-trash"></i> Eliminar código</a>
                        </div>
                    </div>
                `;
            },
        }],
    });

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $("#addCodigo").click(function(e) {
        e.preventDefault();
        cargando();
        $.ajax({
            type: 'GET',
            url: "/codigos/getFormAdd",
            success: function(data) {
                $(".respForm[data-para='codigos']").html(data);
                $('#codigosModal').modal('show');
            },
            error: function(data) {
                console.log("error");
                console.log(data);
            }
        });
    });
    $("body").on("submit", ".formGen", function(e) {
        e.preventDefault();
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

    $("#codigos").on('click', ".editar", function(e) {
        e.preventDefault();
        const idCodigo = $(this).attr('data-id');
        console.log(idCodigo);
        $.ajax({
            type: 'GET',
            url: `/codigos/datosCodigoXId/${idCodigo}`,
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".respForm[data-para='codigos']").html(data);
                $('#codigosModal').modal('show');
            },
            error: function(data) {
                console.log("error");
                console.log(data);
            }
        });
    });

    $("body").on("submit", ".formEdit", function(e) {
        e.preventDefault();
        const idEditar = $("#idCodDiagnostico").val();
        var formdata = new FormData(this);
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

    $("#codigos").on('click', ".detalle", function(e) {
        e.preventDefault();
        const idCodigo = $(this).attr('data-id');
        console.log(idCodigo);
        $.ajax({
            type: 'GET',
            url: `/codigos/detalleCodigo/${idCodigo}`,
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".respForm[data-para='codigos']").html(data);
                $('#codigosModal').modal('show');
            },
            error: function(data) {
                console.log("error");
                console.log(data);
            }
        });
    });

    $("#codigos").on('click', ".eliminar", function(e) {
        const confirmar = confirm('¿Está seguro de realizar esta acción?');
        const idCodigo = $(this).attr('data-id');
        console.log(idCodigo);
        if (confirmar) {
            $.ajax({
                type: 'POST',
                url: `/codigos/eliminarCodigo/${idCodigo}`,
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
        }
    });
});