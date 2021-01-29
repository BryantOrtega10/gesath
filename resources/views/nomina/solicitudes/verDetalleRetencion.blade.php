
<h2>RETENCIÓN EN LA FUENTE</h2>
<table class="table table-hover table-striped">
    <tr>
        <th>Ingreso</th>
        <td>{{number_format($retencion->ingreso,0, ",", ".")}}</td>
    </tr>
    <tr>
        <th>Seguridad Social</th>
        <td>{{number_format($retencion->seguridadSocial,0, ",", ".")}}</td>
    </tr>
    <tr>
        <th>Intereses Vivienda</th>
        <td>{{number_format($retencion->interesesVivienda,0, ",", ".")}}</td>
    </tr>
    <tr>
        <th>Medicina Prepagada</th>
        <td>{{number_format($retencion->medicinaPrepagada,0, ",", ".")}}</td>
    </tr>
    <tr>
        <th>Dependiente</th>
        <td>{{number_format($retencion->dependiente,0, ",", ".")}}</td>
    </tr>
    <tr>
        <th>Aporte Voluntario</th>
        <td>{{number_format($retencion->aporteVoluntario,0, ",", ".")}}</td>
    </tr>
    <tr>
        <th>AFC</th>
        <td>{{number_format($retencion->AFC,0, ",", ".")}}</td>
    </tr>
    <tr>
        <th>Parte exenta</th>
        <td>{{number_format($retencion->exenta,0, ",", ".")}}</td>
    </tr>
    <tr>
        <th>Parte exenta sin aportes voluntarios</th>
        <td>{{number_format($retencion->exentaSinAportes,0, ",", ".")}}</td>
    </tr>
    <tr>
        <th>Total beneficios tributarios</th>
        <td>{{number_format($retencion->totalBeneficiosTributarios,0, ",", ".")}}</td>
    </tr>
    <tr>
        <th>Total beneficios tributarios sin aportes voluntarios</th>
        <td>{{number_format($retencion->totalBeneficiosTributariosSinAportes,0, ",", ".")}}</td>
    </tr>
    <tr>
        <th>Impuesto en uvts</th>
        <td>{{number_format($retencion->impuestoUVT,0, ",", ".")}}</td>
    </tr>
    <tr>
        <th>Impuesto en uvts sin aportes voluntarios</th>
        <td>{{number_format($retencion->impuestoSinAportesUVT,0, ",", ".")}}</td>
    </tr>
    <tr>
        <th>Impuesto</th>
        <td>{{number_format($retencion->impuestoValor,0, ",", ".")}}</td>
    </tr>  
    <tr>
        <th>Impuesto sin aportes voluntarios</th>
        <td>{{number_format($retencion->impuestoValorSinAportes,0, ",", ".")}}</td>
    </tr>   
    <tr>
        <th>Divido en:</th>
        <td>30 DIAS</td>
    </tr>         
    <tr>
        <th>Multiplicado por:</th>
        <td>{{$itemBoucherPago->cantidad}} DIAS</td>
    </tr>         
    <tr>
        <th>TOTAL:</th>
        <th>{{number_format($itemBoucherPago->valor * -1,0, ",", ".")}}</th>
    </tr>       
</table>
