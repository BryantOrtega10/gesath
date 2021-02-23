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
                <label for="horaInicial{{$idRow}}" class="control-label">Hora Inicial:</label>
                <input type="datetime-local" class="form-control" id="horaInicial{{$idRow}}" name="horaInicial[]" />
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label for="horaFinal{{$idRow}}" class="control-label">Hora Final:</label>
                <input type="datetime-local" class="form-control" id="horaFinal{{$idRow}}" name="horaFinal[]"  />
            </div>
        </div>
    </div>   
</div> 