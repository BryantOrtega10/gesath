<h2>Detalle vacaciones</h2>
<h3>Vacaciones Tomadas:</h3>
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
<h3>Calculo</h3>
{!!$itemBoucherPago->comoCalcula!!}