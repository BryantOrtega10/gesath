<form method="post" action="/grupoConcepto/update/{{ $grupoConcepto[0]->idgrupoConcepto }}" class="formEdit">
	<input type="hidden" name="numConceptos" id="numConceptos" value="2">
	<h2>Editar grupo concepto</h2>
	@csrf
	<div class="form-group">
		<label for="nombre" class="control-label">Nombre grupo:</label>
	<input type="text" class="form-control" id="nombre" name="nombre" value = "{{ $grupoConcepto[0]->nombre }}" />
	</div>
	<hr>
	<div class="form-group">
		<div class = "conceptos">
			{!! $DomConceptos !!}
			<!-- DOM DE CONCEPTOS PAPI -->
		</div>
	</div>
	<div class="conceptosCont"></div>
	<button type = "button" class="adicional_con btn btn-success" dataId = "{{ $cantCon }}">Agregar concepto</button>
	<button type="submit" class="btn btn-success">Guardar Cambios</button>
</form>