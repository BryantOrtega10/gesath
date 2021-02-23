<div class="row">
    <div class="col-12">
        <h2>Seleccione un comprobante para descargar</h2><br>
    </div>
    <form method = "POST" class = "col-12 buscarComprobantesPorFecha anchocompleto">
        <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                <div class="form-group">
                    <label for="fechaInicio">Fecha Inicio</label>
                    <input type="date" name="fechaInicio" id="fechaInicio" class = "form-control" required>
                </div>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                <div class="form-group">
                    <label for="fechaFin">Fecha Fin</label>
                    <input type="date" name="fechaFin" id="fechaFin" class = "form-control" required>
                </div>
            </div>
            <div class="col">
                <button type = "submit" class = "btn btn-primary">Buscar</button>
            </div>
        </div>
    </form>
    <div class = "col-12">
        <table class="table table-hover table-striped" id="comprobantes">
            <thead>
                <tr>
                    <td>ID comprobante</td>
                    <td>Fecha inicio</td>
                    <td>Fecha Fin</td>
                    <td></td>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
<script>
    $(document).ready(() => {
        var tabla = $("#comprobantes").DataTable({
            "ajax": {
                "url": `/portal/comprobantesPago/${ {{ $idEmple }} }`,
                "dataSrc": "",
                "type": "get",
                "dataType": 'JSON'
            },
            "oLanguage": {
                "sProcessing": "Procesando...",
                "sLengthMenu": "Mostrar _MENU_ registros",
                "sZeroRecords": "No se encontraron resultados",
                "sEmptyTable": "No se encontraron resultados",
                "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                "sInfoPostFix": "",
                "sUrl": "",
                "sInfoThousands": ",",
                "sLoadingRecords": "Cargando...",
                "oPaginate": {
                    "sFirst": "Primero",
                    "sLast": "Ultimo",
                    "sNext": "Siguiente",
                    "sPrevious": "Anterior"
                },
            },
            "autoWidth": false,
            responsive: true,
            "deferRender": true,
            "searching": false,
            columns: [{
                'data': 'idBoucherPago',
                "orderable": false,
                'searchable': false,
            }, {
                'data': 'fechaInicio',
                "orderable": true,
                'searchable': true
            }, {
                'data': 'fechaFin',
                "orderable": false,
                'searchable': false
            }, {
                'data': 'idBoucherPago',
                "orderable": false,
                'searchable': false,
                "render": (data, type, full, meta) => {
                    return `
                        <form method = "GET" action = "/reportes/comprobantePdf/${data}">
                            <button class "btn btn-link">Descargar&nbsp;<i class="fas fa-download"></i></button>
                        </form>
                    `;
                },
            }],
        });

        $("body").on("submit", ".buscarComprobantesPorFecha", (e) => {
            e.preventDefault();
            const formData = new FormData($('.buscarComprobantesPorFecha')[0]);
            solicitudAjax(`/portal/buscarComprobantes/${ {{ $idEmple }} }`, 'POST', formData,
                (data) => {
                    tabla.clear().draw();
                    tabla.rows.add(data);
                    tabla.columns.adjust().draw();
                }, (err) => {
                    console.log(err);
                }
            );
        });
    });

    
</script>