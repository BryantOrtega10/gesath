<form method="post" action="/concepto/getFormulaConcepto/llenar" id="formFormulaConcepto">
	<h2>Modificar formula a concepto</h2>
    @csrf
    
	<input type="hidden" id="numOperacion" name="numOperacion" value="{{sizeof($formulaConcepto)}}" />
	<div class="form-group">
		<label for="tipoInicio" class="control-label">Tipo inicio:</label>
		<select class="form-control" id="tipoInicio" name="tipoInicio">
            <option value="">Seleccione uno</option>
            <option value="concepto" @isset($formulaConcepto[0]->fkConceptoInicial) selected @endisset>Concepto</option>
            <option value="grupo" @isset($formulaConcepto[0]->fkGrupoConceptoInicial) selected @endisset>Grupo de concepto</option>
			<option value="variable" @isset($formulaConcepto[0]->fkVariableInicial) selected @endisset>Variable</option>
			<option value="valor" @isset($formulaConcepto[0]->valorInicial) selected @endisset>Valor Fijo</option>
		</select>
	</div>
	<div class="form-group variableInicial @if(!isset($formulaConcepto[0]->fkVariableInicial)) oculto @endif">
		<label for="variableInicial" class="control-label">Variable Inicio:</label>
		<select class="form-control" id="variableInicial" name="variableInicial">
			<option value="">Seleccione uno</option>
			@foreach($variables as $variable)
				<option value="{{$variable->idVariable}}" @if ($variable->idVariable == $formulaConcepto[0]->fkVariableInicial)
                    selected
                @endif>{{$variable->nombre}}</option>
			@endforeach
		</select>
	</div>
	<div class="form-group valorInicial @if(!isset($formulaConcepto[0]->valorInicial)) oculto @endif">
		<label for="valorInicio" class="control-label">Valor Inicio:</label>
		<input type="text" class="form-control" id="valorInicio" name="valorInicio" value="{{$formulaConcepto[0]->valorInicial}}" />
    </div>
    <div class="form-group conceptoInicial @if(!isset($formulaConcepto[0]->fkConceptoInicial)) oculto @endif">
		<label for="conceptoInicial" class="control-label">Concepto Inicio:</label>
		<select class="form-control" id="conceptoInicial" name="conceptoInicial">
			<option value="">Seleccione uno</option>
			@foreach($conceptos as $concepto)
                <option value="{{$concepto->idconcepto}}" @if ($concepto->idconcepto == $formulaConcepto[0]->fkConceptoInicial)
                    selected
                @endif>{{$concepto->nombre}}</option>
			@endforeach
		</select>
    </div>
    <div class="form-group grupoInicial @if(!isset($formulaConcepto[0]->fkGrupoConceptoInicial)) oculto @endif">
		<label for="grupoInicial" class="control-label">Grupo concepto Inicio:</label>
		<select class="form-control" id="grupoInicial" name="grupoInicial">
			<option value="">Seleccione uno</option>
			@foreach($grupoConceptos as $grupoConcepto)
				<option value="{{$grupoConcepto->idgrupoConcepto}}" @if ($grupoConcepto->idgrupoConcepto == $formulaConcepto[0]->fkGrupoConceptoInicial)
                    selected
                @endif>{{$grupoConcepto->nombre}}</option>
			@endforeach
		</select>
    </div>
	<div class="form-group">
		<label for="tipoOperacion1" class="control-label">Tipo operaci&oacute;n:</label>
		<select class="form-control" id="tipoOperacion1" name="tipoOperacion[]">
			<option value="">Seleccione uno</option>
			@foreach($tipoOperaciones as $tipoOperacion)
				<option value="{{$tipoOperacion->idtipoOperacion}}" @if ($tipoOperacion->idtipoOperacion == $formulaConcepto[0]->fkTipoOperacion)
                    selected
                @endif>{{$tipoOperacion->nombre}}</option>
			@endforeach
		</select>
	</div>
	<div class="form-group">
		<label for="tipoFin1" class="control-label">Tipo fin</label>
		<select class="form-control tipoFin" id="tipoFin1" name="tipoFin[]" data-id="1">
            <option value="">Seleccione uno</option>
            <option value="concepto" @isset($formulaConcepto[0]->fkConceptoFinal) selected @endisset>Concepto</option>
            <option value="grupo" @isset($formulaConcepto[0]->fkGrupoConceptoFinal) selected @endisset>Grupo de concepto</option>
			<option value="variable" @isset($formulaConcepto[0]->fkVariableFinal) selected @endisset>Variable</option>
			<option value="valor" @isset($formulaConcepto[0]->valorFinal) selected @endisset>Valor Fijo</option>
            
		</select>
	</div>
	<div class="form-group variableFin @if(!isset($formulaConcepto[0]->fkVariableFinal)) oculto @endif" data-id="1">
		<label for="variableFin1" class="control-label">Variable Final:</label>
		<select class="form-control cambiarValorFinal" id="variableFin1" name="variableFin[]">
			<option value="">Seleccione uno</option>
			@foreach($variables as $variable)
				<option value="{{$variable->idVariable}}" @if ($variable->idVariable == $formulaConcepto[0]->fkVariableFinal)
                    selected
                @endif>{{$variable->nombre}}</option>
			@endforeach
		</select>
    </div>
    <div class="form-group conceptoFin @if(!isset($formulaConcepto[0]->fkConceptoFinal)) oculto @endif" data-id="1">
		<label for="conceptoFin1" class="control-label">Concepto Final:</label>
		<select class="form-control" id="conceptoFin1" name="conceptoFin[]">
			<option value="">Seleccione uno</option>
			@foreach($conceptos as $concepto)
				<option value="{{$concepto->idconcepto}}"  @if ($concepto->idconcepto == $formulaConcepto[0]->fkConceptoFinal)
                    selected
                @endif>{{$concepto->nombre}}</option>
			@endforeach
		</select>
    </div>
    <div class="form-group grupoFin @if(!isset($formulaConcepto[0]->fkGrupoConceptoFinal)) oculto @endif" data-id="1">
		<label for="grupoFin1" class="control-label">Grupo concepto Final:</label>
		<select class="form-control" id="grupoFin1" name="grupoFin[]">
			<option value="">Seleccione uno</option>
			@foreach($grupoConceptos as $grupoConcepto)
				<option value="{{$grupoConcepto->idgrupoConcepto}}" @if ($grupoConcepto->idgrupoConcepto == $formulaConcepto[0]->fkGrupoConceptoFinal)
                    selected
                @endif>{{$grupoConcepto->nombre}}</option>
			@endforeach
		</select>
    </div>
	<div class="form-group valorFin @if(!isset($formulaConcepto[0]->valorFinal)) oculto @endif" data-id="1">
		<label for="valorFin1" class="control-label">Valor Final:</label>
		<input type="text" class="form-control cambiarValorFinal" id="valorFin1" name="valorFin[]" value="{{$formulaConcepto[0]->valorFinal}}" />
	</div>
	<div class="respMasOperaciones">
        @for ($i = 1; $i < sizeof($formulaConcepto); $i++)
            <div class="operacionAdicional" data-id="{{($i + 1)}}">
                <a href="#" class="btn btn-outline-danger quitarOperacion" data-id="{{($i + 1)}}">Quitar operacion</a>
                <div class="form-group">
                    <label for="tipoOperacion{{($i + 1)}}" class="control-label">Tipo operaci&oacute;n:</label>
                    <select class="form-control" id="tipoOperacion{{($i + 1)}}" name="tipoOperacion[]">
                        <option value="">Seleccione uno</option>
                        @foreach($tipoOperaciones as $tipoOperacion)
                            <option value="{{$tipoOperacion->idtipoOperacion}}"  @if ($tipoOperacion->idtipoOperacion == $formulaConcepto[$i]->fkTipoOperacion)
                                selected
                            @endif>{{$tipoOperacion->nombre}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="tipoFin{{($i + 1)}}" class="control-label">Tipo fin</label>
                    <select class="form-control tipoFin" id="tipoFin{{($i + 1)}}" name="tipoFin[]" data-id="{{($i + 1)}}">
                        <option value="">Seleccione uno</option>
                        <option value="concepto" @isset($formulaConcepto[$i]->fkConceptoFinal) selected @endisset>Concepto</option>
                        <option value="grupo" @isset($formulaConcepto[$i]->fkGrupoConceptoFinal) selected @endisset>Grupo de concepto</option>
                        <option value="variable" @isset($formulaConcepto[$i]->fkVariableFinal) selected @endisset>Variable</option>
                        <option value="valor" @isset($formulaConcepto[$i]->valorFinal) selected @endisset>Valor Fijo</option>
                    </select>
                </div>
                <div class="form-group conceptoFin @if(!isset($formulaConcepto[$i]->fkConceptoFinal)) oculto @endif" data-id="{{($i + 1)}}">
                    <label for="conceptoFin{{($i + 1)}}" class="control-label">Concepto Final:</label>
                    <select class="form-control" id="conceptoFin{{($i + 1)}}" name="conceptoFin[]">
                        <option value="">Seleccione uno</option>
                        @foreach($conceptos as $concepto)
                            <option value="{{$concepto->idconcepto}}" @if($concepto->idconcepto == $formulaConcepto[$i]->fkConceptoFinal)
                                selected
                            @endif>{{$concepto->nombre}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group grupoFin @if(!isset($formulaConcepto[$i]->fkGrupoConceptoFinal)) oculto @endif" data-id="{{($i + 1)}}">
                    <label for="grupoFin{{($i + 1)}}" class="control-label">Grupo concepto Final:</label>
                    <select class="form-control" id="grupoFin{{($i + 1)}}" name="grupoFin[]">
                        <option value="">Seleccione uno</option>
                        @foreach($grupoConceptos as $grupoConcepto)
                            <option value="{{$grupoConcepto->idgrupoConcepto}}" @if ($grupoConcepto->idgrupoConcepto == $formulaConcepto[$i]->fkGrupoConceptoFinal)
                                selected
                            @endif>{{$grupoConcepto->nombre}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group variableFin @if(!isset($formulaConcepto[$i]->fkVariableFinal)) oculto @endif" data-id="{{($i + 1)}}">
                    <label for="variableFin{{($i + 1)}}" class="control-label">Variable Final:</label>
                    <select class="form-control cambiarValorFinal" id="variableFin{{($i + 1)}}" name="variableFin[]">
                        <option value="">Seleccione uno</option>
                        @foreach($variables as $variable)
                            <option value="{{$variable->idVariable}}" @if($variable->idVariable == $formulaConcepto[$i]->fkVariableFinal)
                                selected
                            @endif>{{$variable->nombre}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group valorFin @if(!isset($formulaConcepto[$i]->valorFinal)) oculto @endif" data-id="{{($i + 1)}}">
                    <label for="valorFin{{($i + 1)}}" class="control-label">Valor Final:</label>
                    <input type="text" class="form-control cambiarValorFinal" id="valorFin{{($i + 1)}}" name="valorFin[]" value="{{$formulaConcepto[$i]->valorFinal}}" />
                </div>
            </div>
        @endfor        
    </div>
	<button class="btn btn-secondary" type="button" id="masOperaciones">Mas operaciones</button>
	<button class="btn btn-primary" type="submit">Asignar formula</button>	
</form>