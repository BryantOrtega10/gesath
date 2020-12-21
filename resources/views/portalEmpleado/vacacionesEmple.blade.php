<h3 style = "text-align: center;">Vacaciones disponibles</h3>
<div class="row puntero">
    <div class="col">
        @if ($diasVac > 0)
            <h5>Días disponibles para distrutar:</h5>
            <table class = "table table-bordered table-hover table-sm">
                <tr>
                    <th>Días vacaciones</th>
                    <td>{{ $diasVac }} DÍAS</td>
                </tr>
                <tr>
                    <th>Fecha ingreso empleado</th>
                    <td>{{ $fechaIngreso }}</td>
                </tr>
                <tr>
                    <th>Fecha corte</th>
                    <td>{{ $fechaCorteCalculo }}</td>
                </tr>
            </table>
        @else
            <h4>No hay vacaciones disponibles por disfrutar</h4>
        @endif
    </div>
    
</div>