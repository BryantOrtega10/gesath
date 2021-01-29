<form method="post" action="/cargos/agregarCargo" class="formGen">
	<h2>Agregar cargo</h2>
	@csrf
	<div class="form-group">
		<label for="nombreCargo" class="control-label">Nombre *</label>
		<input type="text" class="form-control" id="nombreCargo" required name="nombreCargo" />
	</div>
	<button type="submit" class="btn btn-success">Crear cargo</button>
</form>