
<h2>{{$concepto->nombre}}</h2>
<table class="table table-hover table-striped">
    <tr>
        <th scope="col">Tipo</th>
        <th scope="col">Nombre</th>
        <th scope="col">Valor</th>
        <th scope="col"></th>
    </tr>
    @foreach ($arrFormulas as $arrFormula)
        @if ($arrFormula["valor1_tipo"] != "formulaConcepto")
            <tr>
                <td>{{$arrFormula["valor1_tipo"]}}</td>
                @if ($arrFormula["valor1_tipo"] == "Grupo Concepto")
                    <td>
                        <a href="/nomina/verDetalleGrupoConcepto/{{$arrFormula["valor1_idgrupoConcepto"]}}/{{$itemBoucherPago->idItemBoucherPago}}">
                            {{$arrFormula["valor1_nombre"]}}
                        </a>
                    </td>
                @else
                    <td>{{$arrFormula["valor1_nombre"]}}</td>    
                @endif                
                <td>{{number_format($arrFormula["valor1"], 0, ",", ".")}}</td>
            </tr>            
        @endif        
        <tr>
            <td colspan="2"></td>
            <td>{{$arrFormula["operacion"]}}</td>
        </tr>
        <tr>
            <td>{{$arrFormula["valor2_tipo"]}}</td>
            @if ($arrFormula["valor2_tipo"] == "Grupo Concepto")
                <td>
                    <a href="/nomina/verDetalleGrupoConcepto/{{$arrFormula["valor2_idgrupoConcepto"]}}/{{$itemBoucherPago->idItemBoucherPago}}">
                        {{$arrFormula["valor2_nombre"]}}
                    </a>
                </td>
            @else
                <td>{{$arrFormula["valor2_nombre"]}}</td>    
            @endif                
            <td>{{ $arrFormula["valor2"] }}</td>
        </tr>
    @endforeach    
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
        @if ($itemBoucherPago->tipo == "novedadAus")
            <td>$ {{number_format($itemBoucherPago->descuento, 0, ",", ".")}}</td>
        @else
        <td>$ {{number_format($itemBoucherPago->valor, 0, ",", ".")}}</td>
        @endif
    </tr>

</table>
