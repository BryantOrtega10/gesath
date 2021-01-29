<form method="post" action="/nomina/distri/crear" class="formGen">
	<h2>Agregar Distribucion</h2>
	@csrf
	
	<div class="form-group">
		<label for="fkNomina" class="control-label">Nomina *</label>
		<select class="form-control" id="fkNomina" required name="fkNomina">
			<option value="">Seleccione uno</option>
			@foreach($nominas as $nomina)
				<option value="{{$nomina->idNomina}}">{{$nomina->nombre}}</option>
			@endforeach
		</select>
	</div>
	<div class="form-group">
		<label for="fechaInicio" class="control-label">Fecha inicio *</label>
		<input type="date" class="form-control" id="fechaInicio" name="fechaInicio" />
    </div>
    <div class="form-group">
		<label for="fechaFin" class="control-label">Fecha fin *</label>
		<input type="date" class="form-control" id="fechaFin" name="fechaFin" />
    </div>
    <div class="alert alert-danger" role="alert" id="infoErrorForm" style="display: none;"></div>
	<button type="submit" class="btn btn-success">Agregar nuevo</button>
</form>