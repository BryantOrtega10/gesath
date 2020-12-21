@isset($variable) <input type="hidden" name="idVariable" id="idVariable" value="{{$variable->idVariable}}" /> @endisset
<div class="form-group">
	<label for="nombre" class="control-label">Nombre:</label>
	<input type="text" class="form-control" id="nombre" name="nombre" value="@isset($variable){{$variable->nombre}}@endisset" />
</div>
<div class="form-group">
	<label for="descripcion" class="control-label">Descripci&oacute;n:</label>
	<textarea rows="3" class="form-control" id="descripcion" name="descripcion">@isset($variable){{$variable->descripcion}}@endisset</textarea>
</div>
<div class="form-group">
	<label for="tipoGeneracion" class="control-label">Tipo Generaci&oacute;n:</label>
	<select class="form-control" id="tipoGeneracion" name="tipoGeneracion">
		<option value="Valor" @isset($variable) @if($variable->tipoGeneracion == 'Valor') selected="" @endif @endisset>Valor</option>
		<option value="Formula" @isset($variable) @if($variable->tipoGeneracion == 'Formula') selected="" @endif @endisset>Formula</option>
	</select>
</div>
<div class="form-group">
	<label for="tipoCampo" class="control-label">Tipo Campo:</label>
	<select class="form-control" id="tipoCampo" name="tipoCampo">
		<option value="">Seleccione uno</option>
		@foreach($tipoCampo as $tipo)
			<option value="{{$tipo->idTipoCampo}}"  @isset($variable) @if($variable->fkTipoCampo == $tipo->idTipoCampo) selected="" @endif @endisset>{{$tipo->nombre}}</option>
		@endforeach
	</select>
</div>
<div class="form-group">
	<label for="valor" class="control-label">Valor:</label>
	<input type="text" class="form-control" id="valor" name="valor" data-validar="@isset($variable->tipoValidacion){{$variable->tipoValidacion}}@else decimal @endisset" value="@isset($variable){{$variable->valor}}@endisset" />
</div>
<div class="respFormulaVariable"></div>