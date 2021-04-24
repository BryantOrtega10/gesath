<table class="table table-hover table-striped">
    <tr>
        <th>#</th>
        <th>Fecha Inicio</th>
        <th>Fecha Fin</th>
        <th>Salario final</th>
    </tr>
    @php
    $i = 1;        
    @endphp
    @foreach ($periodos as $periodo)
        <tr>
            <td>{{$i}}</td>
            <td>{{$periodo->fechaInicio}}</td>    
            <td>{{$periodo->fechaFin}}</td>
            <td>$ {{number_format($periodo->salario, 0, ",", ".")}}</td>
        </tr>     
        @php
            $i++;
        @endphp
    @endforeach    
    <tr>
        <td>{{$i}}</td>
        <td>{{$empleado->fechaIngreso}}</td>    
        <td></td>
        <td>$ {{number_format($conceptoFijo->valor, 0, ",", ".")}}</td>
    </tr> 
</table>