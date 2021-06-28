<form method="post" action="" class="formGen">
	<h2>Ver concepto</h2>
    @csrf
    <input type="hidden" name="idconcepto" value="{{$concepto->idconcepto}}" />
	<div class="form-group">
		<label for="nombre" class="control-label">Nombre *</label>
		<input type="text" class="form-control" id="nombre" required name="nombre" value="{{$concepto->nombre}}" readonly />
	</div>
	<div class="form-group">
		<label for="fkNaturaleza" class="control-label">Naturaleza *</label>
		@foreach($naturalezas as $naturaleza)
			@if($naturaleza->idnaturalezaConcepto == $concepto->fkNaturaleza)
				<input type="text" class="form-control" id="fkNaturaleza" required name="fkNaturaleza" value="{{$naturaleza->nombre}}" readonly />
			@endif
		@endforeach
	</div>
	<div class="form-group">
		<label for="fkTipoConcepto" class="control-label">Tipo *</label>
		@foreach($tiposConcepto as $tipoConcepto)
			@if($tipoConcepto->idtipo_concepto == $concepto->fkTipoConcepto)
				<input type="text" class="form-control" id="fkTipoConcepto" required name="fkTipoConcepto" value="{{$tipoConcepto->nombre}}" readonly />
			@endif
		@endforeach
	</div>
	<div class="form-group">
		<label for="msubTipo" class="control-label">Sub tipo *</label>
		<input type="text" class="form-control" id="msubTipo" required name="msubTipo" value="{{$concepto->subTipo}}" readonly />
    </div>
    <div>
        <a href="/concepto/getFormulaConceptoVer/{{$concepto->idconcepto}}" class="modificarFormula">Ver Formula</a>
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
		@foreach($variables as $variable)
			@if($variable->idVariable == $concepto->fkVariable)
				<input type="text" class="form-control" id="fkVariable" required name="fkVariable" value="{{$variable->nombre}}" readonly />
			@endif
		@endforeach
	</div>

	<div class="form-group">
		<label>Grupo concepto</label>
		<div class="checksCont">
			@foreach ($gruposConcepto as $grupoConcepto)
			<input type="hidden" @isset($grupoConcepto->relacion) value="1" @else value="0" @endisset name="gruposConceptoRelacion[]"/>
			<input type="hidden" value="{{$grupoConcepto->idgrupoConcepto}}" name="gruposConceptoIds[]"/>
			<div class="row">
				<div class="col-1 text-center">
					<input type="checkbox" value="{{$grupoConcepto->idgrupoConcepto}}" 
					@isset($grupoConcepto->relacion)
						checked="checked"
					@endisset
					disabled
					id="grupoConcepto_{{$grupoConcepto->idgrupoConcepto}}" name="gruposConcepto[]"/>
				</div>
				<div class="col-10">
					<label for="grupoConcepto_{{$grupoConcepto->idgrupoConcepto}}">{{$grupoConcepto->nombre}}</label>
				</div>				
			</div>
			@endforeach
		</div>
	</div>
	<input type="hidden" class="form-control" id="numRetefuente" name="numRetefuente" value="{{$concepto->numRetefuente}}" readonly />
</form>