<form method="post" action="/concepto/copiar" class="formGen">
	<h2>Copiar concepto</h2>
    @csrf
    <input type="hidden" name="idconcepto" value="{{$concepto->idconcepto}}" />
	<div class="form-group">
		<label for="nombre" class="control-label">Nombre *</label>
		<input type="text" class="form-control" id="nombre" required name="nombre" value="{{$concepto->nombre}}" />
	</div>
	<div class="form-group">
		<label for="fkNaturaleza" class="control-label">Naturaleza *</label>
		<select class="form-control" id="fkNaturaleza" required name="fkNaturaleza">
			<option value="">Seleccione uno</option>
			@foreach($naturalezas as $naturaleza)
				<option value="{{$naturaleza->idnaturalezaConcepto}}" @if($naturaleza->idnaturalezaConcepto == $concepto->fkNaturaleza)
                    selected
                @endif>{{$naturaleza->nombre}}</option>
			@endforeach
		</select>
	</div>
	<div class="form-group">
		<label for="fkTipoConcepto" class="control-label">Tipo *</label>
		<select class="form-control" id="fkTipoConcepto" required name="fkTipoConcepto">
			<option value="">Seleccione uno</option>
			@foreach($tiposConcepto as $tipoConcepto)
				<option value="{{$tipoConcepto->idtipo_concepto}}" @if($tipoConcepto->idtipo_concepto == $concepto->fkTipoConcepto)
                    selected
                @endif>{{$tipoConcepto->nombre}}</option>
			@endforeach
		</select>
	</div>
	<div class="form-group">
		<label for="msubTipo" class="control-label">Sub tipo *</label>
		<select class="form-control" id="msubTipo" data-id="{{$concepto->idconcepto}}" required name="subTipo">
			<option value="">Seleccione uno</option>
			<option value="Dias">Dias</option>
			<option value="Formula" @if($concepto->subTipo == "Formula") selected @endif>Formula</option>
			<option value="Porcentaje" @if($concepto->subTipo == "Porcentaje") selected @endif>Porcentaje</option>
			<option value="Tabla" @if($concepto->subTipo == "Tabla") selected @endif>Tabla</option>
			<option value="Valor" @if($concepto->subTipo == "Valor") selected @endif>Valor</option>
		</select>
    </div>
    <div>
        <a href="/concepto/getFormulaConcepto/{{$concepto->idconcepto}}" class="modificarFormula">Modificar Formula</a>
    </div>
	<div class="respFormulaConcepto">
    @if(sizeof($formulaConcepto)>0)
        @isset($formulaConcepto[0]->fkVariableInicial)
            <input type="hidden" name="fkVariableInicial" value="{{$formulaConcepto[0]->fkVariableInicial}}" />    
        @endisset
        @isset($formulaConcepto[0]->valorInicial)
            <input type="hidden" name="valorInicial" value="{{$formulaConcepto[0]->valorInicial}}" />
        @endisset
        @isset($formulaConcepto[0]->fkGrupoConceptoInicial)
            <input type="hidden" name="grupoInicial" value="{{$formulaConcepto[0]->fkGrupoConceptoInicial}}" />
        @endisset
        @isset($formulaConcepto[0]->fkConceptoInicial)
            <input type="hidden" name="conceptoInicial" value="{{$formulaConcepto[0]->fkConceptoInicial}}" />
        @endisset

        @for ($i = 0; $i < sizeof($formulaConcepto); $i++)

            <input type="hidden" name="fkTipoOperacion[]" value="{{$formulaConcepto[$i]->fkTipoOperacion}}" />
            <input type="hidden" name="fkVariableFinal[]" value="{{$formulaConcepto[$i]->fkVariableFinal}}" />
            <input type="hidden" name="valorFinal[]" value="{{$formulaConcepto[$i]->valorFinal}}" />
            <input type="hidden" name="grupoFinal[]" value="{{$formulaConcepto[$i]->fkGrupoConceptoFinal}}" />
            <input type="hidden" name="conceptoFinal[]" value="{{$formulaConcepto[$i]->fkConceptoFinal}}" />
        @endfor
    @endif

    </div>
	<div class="form-group elementoVariable">
		<label for="fkVariable" class="control-label">Variable *</label>
		<select class="form-control" id="fkVariable" name="fkVariable">
			<option value="">Seleccione uno</option>
			@foreach($variables as $variable)
				<option value="{{$variable->idVariable}}"  @if($variable->idVariable == $concepto->fkVariable) selected @endif>{{$variable->nombre}}</option>
			@endforeach
		</select>
	</div>
	<input type="hidden"  id="numRetefuente" name="numRetefuente" value="{{$concepto->numRetefuente}}" />
	<div class="form-group">
		<label>Grupo concepto</label>
		<div class="checksCont">
			@foreach ($gruposConcepto as $grupoConcepto)
			<input type="hidden" value="@isset($grupoConcepto->relacion) 1 @else 0 @endisset" name="gruposConceptoRelacion[]"/>
			<div class="row">
				<div class="col-1 text-center">
					<input type="checkbox" value="{{$grupoConcepto->idgrupoConcepto}}" 
					@isset($grupoConcepto->relacion)
						checked="checked"
					@endisset
					id="grupoConcepto_{{$grupoConcepto->idgrupoConcepto}}" name="gruposConcepto[]"/>
				</div>
				<div class="col-10">
					<label for="grupoConcepto_{{$grupoConcepto->idgrupoConcepto}}">{{$grupoConcepto->nombre}}</label>
				</div>				
			</div>
			@endforeach
		</div>
	</div>	
	<div class="form-group">
		<label for="generacionAutomatica" class="control-label">Generaci&oacute;n *</label>
		<select class="form-control" id="generacionAutomatica" required name="generacionAutomatica">
			<option value="0" @if($concepto->generacionAutomatica == "0") selected @endif >MANUAL</option>
			<option value="1" @if($concepto->generacionAutomatica == "1") selected @endif >AUTOMATICA</option>			
		</select>
	</div>
	<button type="submit" class="btn btn-success">Copiar concepto</button>
</form>