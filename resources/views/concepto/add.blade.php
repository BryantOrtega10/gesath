<form method="post" action="/concepto/crear" class="formGen">
	<h2>Agregar concepto</h2>
	@csrf
	<div class="form-group">
		<label for="nombre" class="control-label">Nombre *</label>
		<input type="text" class="form-control" id="nombre" required name="nombre" />
	</div>
	<div class="form-group">
		<label for="fkNaturaleza" class="control-label">Naturaleza *</label>
		<select class="form-control" id="fkNaturaleza" required name="fkNaturaleza">
			<option value="">Seleccione uno</option>
			@foreach($naturalezas as $naturaleza)
				<option value="{{$naturaleza->idnaturalezaConcepto}}">{{$naturaleza->nombre}}</option>
			@endforeach
		</select>
	</div>
	<div class="form-group">
		<label for="fkTipoConcepto" class="control-label">Tipo *</label>
		<select class="form-control" id="fkTipoConcepto" required name="fkTipoConcepto">
			<option value="">Seleccione uno</option>
			@foreach($tiposConcepto as $tipoConcepto)
				<option value="{{$tipoConcepto->idtipo_concepto}}">{{$tipoConcepto->nombre}}</option>
			@endforeach
		</select>
	</div>
	<div class="form-group">
		<label for="subTipo" class="control-label">Sub tipo *</label>
		<select class="form-control" id="subTipo" required name="subTipo">
			<option value="">Seleccione uno</option>
			<option value="Dias">Dias</option>
			<option value="Formula">Formula</option>
			<option value="Porcentaje">Porcentaje</option>
			<option value="Tabla">Tabla</option>
			<option value="Valor">Valor</option>
		</select>
	</div>
	<div class="respFormulaConcepto"></div>
	<div class="form-group elementoVariable">
		<label for="fkVariable" class="control-label">Variable *</label>
		<select class="form-control" id="fkVariable" name="fkVariable">
			<option value="">Seleccione uno</option>
			@foreach($variables as $variable)
				<option value="{{$variable->idVariable}}">{{$variable->nombre}}</option>
			@endforeach
		</select>
	</div>
	<input type="hidden" value="0" class="form-control" id="numRetefuente" name="numRetefuente" />
	<div class="form-group">
		<label for="generacionAutomatica" class="control-label">Generaci&oacute;n *</label>
		<select class="form-control" id="generacionAutomatica" required name="generacionAutomatica">
			<option value="0">MANUAL</option>
			<option value="1">AUTOMATICA</option>			
		</select>
	</div>
	<div class="form-group">
		<label>Grupo concepto</label>
		<div class="checksCont">
			@foreach ($gruposConcepto as $grupoConcepto)
			<div class="row">
				<div class="col-1 text-center">
					<input type="checkbox" value="{{$grupoConcepto->idgrupoConcepto}}" id="grupoConcepto_{{$grupoConcepto->idgrupoConcepto}}" name="gruposConcepto[]"/>
				</div>
				<div class="col-10">
					<label for="grupoConcepto_{{$grupoConcepto->idgrupoConcepto}}">{{$grupoConcepto->nombre}}</label>
				</div>				
			</div>
			@endforeach
			
		</div>
	</div>




	

	<button type="submit" class="btn btn-success">Crear concepto</button>
</form>