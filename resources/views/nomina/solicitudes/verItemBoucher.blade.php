<div class="row">
    <div class="col-3"><b>Fecha ingreso:</b> <br> <span>{{$periodo_activo->fechaInicio}}</span></div>
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
@if ($empleado->fkTipoCotizante == 51)
    
    <form method="GET" class="formGeneral" autocomplete="off"> 
        <input type="hidden" name="idBoucherPago" value="" />
        <div class="row">
            <div class="col-4">
                <div class="form-group hasText">
                    <label for="numHoras{{$empleado->idempleado}}" class="control-label">Horas al día:</label>
                    <input type="text" value="{{($boucher->horasPeriodo / $boucher->periodoPago)}}" data-id="{{$boucher->idBoucherPago}}" required 
                        class="form-control numHoras" id="numHoras{{$empleado->idempleado}}" name="numHoras{{$empleado->idempleado}}"/>
                </div>
            </div>
            <div class="col-4">
                <div class="form-group hasText">
                    <label for="numDias{{$empleado->idempleado}}" class="control-label">Días:</label>
                    <input type="number" value="{{$boucher->periodoPago}}" min="1" data-id="{{$boucher->idBoucherPago}}" required 
                    class="form-control numDias" data-id="{{$boucher->idBoucherPago}}" id="numDias{{$empleado->idempleado}}" name="numDias{{$empleado->idempleado}}"/>
                </div>
            </div>
            <div class="col-4 ">
                <a href="/nomina/recalcularyCambioComprobante/{{$boucher->idBoucherPago}}" class="recalcularCambio btnSubmitGen text-center" data-id="{{$boucher->idBoucherPago}}">Recalcular con cambio</a>
            </div>
        </div>
    </form>
@endif
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
                            <a href="/nomina/verDetalleRetencion/{{$infoBouche->fkBoucherPago}}/NORMAL" class="verComoCalculo">Ver detalle retención</a><br>
                            <a href="/nomina/comoCalculo/{{$infoBouche->idItemBoucherPago}}" class="verComoCalculo">Como se calcula</a>                                
                        @else
                            @if($infoBouche->fkConcepto =="76")
                                <a href="/nomina/verDetalleRetencion/{{$infoBouche->fkBoucherPago}}/INDEMNIZACION" class="verComoCalculo">Ver detalle retención</a>
                            @else
                                @if($infoBouche->fkConcepto =="77")
                                    <a href="/nomina/verDetalleRetencion/{{$infoBouche->fkBoucherPago}}/PRIMA" class="verComoCalculo">Ver detalle retención</a>
                                @else
                                    @if($infoBouche->fkConcepto =="28" || $infoBouche->fkConcepto =="29" || $infoBouche->fkConcepto =="30")
                                        <a href="/nomina/verDetalleVacacion/{{$infoBouche->idItemBoucherPago}}" class="verComoCalculo">Ver detalle vacaciones</a>
                                    @else
                                        <a href="/nomina/comoCalculo/{{$infoBouche->idItemBoucherPago}}" class="verComoCalculo">Como se calcula</a>                                
                                    @endif 
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
        <tr>
            <td colspan="5" class="provCont">
                @foreach ($provisiones as $provision)
                    @if ($provision->fkConcepto == 73)
                        <a href="/nomina/verDetalleProvision/{{$infoBouche->fkBoucherPago}}/73" class="verComoCalculo">Provisión prima $ {{number_format($provision->valor, 0, ",", ".")}}</a>        
                    @endif
                    @if ($provision->fkConcepto == 71)
                        <a href="/nomina/verDetalleProvision/{{$infoBouche->fkBoucherPago}}/71" class="verComoCalculo">Provisión cesantias $ {{number_format($provision->valor, 0, ",", ".")}}</a>        
                    @endif
                    @if ($provision->fkConcepto == 72)
                        <a href="/nomina/verDetalleProvision/{{$infoBouche->fkBoucherPago}}/72" class="verComoCalculo">Provisión intereses $ {{number_format($provision->valor, 0, ",", ".")}}</a>        
                    @endif
                    @if ($provision->fkConcepto == 74)
                        <a href="/nomina/verDetalleProvision/{{$infoBouche->fkBoucherPago}}/74" class="verComoCalculo">Provisión vacaciones $ {{number_format($provision->valor, 0, ",", ".")}}</a>        
                    @endif
                @endforeach
                
            </td>
        </tr>
    </table>