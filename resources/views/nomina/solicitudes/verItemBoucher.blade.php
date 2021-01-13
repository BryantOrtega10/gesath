<div class="row">
    <div class="col-3"><b>Fecha ingreso:</b> <br> <span>{{$empleado->fechaIngreso}}</span></div>
    <div class="col-3"><b>Salario:</b> <br>  <span>$ {{number_format($conceptoSalario->valor,0,",",".")}}</span></div>
    <div class="col-3"><b>Empresa:</b> <br> <span>{{$empleado->razonSocial}}</span></div>
    <div class="col-3"><b>Centro(s) de costo:</b> <br>
        <span>
            @foreach ($centrosCosto as $centroCosto)
                {{$centroCosto->nombre}}<br>
            @endforeach
        </span>
    </div>
</div>
<br>
@isset($novedadesRetiro)
    <div class="row">
        <div class="col-3"><b>Fecha retiro</b> <br> <span>{{$novedadesRetiro->fecha}}</span> </div>
    </div>
@endisset
    <table class="table table-hover table-striped">
        <tr>
            <th scope="col">Concepto</th>
            <th scope="col">Pago</th>
            <th scope="col">Descuento</th>
            <th scope="col">Cantidad</th>
            <th scope="col"></th>
        </tr>
        @php
            $pago = 0;
            $descuento = 0;
        @endphp
        @foreach ($infoBoucher as $infoBouche)
            <tr>
                <td scope="row">
                    {{$infoBouche->nombre}}
                </td>
                <td scope="row">
                    $ {{number_format($infoBouche->pago,0, ",", ".")}}
                </td>
                <td scope="row">
                    $ {{number_format($infoBouche->descuento,0, ",", ".")}}
                </td>
                <td scope="row">
                    @if ($infoBouche->tipoUnidad=="VALOR")
                        {{$infoBouche->tipoUnidad}}
                    @else
                        {{$infoBouche->cantidad}} - {{$infoBouche->tipoUnidad}}    
                    @endif
                    
                </td>
                <td scope="row">
                    @if ($infoBouche->tipoUnidad=="VALOR")
                        Valor fijo novedad
                    @else
                        @if ($infoBouche->fkConcepto =="36")
                            <a href="/nomina/verDetalleRetencion/{{$infoBouche->fkBoucherPago}}/NORMAL" class="verComoCalculo">Ver detalle retención</a>
                        
                        @else
                            @if($infoBouche->fkConcepto =="76")
                                <a href="/nomina/verDetalleRetencion/{{$infoBouche->fkBoucherPago}}/INDEMNIZACION" class="verComoCalculo">Ver detalle retención</a>
                            @else
                                @if($infoBouche->fkConcepto =="28" || $infoBouche->fkConcepto =="29" || $infoBouche->fkConcepto =="30")
                                    <a href="/nomina/verDetalleVacacion/{{$infoBouche->idItemBoucherPago}}" class="verComoCalculo">Ver detalle vacaciones</a>
                                @else
                                    <a href="/nomina/comoCalculo/{{$infoBouche->idItemBoucherPago}}" class="verComoCalculo">Como se calcula</a>                                
                                @endif 
                            @endif 
                           
                        @endif
                    @endif
                </td>
            </tr>
            @php
                $pago = $pago + $infoBouche->pago;
                $descuento = $descuento + $infoBouche->descuento;
            @endphp
        @endforeach
        <tr>
            <th scope="row">Totales</td>
            <th scope="row">
                $ {{number_format($pago,0, ",", ".")}}
            </th>
            <th scope="row">
                $ {{number_format($descuento,0, ",", ".")}}
            </th>
            <th scope="row"></td>
            <th scope="row"></td>
        </tr>
    </table>
