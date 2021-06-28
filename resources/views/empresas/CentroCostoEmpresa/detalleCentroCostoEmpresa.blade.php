<form class="formDetail" method = "POST">
    <div class="form-group">
        <label for="nombre">Nombre</label>
        <input type="text" class="form-control" id="nombre" name = "nombre" value = "{{ $centroCosto->nombre}}" disabled>
    </div>
    <div class="form-group">
        <label for="id_uni_centro">ID Único Centro de Costo</label>
        <input type="text" class="form-control" id="id_uni_centro" name = "id_uni_centro" value = "{{ $centroCosto->id_uni_centro}}" disabled>
    </div>
    <div class="form-group">
        <label for="dias_cesantias">Dias cesantias</label>
        <input type="text" class="form-control" id="dias_cesantias" name = "dias_cesantias" value = "{{ $centroCosto->diasCesantias ?? "Sin configurar" }}" disabled>
    </div>
</form>