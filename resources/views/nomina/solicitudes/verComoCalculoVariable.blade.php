
<h2>{{$concepto->nombre}}</h2>
<table class="table table-hover table-striped">
    <tr>
        <th scope="col">Tipo</th>
        <th scope="col">Nombre</th>
        <th scope="col">Valor</th>
        <th scope="col"></th>
    </tr>
    <tr>
        <td>Variable</td>
        <td>{{$variable->nombre}}</td>
        <td>{{$variable->valor}}</td>
    </tr>   
    <tr>
        <td colspan="2"></td>
        <td>Dividido por:</td>
    </tr> 
    <tr>
        <td>Valor</td>
        <td>Valor Fijo</td>
        <td>30 - DIAS</td>
    </tr>
    <tr>
        <td colspan="2"></td>
        <td>Multiplicado por:</td>
    </tr>
    <tr>
        <td>Cantidad</td>
        <td></td>
        <td>{{$itemBoucherPago->cantidad}} - {{$itemBoucherPago->tipoUnidad}}</td>
    </tr>

    <tr>
        <th>Total</th>
        <td></td>
        <td>$ {{number_format($itemBoucherPago->valor, 0, ",", ".")}}</td>
    </tr>

</table>
