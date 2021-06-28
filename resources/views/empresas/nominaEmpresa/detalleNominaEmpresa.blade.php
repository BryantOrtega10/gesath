<form>
    <div class="form-group">
        <label for="nombre">Nombre</label>
        <input value = "{{ $nominaEmpresa->nombre }}" type="text" class="form-control" id="nombre" name = "nombre" disabled>
    </div>
    <div class="form-group">
        <label for="tipoPeriodo">Tipo periodo</label>
        <input value = "{{ $nominaEmpresa->tipoPeriodo }}" type="text" class="form-control" id="tipoPeriodo" name = "tipoPeriodo" disabled>
    </div>
    <div class="form-group">
        <label for="periodo">Periodo</label>
        <input value = "{{ $nominaEmpresa->periodo }}" type="text" class="form-control" id="periodo" name = "periodo" disabled>
    </div>
    <div class="form-group">
        <label for="diasCesantias">D&iacute;s cesantias</label>
        <input value = "{{ $nominaEmpresa->diasCesantias }}" type="text" class="form-control" id="diasCesantias" name = "diasCesantias" disabled>
    </div>
    <div class="form-group">
        <label for="id_uni_nomina">ID Único Nómina</label>
        <input value = "{{ $nominaEmpresa->id_uni_nomina }}" type="text" class="form-control" id="id_uni_nomina" name = "id_uni_nomina" disabled>
    </div>
</form>