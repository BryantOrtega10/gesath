<form method="post" action="" id="formFormulaConcepto">
	<h2>Ver formula a concepto</h2>
    @csrf
    
	<input type="hidden" id="numOperacion" name="numOperacion" value="{{sizeof($formulaConcepto)}}" />
	<div class="form-group">
		<label for="tipoInicio" class="control-label">Tipo inicio:</label>    
        @isset($formulaConcepto[0]->fkConceptoInicial) <input type="text" class="form-control" id="tipoInicio" required name="tipoInicio" value="Concepto" readonly /> @endisset
        @isset($formulaConcepto[0]->fkGrupoConceptoInicial) <input type="text" class="form-control" id="tipoInicio" required name="tipoInicio" value="Grupo de concepto" readonly /> @endisset
        @isset($formulaConcepto[0]->fkVariableInicial) <input type="text" class="form-control" id="tipoInicio" required name="tipoInicio" value="Variable" readonly /> @endisset
        @isset($formulaConcepto[0]->valorInicial) <input type="text" class="form-control" id="tipoInicio" required name="tipoInicio" value="Valor Fijo" readonly /> @endisset
    
	</div>
	<div class="form-group variableInicial @if(!isset($formulaConcepto[0]->fkVariableInicial)) oculto @endif">
		<label for="variableInicial" class="control-label">Variable Inicio:</label>
        @foreach($variables as $variable)
            @if ($variable->idVariable == $formulaConcepto[0]->fkVariableInicial)
                <input type="text" class="form-control" id="variableInicial" required name="variableInicial" value="{{$variable->nombre}}" readonly />
            @endif
        @endforeach
		
	</div>
	<div class="form-group valorInicial @if(!isset($formulaConcepto[0]->valorInicial)) oculto @endif">
		<label for="valorInicio" class="control-label">Valor Inicio:</label>
		<input type="text" readonly class="form-control" id="valorInicio" name="valorInicio" value="{{$formulaConcepto[0]->valorInicial}}" />
    </div>
    <div class="form-group conceptoInicial @if(!isset($formulaConcepto[0]->fkConceptoInicial)) oculto @endif">
		<label for="conceptoInicial" class="control-label">Concepto Inicio:</label>
        @foreach($conceptos as $concepto)
            @if ($concepto->idconcepto == $formulaConcepto[0]->fkConceptoInicial)
            <input type="text" class="form-control" id="conceptoInicial" readonly name="conceptoInicial" value="{{$concepto->nombre}}" readonly />
            @endif
        @endforeach
    
    </div>
    <div class="form-group grupoInicial @if(!isset($formulaConcepto[0]->fkGrupoConceptoInicial)) oculto @endif">
		<label for="grupoInicial" class="control-label">Grupo concepto Inicio:</label>
        @foreach($grupoConceptos as $grupoConcepto)
            @if ($grupoConcepto->idgrupoConcepto == $formulaConcepto[0]->fkGrupoConceptoInicial)
                <input type="text" class="form-control" id="grupoInicial" readonly name="grupoInicial" value="{{$grupoConcepto->nombre}}" readonly />
            @endif
        @endforeach
    </div>
	<div class="form-group">
		<label for="tipoOperacion1" class="control-label">Tipo operaci&oacute;n:</label>
        @foreach($tipoOperaciones as $tipoOperacion)
            @if ($tipoOperacion->idtipoOperacion == $formulaConcepto[0]->fkTipoOperacion)
            <input type="text" class="form-control" id="tipoOperacion1" readonly name="tipoOperacion1" value="{{$tipoOperacion->nombre}}" readonly />
            @endif
        @endforeach
		
	</div>
	<div class="form-group">
		<label for="tipoFin1" class="control-label">Tipo fin</label>
        @isset($formulaConcepto[0]->fkConceptoFinal) <input type="text" class="form-control" id="tipoFin" required name="tipoFin" value="Concepto" readonly /> @endisset
        @isset($formulaConcepto[0]->fkGrupoConceptoFinal) <input type="text" class="form-control" id="tipoFin" required name="tipoFin" value="Grupo de concepto" readonly /> @endisset
        @isset($formulaConcepto[0]->fkVariableFinal) <input type="text" class="form-control" id="tipoFin" required name="tipoFin" value="Variable" readonly /> @endisset
        @isset($formulaConcepto[0]->valorFinal) <input type="text" class="form-control" id="tipoFin" required name="tipoFin" value="Valor Fijo" readonly /> @endisset
	</div>
	<div class="form-group variableFin @if(!isset($formulaConcepto[0]->fkVariableFinal)) oculto @endif" data-id="1">
		<label for="variableFin1" class="control-label">Variable Final:</label>
        @foreach($variables as $variable)
            @if ($variable->idVariable == $formulaConcepto[0]->fkVariableFinal)
                <input type="text" class="form-control" id="variableFin1" readonly name="variableFin1" value="{{$variable->nombre}}" readonly />
            @endif
        @endforeach		
    </div>
    <div class="form-group conceptoFin @if(!isset($formulaConcepto[0]->fkConceptoFinal)) oculto @endif" data-id="1">
		<label for="conceptoFin1" class="control-label">Concepto Final:</label>
        @foreach($conceptos as $concepto)
            @if ($concepto->idconcepto == $formulaConcepto[0]->fkConceptoFinal)
                <input type="text" class="form-control" id="conceptoFin1" name="conceptoFin1" value="{{$concepto->nombre}}" readonly />
            @endif
        @endforeach
    
    </div>
    <div class="form-group grupoFin @if(!isset($formulaConcepto[0]->fkGrupoConceptoFinal)) oculto @endif" data-id="1">
		<label for="grupoFin1" class="control-label">Grupo concepto Final:</label>
        @foreach($grupoConceptos as $grupoConcepto)
            @if ($grupoConcepto->idgrupoConcepto == $formulaConcepto[0]->fkGrupoConceptoFinal)
                <input type="text" class="form-control" id="grupoFin1" name="grupoFin1" value="{{$grupoConcepto->nombre}}" readonly />
            @endif
        @endforeach
		
    </div>
	<div class="form-group valorFin @if(!isset($formulaConcepto[0]->valorFinal)) oculto @endif" data-id="1">
		<label for="valorFin1" class="control-label">Valor Final:</label>
		<input type="text" class="form-control cambiarValorFinal" readonly id="valorFin1" name="valorFin[]" value="{{$formulaConcepto[0]->valorFinal}}" />
	</div>
	<div class="respMasOperaciones">
        @for ($i = 1; $i < sizeof($formulaConcepto); $i++)
            <div class="operacionAdicional" data-id="{{($i + 1)}}">
                <div class="form-group">
                    <label for="tipoOperacion{{($i + 1)}}" class="control-label">Tipo operaci&oacute;n:</label>
                    @foreach($tipoOperaciones as $tipoOperacion)
                        @if ($tipoOperacion->idtipoOperacion == $formulaConcepto[$i]->fkTipoOperacion)
                        <input type="text" class="form-control" id="tipoOperacion{{($i + 1)}}" name="tipoOperacion{{($i + 1)}}" value="{{$tipoOperacion->nombre}}" readonly />
                        @endif
                    @endforeach
                </div>
                <div class="form-group">
                    <label for="tipoFin{{($i + 1)}}" class="control-label">Tipo fin</label>
                    @isset($formulaConcepto[$i]->fkConceptoFinal) <input type="text" class="form-control cambiarValorFinal" readonly id="valorFin1" name="valorFin[]" value="Concepto" /> @endisset
                    @isset($formulaConcepto[$i]->fkGrupoConceptoFinal) <input type="text" class="form-control cambiarValorFinal" readonly id="valorFin1" name="valorFin[]" value="Grupo de concepto" /> @endisset
                    @isset($formulaConcepto[$i]->fkVariableFinal) <input type="text" class="form-control cambiarValorFinal" readonly id="valorFin1" name="valorFin[]" value="Variable" /> @endisset
                    @isset($formulaConcepto[$i]->valorFinal) <input type="text" class="form-control cambiarValorFinal" readonly id="valorFin1" name="valorFin[]" value="Valor Fijo" /> @endisset     
                </div>
                <div class="form-group conceptoFin @if(!isset($formulaConcepto[$i]->fkConceptoFinal)) oculto @endif" data-id="{{($i + 1)}}">
                    <label for="conceptoFin{{($i + 1)}}" class="control-label">Concepto Final:</label>
                    @foreach($conceptos as $concepto)
                        @if($concepto->idconcepto == $formulaConcepto[$i]->fkConceptoFinal)
                            <input type="text" class="form-control" readonly value="{{$concepto->nombre}}" />
                        @endif>{{$concepto->nombre}}</option>
                    @endforeach
                </div>
                <div class="form-group grupoFin @if(!isset($formulaConcepto[$i]->fkGrupoConceptoFinal)) oculto @endif" data-id="{{($i + 1)}}">
                    <label for="grupoFin{{($i + 1)}}" class="control-label">Grupo concepto Final:</label>
                    @foreach($grupoConceptos as $grupoConcepto)
                        @if ($grupoConcepto->idgrupoConcepto == $formulaConcepto[$i]->fkGrupoConceptoFinal)
                            <input type="text" class="form-control" readonly value="{{$grupoConcepto->nombre}}" />
                        @endif
                    @endforeach
           
                </div>
                <div class="form-group variableFin @if(!isset($formulaConcepto[$i]->fkVariableFinal)) oculto @endif" data-id="{{($i + 1)}}">
                    <label for="variableFin{{($i + 1)}}" class="control-label">Variable Final:</label>
                    @foreach($variables as $variable)
                        @if($variable->idVariable == $formulaConcepto[$i]->fkVariableFinal)
                            <input type="text" class="form-control" readonly value="{{$variable->nombre}}" />
                        @endif
                    @endforeach
                    
                </div>
                <div class="form-group valorFin @if(!isset($formulaConcepto[$i]->valorFinal)) oculto @endif" data-id="{{($i + 1)}}">
                    <label for="valorFin{{($i + 1)}}" class="control-label">Valor Final:</label>
                    <input type="text" readonly class="form-control cambiarValorFinal" id="valorFin{{($i + 1)}}" name="valorFin[]" value="{{$formulaConcepto[$i]->valorFinal}}" />
                </div>
            </div>
        @endfor        
    </div>
</form>