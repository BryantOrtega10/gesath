<div class="novedadAdicional" data-id="{{$idRow}}">
    @if ($idRow != 0)
        <div class="row">
            <div class="offset-10 col-2 text-right">
                <a href="#" class="btn btn-outline-danger quitarNovedadAdicional" data-id="{{$idRow}}">Quitar</a>
            </div>
        </div>
    @endif
    <div class="row">
        <div class="col-3">
            <div class="form-group busquedaPop busquedaEmpleado" id="busquedaEmpleado{{$idRow}}" data-id="{{$idRow}}">
                <label for="nombreEmpleado{{$idRow}}" class="control-label">Empleado:</label>
                <input type="text" readonly class="form-control nombreEmpleado" id="nombreEmpleado{{$idRow}}" name="nombreEmpleado[]" data-id="{{$idRow}}" />
                <input type="hidden" class="form-control idEmpleado" id="idEmpleado{{$idRow}}" name="idEmpleado[]" data-id="{{$idRow}}" />
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label for="concepto{{$idRow}}" class="control-label">Concepto:</label>
                <select class="form-control" id="concepto{{$idRow}}" name="concepto[]">
                    <option value=""></option>
                    @foreach ($conceptos as $concepto)
                        <option value="{{$concepto->idconcepto}}">{{$concepto->nombre}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label for="fechaAusenciaInicial{{$idRow}}" class="control-label">Fecha Inicial:</label>
                <input type="date" class="form-control" id="fechaAusenciaInicial{{$idRow}}" name="fechaAusenciaInicial[]" min="{{$req->fechaMinima}}" />
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label for="fechaAusenciaFinal{{$idRow}}" class="control-label">Fecha Final:</label>
                <input type="date" class="form-control" id="fechaAusenciaFinal{{$idRow}}" name="fechaAusenciaFinal[]" min="{{$req->fechaMinima}}" />
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-3">
            <div class="form-group hasText">
                <label for="domingoAplica{{$idRow}}" class="control-label">Descuenta Domingo?</label>
                <select class="form-control" id="domingoAplica{{$idRow}}" name="domingoAplica[]">
                    <option value="1">SI</option>
                    <option value="0">NO</option>
                </select>
            </div>
        </div>
    </div>
</div>