<form class="formGen" action = "/empresa/centroCosto/create" method = "POST">
    <input type="hidden" name="fkEmpresa" value = "{{ $idEmpresa }}">
    <div class="form-group">
        <label for="nombre">Nombre</label>
        <input type="text" class="form-control" id="nombre" name = "nombre">
    </div>
    <div class="form-group">
        <label for="id_uni_centro">ID Único Centro de Costo</label>
        <input type="text" class="form-control" id="id_uni_centro" name = "id_uni_centro">
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-primary">Agregar</button>
    </div>
</form>