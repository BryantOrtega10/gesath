<form method="post" class="formEdit" action = "/codigos/editarCodigo">
	<h2>Editar código diagnóstico</h2>
	@csrf
	<div class="form-group">
		<label for="idCodDiagnostico" class="control-label">Código *</label>
		<input value = "{{ $codigos->idCodDiagnostico }}" type="text" class="form-control" id="idCodDiagnostico" required name="idCodDiagnostico" />
	</div>
	<div class="form-group">
		<label for="nombre" class="control-label">Nombre *</label>
		<input value = "{{ $codigos->nombre }}" type="text" class="form-control" id="nombre" required name="nombre" />
	</div>
	<button type="submit" class="btn btn-primary">Guardar cambios</button>
</form>