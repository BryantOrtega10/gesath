<form method="post" action="/codigos/agregarCodigo" class="formGen">
	<h2>Agregar código diagnóstico</h2>
	@csrf
	<div class="form-group">
		<label for="idCodDiagnostico" class="control-label">Código *</label>
		<input type="text" class="form-control" id="idCodDiagnostico" required name="idCodDiagnostico" />
	</div>
	<div class="form-group">
		<label for="nombre" class="control-label">Nombre *</label>
		<input type="text" class="form-control" id="nombre" required name="nombre" />
	</div>
	<button type="submit" class="btn btn-primary">Crear código</button>
</form>