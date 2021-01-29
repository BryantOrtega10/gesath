<form>
	<h2>Detalle grupo concepto</h2>
	@csrf
	<div class="form-group">
		<label for="nombre" class="control-label">Nombre grupo:</label>
	    <input type="text" class="form-control" id="nombre" name="nombre" value = "{{ $grupoConcepto[0]->nombre }}" disabled />
	</div>
	<hr>
	<div class="form-group">
		{!! $DomConceptos !!}
		<!-- DOM DE DETALLE DE CONCEPTOS PAPI -->
	</div>
</form>