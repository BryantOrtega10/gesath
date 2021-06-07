<table class="table table-hover table-striped">
    <tr>
        <th>#</th>
        <th width="120">Fecha Inicio</th>
        <th width="120">Fecha Fin</th>
        <th>Empresa</th>
        <th>Cargo</th>
        <th>Tipo Contrato</th>
        <th>Salario final</th>
    </tr>
    @php
    $i = 1;        
    @endphp
    <tr>
        <td>{{$i}}</td>
        <td>{{$empleado->fechaIngreso}}</td>    
        <td></td>
        <td>{{$empleado->nombreNomina}}</td>
        <td>{{$empleado->nombreCargo}}</td>    
        <td>{{$tipoContrato->nombreTipoContrato}}</td>    
        <td>$ {{number_format($conceptoFijo->valor, 0, ",", ".")}}</td>
    </tr> 
    @php
        $i++;
    @endphp
    @foreach ($periodos as $periodo)
        <tr>
            <td>{{$i}}</td>
            <td>{{$periodo->fechaInicio}}</td>    
            <td>{{$periodo->fechaFin}}</td>
            <td>{{$periodo->nombreNomina}}</td>
            <td>{{$periodo->nombreCargo}}</td>
            <td>{{$periodo->nombreTipoContrato}}</td>
            <td>$ {{number_format($periodo->salario, 0, ",", ".")}}</td>
        </tr>     
        @php
            $i++;
        @endphp
    @endforeach    
    
</table>