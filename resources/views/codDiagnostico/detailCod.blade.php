<form method="post">
	<h2>Detalle código diagnóstico</h2>
	@csrf
	<div class="form-group">
		<label for="idCodDiagnostico" class="control-label">Código *</label>
		<input value = "{{ $codigos->idCodDiagnostico }}" type="text" class="form-control" id="idCodDiagnostico" disabled name="idCodDiagnostico" />
	</div>
	<div class="form-group">
		<label for="nombre" class="control-label">Nombre *</label>
		<input value = "{{ $codigos->nombre }}" type="text" class="form-control" id="nombre" disabled name="nombre" />
	</div>
</form>