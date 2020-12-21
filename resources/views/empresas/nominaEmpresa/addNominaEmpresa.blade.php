<form class="formGen" action = "/empresa/nomina/create" method = "POST">
    <input type="hidden" name="fkEmpresa" value = "{{ $idNomina }}">
    <div class="form-group">
        <label for="nombre">Nombre</label>
        <input type="text" class="form-control" id="nombre" name = "nombre">
    </div>
    <div class="form-group">
        <label for="tipoPeriodo">Tipo periodo</label>
        <input type="text" class="form-control" id="tipoPeriodo" name = "tipoPeriodo">
    </div>
    <div class="form-group">
        <label for="periodo">Periodo</label>
        <input type="text" class="form-control" id="periodo" name = "periodo">
    </div>
    <div class="form-group">
        <label for="id_uni_nomina">ID Único Nómina</label>
        <input type="text" class="form-control" id="id_uni_nomina" name = "id_uni_nomina">
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-primary">Agregar</button>
    </div>
</form>