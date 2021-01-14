<form method="post" action="/empresa/centroTrabajo/agregarCentroTrabajo/{{ $idEmpresa }}" class="formGen">
	<h2>Agregar centro trabajo</h2>
	@csrf
	<div class="form-group">
		<label for="codigo" class="control-label">Código *</label>
		<input type="number" class="form-control" id="codigo" required name="codigo" />
	</div>
	<div class="form-group">
		<label for="nombre" class="control-label">Nombre *</label>
		<input type="text" class="form-control" id="nombre" required name="nombre" />
	</div>
	<div class="form-group">
		<label for="fkNivelArl" class="control-label">Nivel ARL *</label>
		<select class="form-control" id="fkNivelArl" required name="fkNivelArl">
            <option value = ''>-- Seleccione una opción --</option>
            @foreach($nivelArl as $n)
            <option value = '{{ $n->idnivel_arl }}'>{{ $n->nombre }}</option>
            @endforeach
        </select>
	</div>
	<button type="submit" class="btn btn-success">Crear centro trabajo</button>
</form>