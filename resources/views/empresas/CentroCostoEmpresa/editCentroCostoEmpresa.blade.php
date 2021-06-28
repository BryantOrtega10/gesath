<form class="formEdit" action = "/empresa/centroCosto/update" method = "POST">
    <input type="hidden" name="fkEmpresa" id = "fkEmpresa" value = "{{ $centroCosto->fkEmpresa }}">
    <input type="hidden" name="idCentro" id = "idCentro" value = "{{ $centroCosto->idcentroCosto }}">
    <div class="form-group">
        <label for="nombre">Nombre</label>
        <input type="text" class="form-control" id="nombre" name = "nombre" value = "{{ $centroCosto->nombre }}">
    </div>
    <div class="form-group">
        <label for="id_uni_centro">ID Único Centro de Costo</label>
        <input type="text" class="form-control" id="id_uni_centro" name = "id_uni_centro" value = "{{ $centroCosto->id_uni_centro }}">
    </div>
    <div class="form-group">
        <label for="dias_cesantias">Dias cesantias</label>
        <select class="form-control" id="dias_cesantias" name = "dias_cesantias">
            <option value="">Sin configurar</option>
            <option value="30" @if ($centroCosto->diasCesantias == "30") selected @endif>30 </option>
            <option value="36" @if ($centroCosto->diasCesantias == "36") selected @endif>36 </option>
        </select>        
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
    </div>
</form>