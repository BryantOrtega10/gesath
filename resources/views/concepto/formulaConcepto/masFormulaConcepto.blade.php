<div class="operacionAdicional" data-id="{{$idRegistro}}">
	<a href="#" class="btn btn-outline-danger quitarOperacion" data-id="{{$idRegistro}}">Quitar operacion</a>
	<div class="form-group">
		<label for="tipoOperacion{{$idRegistro}}" class="control-label">Tipo operaci&oacute;n:</label>
		<select class="form-control" id="tipoOperacion{{$idRegistro}}" name="tipoOperacion[]">
			<option value="">Seleccione uno</option>
			@foreach($tipoOperaciones as $tipoOperacion)
				<option value="{{$tipoOperacion->idtipoOperacion}}">{{$tipoOperacion->nombre}}</option>
			@endforeach
		</select>
	</div>
	<div class="form-group">
		<label for="tipoFin{{$idRegistro}}" class="control-label">Tipo fin</label>
		<select class="form-control tipoFin" id="tipoFin{{$idRegistro}}" name="tipoFin[]" data-id="{{$idRegistro}}">
			<option value="">Seleccione uno</option>
			<option value="variable">Variable</option>
            <option value="valor">Valor Fijo</option>
            <option value="concepto">Concepto</option>
            <option value="grupo">Grupo de concepto</option>
		</select>
    </div>
    <div class="form-group conceptoFin oculto" data-id="{{$idRegistro}}">
		<label for="conceptoFin{{$idRegistro}}" class="control-label">Concepto Final:</label>
		<select class="form-control" id="conceptoFin{{$idRegistro}}" name="conceptoFin[]">
			<option value="">Seleccione uno</option>
			@foreach($conceptos as $concepto)
				<option value="{{$concepto->idconcepto}}">{{$concepto->nombre}}</option>
			@endforeach
		</select>
    </div>
    <div class="form-group grupoFin oculto" data-id="{{$idRegistro}}">
		<label for="grupoFin{{$idRegistro}}" class="control-label">Grupo concepto Final:</label>
		<select class="form-control" id="grupoFin{{$idRegistro}}" name="grupoFin[]">
			<option value="">Seleccione uno</option>
			@foreach($grupoConceptos as $grupoConcepto)
				<option value="{{$grupoConcepto->idgrupoConcepto}}">{{$grupoConcepto->nombre}}</option>
			@endforeach
		</select>
    </div>
	<div class="form-group variableFin oculto" data-id="{{$idRegistro}}">
		<label for="variableFin{{$idRegistro}}" class="control-label">Variable Final:</label>
		<select class="form-control cambiarValorFinal" id="variableFin{{$idRegistro}}" name="variableFin[]">
			<option value="">Seleccione uno</option>
			@foreach($variables as $variable)
				<option value="{{$variable->idVariable}}">{{$variable->nombre}}</option>
			@endforeach
		</select>
	</div>
	<div class="form-group valorFin oculto" data-id="{{$idRegistro}}">
		<label for="valorFin{{$idRegistro}}" class="control-label">Valor Final:</label>
		<input type="text" class="form-control cambiarValorFinal" id="valorFin{{$idRegistro}}" name="valorFin[]" />
	</div>
</div>