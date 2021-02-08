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
                <label for="fechaRetiro{{$idRow}}" class="control-label">Fecha Retiro:</label>
                <input type="date" class="form-control fechaRetiro" data-id="{{$idRow}}" id="fechaRetiro{{$idRow}}" name="fechaRetiro[]"  />
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label for="fechaRetiroReal{{$idRow}}" class="control-label">Fecha Real:</label>
                <input type="date" class="form-control" id="fechaRetiroReal{{$idRow}}" name="fechaRetiroReal[]"  />
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label for="motivoRetiro{{$idRow}}" class="control-label">Motivo Retiro:</label>
                <select class="form-control" id="motivoRetiro{{$idRow}}" name="motivoRetiro[]">
                    <option value=""></option>
                    @foreach ($motivosRetiro as $motivoRetiro)
                        <option value="{{$motivoRetiro->idMotivoRetiro}}">{{$motivoRetiro->nombre}}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-3">
            <div class="form-group">
                <label for="indemnizacion{{$idRow}}" class="control-label">Indemnizacion:</label>
                <select class="form-control" id="indemnizacion{{$idRow}}" name="indemnizacion[]">
                    <option value=""></option>
                    <option value="1">SI</option>
                    <option value="0">NO</option>                   
                </select>
            </div>
        </div>
    </div>
</div>