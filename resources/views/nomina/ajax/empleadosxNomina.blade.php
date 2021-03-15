<input type="hidden" name="excluirEmpleados" id="excluirEmpleados" />
@if ($tipoNomina == 7)
    <div class="row">
        <div class="col-3 activo">
            <div class="form-group">
                <label for="fechaPrima" class="control-label">Fecha prima:</label>
                <input type="date" required class="form-control" id="fechaPrima" name="fechaPrima"/>
            </div>
        </div>
    </div>
@endif
@if ($tipoNomina == 6)
    <div class="row">
        <div class="col-3">
            <div class="form-group hasText">
                <label for="tipoliquidacionPrima" class="control-label">Tipo liquidacion prima:</label>
                <select class="form-control" id="tipoliquidacionPrima" name="tipoliquidacionPrima">
                    <option value="1">Valor a una fecha</option>
                    <option value="2">Porcentaje</option>
                    <option value="3">Valor fijo</option>
                </select>
            </div>
        </div>
        <div class="col-3 fechaParaPrima activo">
            <div class="form-group">
                <label for="fechaPrima" class="control-label">Fecha prima:</label>
                <input type="date" class="form-control" id="fechaPrima" name="fechaPrima"/>
            </div>
        </div>
        <div class="col-3 porcentajeParaPrima">
            <div class="form-group">
                <label for="porcentajePrima" class="control-label">Porcentaje prima (%):</label>
                <input type="text" class="form-control" id="porcentajePrima" name="porcentajePrima"/>
            </div>
        </div>
        <div class="col-3 valorFijoParaPrima ">
            <div class="form-group">
                <label for="valorFijoPrima" class="control-label">Valor fijo prima:</label>
                <input type="text" class="form-control separadorMiles" id="valorFijoPrima" name="valorFijoPrima"/>
            </div>
        </div>
    </div>
@endif
<div class="cajaGeneral text-left">
    <div class="container">
        <div class="row">
            <div class="col-7 font-weight-bold">Nombre</div>
            <div class="col-2 font-weight-bold">Documento</div>
            <div class="col-2 font-weight-bold">N&uacute;mero</div>
            <div class="col-1 font-weight-bold">Accion</div>
        </div>
        @foreach ($empleados as $empleado)
            <div class="row empleadoFila" data-id="{{$empleado->idempleado}}">
                <div class="col-7">
                    {{$empleado->primerNombre." ".$empleado->segundoNombre." ".$empleado->primerApellido." ".$empleado->segundoApellido}}
                </div>
                <div class="col-2 font-weight-bold">{{$empleado->nombre}}</div>
                <div class="col-2 font-weight-bold">{{$empleado->numeroIdentificacion}}</div>
                <div class="col-1 font-weight-bold"><a href="#" class="quitarEmpleadoNomina" data-id="{{$empleado->idempleado}}"><i class="fas fa-trash"></i></a></div>

                @if ($empleado->fkTipoCotizante=="51" && $tipoNomina!=3 && $tipoNomina!=7 && $tipoNomina!=10 && $tipoNomina!=11)
                    <div class="col-7"></div>    
                    <div class="col-2">
                        <div class="form-group">
                            <label for="numHoras{{$empleado->idempleado}}" class="control-label">Horas al día:</label>
                            <input type="text" required class="form-control" id="numHoras{{$empleado->idempleado}}" name="numHoras{{$empleado->idempleado}}"/>
                        </div>
                    </div>    
                    <div class="col-2">
                        <div class="form-group">
                            <label for="numDias{{$empleado->idempleado}}" class="control-label">Días:</label>
                            <input type="number" min="1" required class="form-control" id="numDias{{$empleado->idempleado}}" name="numDias{{$empleado->idempleado}}"/>
                        </div>
                    </div>    
                    <div class="col-1"></div>    
                @endif
                
                

            </div>
        @endforeach

        
        <div class="text-center"><input type="submit" value="Liquidar" class="btnSubmitGen" /></div>

    </div>

</div>