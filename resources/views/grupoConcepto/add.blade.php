<form method="post" action="/grupoConcepto/crear" class="formGen">
	<input type="hidden" name="numConceptos" id="numConceptos" value="2">
	<h2>Agregar grupo concepto</h2>
	@csrf
	<div class="form-group">
		<label for="nombre" class="control-label">Nombre grupo:</label>
		<input type="text" class="form-control" id="nombre" name="nombre" />
	</div>
	<hr>
	<div class="form-group">
		<label for="concepto1" class="control-label">Concepto 1:</label>
		<select class="form-control" id="concepto1" name="fkConcepto[]" required="">
			<option value="">Seleccione uno</option>
			@foreach($conceptos as $concepto)
				<option value="{{$concepto->idconcepto}}">{{$concepto->nombre}}</option>
			@endforeach
		</select>
	</div>
	<div class="form-group">
		<label for="concepto2" class="control-label">Concepto 2:</label>
		<select class="form-control" id="concepto2" name="fkConcepto[]">
			<option value="">Seleccione uno</option>
			@foreach($conceptos as $concepto)
				<option value="{{$concepto->idconcepto}}">{{$concepto->nombre}}</option>
			@endforeach
		</select>
	</div>
	<div class="conceptosCont"></div>
	<button class="btn btn-secondary" type="button" id="masConceptos">Agregar concepto</button>
	<button type="submit" class="btn btn-success">Crear Grupo</button>
</form>