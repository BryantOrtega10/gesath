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
    $('#prestamos_table').DataTable({
        language: {
            "sProcessing": "Procesando...",
            "sLengthMenu": "Mostrar _MENU_ registros",
            "sZeroRecords": "No se encontraron resultados",
            "sEmptyTable": "Ningún dato disponible en esta tabla",
            "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
            "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
            "sSearch": "Buscar:",
            "sInfoThousands": ",",
            "sLoadingRecords": "Cargando...",
            "oPaginate": {
                "sFirst": "Primero",
                "sLast": "Último",
                "sNext": "Siguiente",
                "sPrevious": "Anterior"
            },
            "oAria": {
                "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            },
            "buttons": {
                "copy": "Copiar",
                "colvis": "Visibilidad"
            }
        }
    });
    $("body").on("change", "#montoInicial", function(e) {
        $("#valorTotalEmbargo").val($(this).val());
        $("#valorTotalEmbargo").trigger("change");
    });
    $("body").on("change", "#depto", function() {
        cargando();
        var valorUbicacion = $(this).val();
        $("#ciudad").html('<option value=""></option>');
        $("#ciudad").trigger("change");
        if (valorUbicacion != "") {
            $.ajax({
                type: 'GET',
                url: "/ubicaciones/obtenerHijos/" + valorUbicacion,
                success: function(data) {
                    $("#cargando").css("display", "none");
                    $("#ciudad").html(data.opciones);
                },
                error: function(data) {
                    console.log("error");
                    console.log(data);
                }
            });
        }
    });


    $(".separadorMiles").inputmask({ alias: "currency", removeMaskOnSubmit: true });
    $("body").on("change", "#tipoDesc", function(e) {
        $(".presCuotas").removeClass("activo");
        $(".presValor").removeClass("activo");
        $(".presPorcentaje").removeClass("activo");

        if ($(this).val() == "1") {
            $(".presCuotas").addClass("activo");
        } else if ($(this).val() == "2") {
            $(".presValor").addClass("activo");
        } else if ($(this).val() == "3") {
            $(".presPorcentaje").addClass("activo");
        }
    });



    $("body").on("change", "#infoEmpresa", function(e) {
        e.preventDefault();

        $("#infoNomina").html('<option value=""></option>');
        $("#infoNomina").trigger("change");

        const idEmpresa = $(this).val();
        if (idEmpresa != "") {
            cargando();
            $.ajax({
                type: 'GET',
                url: "/empleado/cargarDatosPorEmpresa/" + idEmpresa,
                success: function(data) {
                    $("#cargando").css("display", "none");
                    $("#infoNomina").html(data.opcionesNomina);
                },
                error: function(data) {
                    console.log("error");
                    console.log(data);
                }
            });
        }

    });
    $("body").on("change", "#infoNomina", function(e) {
        e.preventDefault();


        $("#periocidad").html('<option value=""></option>');
        $("#periocidad").trigger("change");

        const idNomina = $(this).val();
        if (idNomina != "") {
            cargando();
            $.ajax({
                type: 'GET',
                url: "/prestamos/periocidadxNomina/" + idNomina,
                success: function(data) {
                    $("#cargando").css("display", "none");
                    $("#periocidad").html(data.opcionesPeriocidad);
                },
                error: function(data) {
                    console.log("error");
                    console.log(data);
                }
            });
        }

    });
    $("body").on("click", ".recargar", function() {
        cargando();
        $.ajax({
            type: 'GET',
            url: "/empleado/cargarFormEmpleadosxNomina?idNomina=" + $("#infoNomina").val(),
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".resFormBusEmpleado").html(data);
                $('#busquedaEmpleadoModal').modal('show');
            },
            error: function(data) {
                console.log("error");
                console.log(data);
            }
        });
    });
    $("body").on("click", "#busquedaEmpleado", function() {
        cargando();
        $.ajax({
            type: 'GET',
            url: "/empleado/cargarFormEmpleadosxNomina?idNomina=" + $("#infoNomina").val(),
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".resFormBusEmpleado").html(data);
                $('#busquedaEmpleadoModal').modal('show');
            },
            error: function(data) {
                console.log("error");
                console.log(data);
            }
        });
    });

    $("body").on("submit", "#filtrarEmpleado", function(e) {
        e.preventDefault();
        cargando();

        var formdata = $('#filtrarEmpleado').serialize();
        $.ajax({
            type: 'GET',
            url: $(this).attr("action"),
            data: formdata,
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".resFormBusEmpleado").html(data);
            },
            error: function(data) {
                console.log("error");
                console.log(data);
            }
        });

    });
    $("body").on("click", ".resFormBusEmpleado .pagination a", function(e) {
        e.preventDefault();
        cargando();
        $.ajax({
            type: 'GET',
            url: $(this).attr("href"),
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".resFormBusEmpleado").html(data);
            },
            error: function(data) {
                console.log("error");
                console.log(data);
            }
        });
    });
    $("body").on("click", ".resFormBusEmpleado a.seleccionarEmpleado", function(e) {
        e.preventDefault();
        $("#nombreEmpleado").val($(this).html().trim());
        $("#nombreEmpleado").trigger("change");
        $("#idEmpleado").val($(this).attr("data-id"));

        $('#busquedaEmpleadoModal').modal('hide');

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
                $("#cargando").css("display", "none");
                if (data.success) {
                    alert(data.mensaje);
                    window.open(data.url, "_self");
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
    $("body").on("click", ".eliminarPrestamo", function(e) {
        e.preventDefault();
        if (confirm("En verdad desea eliminar este prestamo?")) {
            if (typeof $("#cargando")[0] !== 'undefined') {
                $("#cargando").css("display", "flex");
            } else {
                $("body").append('<div id="cargando" style="display: flex;">Cargando...</div>');
            }
            $.ajax({
                type: 'GET',
                url: $(this).attr("href"),
                cache: false,
                success: function(data) {
                    $("#cargando").css("display", "none");
                    if (data.success) {
                        alert(data.mensaje);
                        window.open(data.url, "_self");
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
        }

    });
});