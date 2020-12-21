<div class="conceptoMasCont" data-id="{{$idRegistro}}">
<a href="#" class="btn btn-outline-danger quitarConcepto" data-id="{{$idRegistro}}">Quitar concepto</a>
<div class="form-group">
	<label for="concepto{{$idRegistro}}" class="control-label">Concepto {{$idRegistro}}:</label>
	<select class="form-control" id="concepto{{$idRegistro}}" name="concepto[]" required="">
		<option value="">Seleccione uno</option>
		@foreach($conceptos as $conceptos)
			<option value="{{$conceptos->idconcepto}}">{{$conceptos->nombre}}</option>
		@endforeach
	</select>
</div>
</div>