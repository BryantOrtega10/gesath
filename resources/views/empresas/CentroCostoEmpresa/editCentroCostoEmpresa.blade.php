<form class="formEdit" action = "/empresa/centroCosto/update" method = "POST">
    <input type="hidden" name="fkEmpresa" id = "fkEmpresa" value = "{{ $centroCosto->fkEmpresa }}">
    <div class="form-group">
        <label for="nombre">Nombre</label>
        <input type="text" class="form-control" id="nombre" name = "nombre" value = "{{ $centroCosto->nombre }}">
    </div>
    <div class="form-group">
        <label for="id_uni_centro">ID Único Centro de Costo</label>
        <input type="text" class="form-control" id="id_uni_centro" name = "id_uni_centro" value = "{{ $centroCosto->id_uni_centro }}">
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
    </div>
</form>