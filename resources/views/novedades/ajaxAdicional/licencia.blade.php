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
                <label for="fechaInicial{{$idRow}}" class="control-label">Fecha Inicial:</label>
                <input type="date" class="form-control fechaInicial" data-id="{{$idRow}}" id="fechaInicial{{$idRow}}" name="fechaInicial[]" />
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label for="dias{{$idRow}}" class="control-label">Dias:</label>
                <input type="text" class="form-control dias" data-id="{{$idRow}}" id="dias{{$idRow}}" name="dias[]" />
            </div>
        </div>
    </div>
    <div class="row">
        
        <div class="col-3">
            <div class="form-group">
                <label for="fechaFinal{{$idRow}}" class="control-label">Fecha Final:</label>
                <input type="date" class="form-control" id="fechaFinal{{$idRow}}" name="fechaFinal[]" readonly  />
            </div>
        </div>
    </div>
</div>