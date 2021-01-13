<h2>Detalle vacaciones</h2>
<table class="table table-hover table-striped">
    <tr>
        <th>Fecha ingreso</th>
        <td>{{$empleado->fechaIngreso}}</td>
    </tr>
    <tr>
        <th>Fecha corte</th>
        <td>{{$fechaFinGen}}</td>
    </tr>
    <tr>
        <th>Valor Salario</th>
        <td>${{number_format($empleado->valorSalario,0, ",", ".")}}</td>
    </tr>
    <tr>
        <th>Días Trabajados</th>
        <td>{{$diasTrabajados}}</td>
    </tr>
    <tr>
        <th>Días licencia</th>
        <td>{{$diasLic}}</td>
    </tr>
    <tr>
        <th>Días Neto</th>
        <td>{{$diasNeto}}</td>
    </tr>
    <tr>
        <th>Días vacaciones</th>
        <td>{{$diasVacGen}}</td>
    </tr>
    <tr>
        <th>Días Tomadas</th>
        <td>{{(isset($novedadesVacacionGen->suma) ? $novedadesVacacionGen->suma : 0)}}</td>
    </tr>
    <tr>
        <th>Días Pendientes</th>
        <td>{{round($diasVacGen - (isset($novedadesVacacionGen->suma) ? $novedadesVacacionGen->suma : 0), 2)}}</td>
    </tr>
</table>
<h3>Vacaciones:</h3>
<table class="table table-hover table-striped">
    @foreach ($arrDatos as $datoCau)
        @foreach($datoCau['disfrute'] as $disf)
        <tr>
            <td class="azul2">{{$disf['diaIni']}}</td>
            <td class="azul2">{{$disf['diaFin']}}</td>
            <td class="azul2">{{$disf['diaTom']}}</td>
        </tr>
        @endforeach
    @endforeach
</table>
