<div class="centroCosto" data-id="{{$num}}">
    <input type="hidden" name="idEmpleadoCentroCosto[]" value="-1" />
    <div class="row">
        <div class="col-11"></div>
        <div class="col-1 text-right">
            <a href="#" class="btn btn-outline-danger quitarCentroCosto" data-id="{{$num}}">Quitar</a>
        </div>
    </div>
    <div class="row">
        <div class="col-3">
            <div class="form-group">
                <label for="infoCentroCosto{{$num}}" class="control-label">Centro de costo *</label>
                <select class="form-control" id="infoCentroCosto{{$num}}" name="infoCentroCosto[]">
                    <option value=""></option>
                    @foreach ($centrosCosto as $centroCosto)
                        <option value="{{$centroCosto->idcentroCosto}}">{{$centroCosto->nombre}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-3">
            <div class="form-group hasText">
                <label for="infoPorcentaje{{$num}}" class="control-label">Porcentaje *</label>
                <input type="text" class="form-control" id="infoPorcentaje{{$num}}" name="infoPorcentaje[]" readonly value=""/>                    
            </div>
        </div>
    </div>
</div>