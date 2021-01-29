<form method="post" action="/concepto/getFormulaConcepto/llenar" id="formFormulaConcepto">
	<h2>Agregar formula a concepto</h2>
	@csrf
	<input type="hidden" id="numOperacion" name="numOperacion" value="1" />
	<div class="form-group">
		<label for="tipoInicio" class="control-label">Tipo inicio:</label>
		<select class="form-control" id="tipoInicio" name="tipoInicio">
            <option value="">Seleccione uno</option>
            <option value="concepto">Concepto</option>
            <option value="grupo">Grupo de concepto</option>
			<option value="variable">Variable</option>
			<option value="valor">Valor Fijo</option>
		</select>
	</div>
	<div class="form-group variableInicial oculto">
		<label for="variableInicial" class="control-label">Variable Inicio:</label>
		<select class="form-control" id="variableInicial" name="variableInicial">
			<option value="">Seleccione uno</option>
			@foreach($variables as $variable)
				<option value="{{$variable->idVariable}}">{{$variable->nombre}}</option>
			@endforeach
		</select>
	</div>
	<div class="form-group valorInicial oculto">
		<label for="valorInicio" class="control-label">Valor Inicio:</label>
		<input type="text" class="form-control" id="valorInicio" name="valorInicio" />
    </div>
    <div class="form-group conceptoInicial oculto">
		<label for="conceptoInicial" class="control-label">Concepto Inicio:</label>
		<select class="form-control" id="conceptoInicial" name="conceptoInicial">
			<option value="">Seleccione uno</option>
			@foreach($conceptos as $concepto)
				<option value="{{$concepto->idconcepto}}">{{$concepto->nombre}}</option>
			@endforeach
		</select>
    </div>
    <div class="form-group grupoInicial oculto">
		<label for="grupoInicial" class="control-label">Grupo concepto Inicio:</label>
		<select class="form-control" id="grupoInicial" name="grupoInicial">
			<option value="">Seleccione uno</option>
			@foreach($grupoConceptos as $grupoConcepto)
				<option value="{{$grupoConcepto->idgrupoConcepto}}">{{$grupoConcepto->nombre}}</option>
			@endforeach
		</select>
    </div>
    


	<div class="form-group">
		<label for="tipoOperacion1" class="control-label">Tipo operaci&oacute;n:</label>
		<select class="form-control" id="tipoOperacion1" name="tipoOperacion[]">
			<option value="">Seleccione uno</option>
			@foreach($tipoOperaciones as $tipoOperacion)
				<option value="{{$tipoOperacion->idtipoOperacion}}">{{$tipoOperacion->nombre}}</option>
			@endforeach
		</select>
	</div>
	<div class="form-group">
		<label for="tipoFin1" class="control-label">Tipo fin</label>
		<select class="form-control tipoFin" id="tipoFin1" name="tipoFin[]" data-id="1">
            <option value="">Seleccione uno</option>
            <option value="concepto">Concepto</option>
            <option value="grupo">Grupo de concepto</option>
			<option value="variable">Variable</option>
            <option value="valor">Valor Fijo</option>
            
		</select>
	</div>
	<div class="form-group variableFin oculto" data-id="1">
		<label for="variableFin1" class="control-label">Variable Final:</label>
		<select class="form-control cambiarValorFinal" id="variableFin1" name="variableFin[]">
			<option value="">Seleccione uno</option>
			@foreach($variables as $variable)
				<option value="{{$variable->idVariable}}">{{$variable->nombre}}</option>
			@endforeach
		</select>
    </div>
    <div class="form-group conceptoFin oculto" data-id="1">
		<label for="conceptoFin1" class="control-label">Concepto Final:</label>
		<select class="form-control" id="conceptoFin1" name="conceptoFin[]">
			<option value="">Seleccione uno</option>
			@foreach($conceptos as $concepto)
				<option value="{{$concepto->idconcepto}}">{{$concepto->nombre}}</option>
			@endforeach
		</select>
    </div>
    <div class="form-group grupoFin oculto" data-id="1">
		<label for="grupoFin1" class="control-label">Grupo concepto Final:</label>
		<select class="form-control" id="grupoFin1" name="grupoFin[]">
			<option value="">Seleccione uno</option>
			@foreach($grupoConceptos as $grupoConcepto)
				<option value="{{$grupoConcepto->idgrupoConcepto}}">{{$grupoConcepto->nombre}}</option>
			@endforeach
		</select>
    </div>


	<div class="form-group valorFin oculto" data-id="1">
		<label for="valorFin1" class="control-label">Valor Final:</label>
		<input type="text" class="form-control cambiarValorFinal" id="valorFin1" name="valorFin[]" />
	</div>
	<div class="respMasOperaciones"></div>
	<button class="btn btn-secondary" type="button" id="masOperaciones">Mas operaciones</button>
	<button class="btn btn-primary" type="submit">Asignar formula</button>	
</form>