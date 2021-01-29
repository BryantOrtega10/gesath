<form method="post" action="/cargos/editarCargo/{{ $cargos->idCargo }}" class="formGen">
	<h2>Editar cargo</h2>
	@csrf
	<div class="form-group">
		<label for="nombreCargo" class="control-label">Nombre *</label>
	<input type="text" class="form-control" id="nombreCargo" required name="nombreCargo" value = "{{ $cargos->nombreCargo }}" />
	</div>
	<button type="submit" class="btn btn-success">Guardar cambios</button>
</form>