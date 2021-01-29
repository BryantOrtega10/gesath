<form method="post" action="/concepto/condiciones/agregarCondicion" class="formGen" id="getCondicionesConcepto">
	<input type="hidden" name="idConcepto" value="{{$idConcepto}}" />
    <h2>Agregar condiciones a concepto</h2>
    <div class="form-group">
		<label for="descripcionCondicion" class="control-label">Descripcion:</label>
		<textarea class="form-control" id="descripcionCondicion" name="descripcionCondicion"></textarea>
	</div>
    <div class="form-group">
		<label for="tipoCondicion" class="control-label">Tipo condicion:</label>
		<select class="form-control" id="tipoCondicion" name="tipoCondicion">
			<option value="">Seleccione uno</option>
            @foreach($tipoCondiciones as $tipoCondicion)
				<option value="{{$tipoCondicion->idtipoCondicion}}">{{$tipoCondicion->nombre}}</option>
			@endforeach           
		</select>
    </div>
    <div class="form-group">
		<label for="tipoResultado" class="control-label">Tipo resultado:</label>
		<select class="form-control" id="tipoResultado" name="tipoResultado">
			<option value="">Seleccione uno</option>
            @foreach($tipoResultados as $tipoResultado)
				<option value="{{$tipoResultado->idtipoResultado}}">{{$tipoResultado->nombre}}</option>
			@endforeach           
		</select>
    </div>
    <div class="form-group">
		<label for="mensajeCondicion" class="control-label">Mensaje a mostrar:</label>
		<textarea class="form-control" id="mensajeCondicion" name="mensajeCondicion"></textarea>
    </div>
    <hr>
    <div class="form-group">
		<label for="tipoInicio" class="control-label">Tipo inicio:</label>
		<select class="form-control" id="tipoInicio" name="tipoInicio">
            <option value="">Seleccione uno</option>
            <option value="concepto">Concepto</option>
            <option value="grupo">Grupo de concepto</option>
		</select>
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
	<div class="form-group multiplicadorInicial oculto">
		<label for="multiplicadorInicial" class="control-label">Multiplicado por:</label>
		<input type="text" class="form-control" id="multiplicadorInicial" name="multiplicadorInicial" />
    </div>


    <div class="form-group">
		<label for="operador1" class="control-label">Operador comparacion:</label>
		<select class="form-control operador" id="operador1" name="operador[]" data-id="1">
			<option value="">Seleccione uno</option>
			@foreach($operadores as $operador)
				<option value="{{$operador->idoperadorComparacion}}">{{$operador->nombre}}</option>
			@endforeach
		</select>
    </div>
    <div class="form-group tipoFin1 oculto" data-id="1">
		<label for="tipoFin11" class="control-label">Tipo fin</label>
		<select class="form-control selectTipoFin1" id="tipoFin11" name="tipoFin1[]" data-id="1">
            <option value="">Seleccione uno</option>
            <option value="concepto">Concepto</option>
            <option value="grupo">Grupo de concepto</option>
			<option value="variable">Variable</option>
            <option value="valor">Valor Fijo</option>
		</select>
    </div>
    <div class="form-group variableFin1 oculto" data-id="1">
		<label for="variableFin11" class="control-label">Variable Final:</label>
		<select class="form-control cambiarValorFinal" id="variableFin11" name="variableFin1[]">
			<option value="">Seleccione uno</option>
			@foreach($variables as $variable)
				<option value="{{$variable->idVariable}}">{{$variable->nombre}}</option>
			@endforeach
		</select>
    </div>
    <div class="form-group conceptoFin1 oculto" data-id="1">
		<label for="conceptoFin11" class="control-label">Concepto Final:</label>
		<select class="form-control" id="conceptoFin11" name="conceptoFin1[]">
			<option value="">Seleccione uno</option>
			@foreach($conceptos as $concepto)
				<option value="{{$concepto->idconcepto}}">{{$concepto->nombre}}</option>
			@endforeach
		</select>
    </div>
    <div class="form-group grupoFin1 oculto" data-id="1">
		<label for="grupoFin11" class="control-label">Grupo concepto Final:</label>
		<select class="form-control" id="grupoFin11" name="grupoFin1[]">
			<option value="">Seleccione uno</option>
			@foreach($grupoConceptos as $grupoConcepto)
				<option value="{{$grupoConcepto->idgrupoConcepto}}">{{$grupoConcepto->nombre}}</option>
			@endforeach
		</select>
    </div>
	<div class="form-group valorFin1 oculto" data-id="1">
		<label for="valorFin11" class="control-label">Valor Final:</label>
		<input type="text" class="form-control cambiarValorFinal" id="valorFin11" name="valorFin1[]" />
    </div>
    <div class="form-group multiplicadorFin1 oculto" data-id="1">
		<label for="multiplicadorFin11" class="control-label">Multiplicado por:</label>
		<input type="text" class="form-control" id="multiplicadorFin11" name="multiplicadorFin1[]" />
	</div>
	




    <div class="form-group tipoFin2 oculto" data-id="1">
		<label for="tipoFin21" class="control-label">Tipo fin</label>
		<select class="form-control selectTipoFin2" id="tipoFin21" name="tipoFin2[]" data-id="1">
            <option value="">Seleccione uno</option>
            <option value="concepto">Concepto</option>
            <option value="grupo">Grupo de concepto</option>
			<option value="variable">Variable</option>
            <option value="valor">Valor Fijo</option>
		</select>
    </div>

    <div class="form-group variableFin2 oculto" data-id="1">
		<label for="variableFin21" class="control-label">Variable Final:</label>
		<select class="form-control cambiarValorFinal" id="variableFin21" name="variableFin2[]">
			<option value="">Seleccione uno</option>
			@foreach($variables as $variable)
				<option value="{{$variable->idVariable}}">{{$variable->nombre}}</option>
			@endforeach
		</select>
    </div>
    <div class="form-group conceptoFin2 oculto" data-id="1">
		<label for="conceptoFin21" class="control-label">Concepto Final:</label>
		<select class="form-control" id="conceptoFin21" name="conceptoFin2[]">
			<option value="">Seleccione uno</option>
			@foreach($conceptos as $concepto)
				<option value="{{$concepto->idconcepto}}">{{$concepto->nombre}}</option>
			@endforeach
		</select>
    </div>
    <div class="form-group grupoFin2 oculto" data-id="1">
		<label for="grupoFin21" class="control-label">Grupo concepto Final:</label>
		<select class="form-control" id="grupoFin21" name="grupoFin2[]">
			<option value="">Seleccione uno</option>
			@foreach($grupoConceptos as $grupoConcepto)
				<option value="{{$grupoConcepto->idgrupoConcepto}}">{{$grupoConcepto->nombre}}</option>
			@endforeach
		</select>
    </div>
	<div class="form-group valorFin2 oculto" data-id="1">
		<label for="valorFin21" class="control-label">Valor Final:</label>
		<input type="text" class="form-control cambiarValorFinal" id="valorFin21" name="valorFin2[]" />
	</div>
	<div class="form-group multiplicadorFin2 oculto" data-id="1">
		<label for="multiplicadorFin21" class="control-label">Multiplicado por:</label>
		<input type="text" class="form-control" id="multiplicadorFin21" name="multiplicadorFin2[]" />
	</div>

   

	<div class="form-group tipoFin3 oculto" data-id="1">
		<label for="tipoFin31" class="control-label">Tipo fin</label>
		<select class="form-control selectTipoFin3" id="tipoFin31" name="tipoFin3[]" data-id="1">
            <option value="">Seleccione uno</option>
            <option value="concepto">Concepto</option>
            <option value="grupo">Grupo de concepto</option>
			<option value="variable">Variable</option>
            <option value="valor">Valor Fijo</option>
		</select>
    </div>

    <div class="form-group variableFin3 oculto" data-id="1">
		<label for="variableFin31" class="control-label">Variable Final:</label>
		<select class="form-control cambiarValorFinal" id="variableFin31" name="variableFin3[]">
			<option value="">Seleccione uno</option>
			@foreach($variables as $variable)
				<option value="{{$variable->idVariable}}">{{$variable->nombre}}</option>
			@endforeach
		</select>
    </div>
    <div class="form-group conceptoFin3 oculto" data-id="1">
		<label for="conceptoFin31" class="control-label">Concepto Final:</label>
		<select class="form-control" id="conceptoFin31" name="conceptoFin3[]">
			<option value="">Seleccione uno</option>
			@foreach($conceptos as $concepto)
				<option value="{{$concepto->idconcepto}}">{{$concepto->nombre}}</option>
			@endforeach
		</select>
    </div>
    <div class="form-group grupoFin3 oculto" data-id="1">
		<label for="grupoFin31" class="control-label">Grupo concepto Final:</label>
		<select class="form-control" id="grupoFin31" name="grupoFin3[]">
			<option value="">Seleccione uno</option>
			@foreach($grupoConceptos as $grupoConcepto)
				<option value="{{$grupoConcepto->idgrupoConcepto}}">{{$grupoConcepto->nombre}}</option>
			@endforeach
		</select>
    </div>
	<div class="form-group valorFin3 oculto" data-id="1">
		<label for="valorFin31" class="control-label">Valor Final:</label>
		<input type="text" class="form-control cambiarValorFinal" id="valorFin31" name="valorFin3[]" />
	</div>
	<div class="form-group multiplicadorFin3 oculto" data-="1">
		<label for="multiplicadorFin31" class="control-label">Multiplicado por:</label>
		<input type="text" class="form-control" id="multiplicadorFin31" name="multiplicadorFin3[]" />
	</div>


	<div class="contItemCondicion"></div>
	<input type="hidden" name="numItem" id="numItem" value="1" />
 
    <button class="btn btn-secondary" type="button" id="masItems">Agregar Item Condicion</button>

	<button class="btn btn-primary" type="submit">Agregar Condicion</button>	

</form>