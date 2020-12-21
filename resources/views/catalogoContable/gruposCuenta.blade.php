<div class="contGrupoCuenta" data-id="{{$num}}">
    <button type="button" class="btn btn-danger quitarGrupo" data-id="{{$num}}"><i class="fas fa-window-close"></i></button>
    <div class="form-group">
        <label for="tablaConsulta{{$num}}" class="control-label">Tipo:</label>
        <select class="form-control tablaConsulta" id="tablaConsulta0" data-id="{{$num}}" required name="tablaConsulta[]">
            <option value="">Seleccione uno</option>
            <option value="1">Grupo concepto</option>
            <option value="2">Provision</option>
            <option value="3">Aporte empleador</option>
        </select>
    </div>
    <div class="form-group grupoConceptoCuenta" data-id="{{$num}}">
        <label for="fkGrupoConcepto{{$num}}" class="control-label">Grupo concepto *</label>
        <select class="form-control" id="fkGrupoConcepto{{$num}}" name="fkGrupoConcepto[]">
            <option value="">Seleccione uno</option>
            @foreach($gruposConcepto as $grupoConcepto)
                <option value="{{$grupoConcepto->idgrupoConcepto}}">{{$grupoConcepto->nombre}}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group grupoProvision" data-id="{{$num}}">
        <label for="subTipoProvision{{$num}}" class="control-label">Tipo Provision *</label>
        <select class="form-control" id="subTipoProvision{{$num}}" name="subTipoProvision[]">
            <option value="">Seleccione uno</option>
            <option value="1">PRIMA</option>
            <option value="2">CESANTIAS</option>
            <option value="3">INTERESES DE CESANTIA</option>
            <option value="4">VACACIONES</option>
        </select>
    </div>
    <div class="form-group grupoAporteEmpleador" data-id="{{$num}}">
        <label for="subTipoAporteEmpleador{{$num}}" class="control-label">Tipo Aporte *</label>
        <select class="form-control" id="subTipoAporteEmpleador0" name="subTipoAporteEmpleador[]">
            <option value="">Seleccione uno</option>
            <option value="1">PENSIÓN</option>
            <option value="2">SALUD</option>
            <option value="3">ARL</option>
            <option value="4">CCF</option>
            <option value="5">ICBF</option>
            <option value="6">SENA</option>
        </select>
    </div>
</div>