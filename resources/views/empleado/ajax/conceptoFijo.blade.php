<div class="conceptoFijo" data-id="{{$num}}">
    <div class="row">
        <div class="col-11"></div>
        <div class="col-1 text-right">
            <a href="#" class="btn btn-outline-danger quitarConceptoFijo" data-id="{{$num}}">Quitar</a>
        </div>
    </div>
    <div class="row">
        <div class="col-3">
            <div class="form-group">
                <label for="conFiConcepto{{$num}}" class="control-label">Concepto</label>
                <select class="form-control" id="conFiConcepto{{$num}}" name="conFiConcepto[]">
                    <option value=""></option>                
                    @foreach ($conceptos as $concepto)
                        <option value="{{$concepto->idconcepto}}">{{$concepto->nombre}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label for="conFiUnidad{{$num}}" class="control-label">Unidad</label>
                <select class="form-control" id="conFiUnidad{{$num}}" name="conFiUnidad[]">
                    <option value=""></option>                
                    <option value="DIA">DIA</option>
                    <option value="HORA">HORA</option>
                    <option value="MES">MES</option>
                    <option value="UNIDAD">UNIDAD</option>                                        
                </select>
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label for="conFiValor{{$num}}" class="control-label">Valor</label>
                <input type="text" class="form-control separadorMiles" id="conFiValor{{$num}}" name="conFiValor[]" />
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label for="conFiPorcentaje{{$num}}" class="control-label">Porcentaje</label>
                <input type="text" class="form-control" id="conFiPorcentaje{{$num}}" name="conFiPorcentaje[]" readonly />
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-3">
            <div class="form-group @isset($contratoActivo->fechaInicio)
                hasText
            @endisset">
                <label for="conFiFechaInicio{{$num}}" class="control-label">Fecha Inicio</label>
                <input type="date" class="form-control" id="conFiFechaInicio{{$num}}" name="conFiFechaInicio[]" />
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label for="conFiFechaFin{{$num}}" class="control-label">Fecha Fin</label>
                <input type="date" class="form-control" id="conFiFechaFin{{$num}}" name="conFiFechaFin[]" />
            </div>
        </div>
    </div>
</div>