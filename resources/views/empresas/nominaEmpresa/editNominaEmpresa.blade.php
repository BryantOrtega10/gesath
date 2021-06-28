<form class="formEdit" action = "/empresa/nomina/update/{{ $nominaEmpresa->idNomina }}" method = "POST">
    <input type="hidden" id = "fkEmpresa" name="fkEmpresa" value = "{{ $nominaEmpresa->fkEmpresa }}">
    <div class="form-group">
        <label for="nombre">Nombre</label>
        <input value = "{{ $nominaEmpresa->nombre }}" type="text" class="form-control" id="nombre" name = "nombre">
    </div>
    <div class="form-group">
        <label for="tipoPeriodo">Tipo periodo</label>
        <input value = "{{ $nominaEmpresa->tipoPeriodo }}" type="text" class="form-control" id="tipoPeriodo" name = "tipoPeriodo">
    </div>
    <div class="form-group">
        <label for="periodo">Periodo</label>
        <select class="form-control" id="periodo" name = "periodo">
            <option value="15" @if ($nominaEmpresa->periodo == "15") selected @endif>15 </option>
            <option value="30" @if ($nominaEmpresa->periodo == "30") selected @endif>30 </option>
        </select>        
    </div>
    <div class="form-group">
        <label for="dias_cesantias">Dias cesantias</label>
        <select class="form-control" id="dias_cesantias" name = "dias_cesantias">
            <option value="30" @if ($nominaEmpresa->diasCesantias == "30") selected @endif>30 </option>
            <option value="36" @if ($nominaEmpresa->diasCesantias == "36") selected @endif>36 </option>
        </select>        
    </div>
    <div class="form-group">
        <label for="id_uni_nomina">ID Único Nómina</label>
        <input value = "{{ $nominaEmpresa->id_uni_nomina }}" type="text" class="form-control" id="id_uni_nomina" name = "id_uni_nomina">
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
    </div>
</form>