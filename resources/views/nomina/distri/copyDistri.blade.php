<form method="post" action="/nomina/distri/copiar" class="formGen">
    <input type="hidden" class="form-control" name="id_distri_centro_costo" value="{{$distri->id_distri_centro_costo}}" />
	<h2>Copiar Distribucion</h2>
	@csrf
	
	<div class="form-group hasText">
		<label for="fkNomina" class="control-label">Nomina *</label>
        <input type="text" class="form-control" id="fkNominaNom" name="fkNominaNom" readonly value="{{$distri->nombre}}" />
        <input type="hidden" id="fkNomina" name="fkNomina" readonly value="{{$distri->fkNomina}}" />
	</div>
	<div class="form-group">
		<label for="fechaInicio" class="control-label">Fecha inicio *</label>
		<input type="date" class="form-control" id="fechaInicio" name="fechaInicio"  value="{{$distri->fechaInicio}}"/>
    </div>
    <div class="form-group">
		<label for="fechaFin" class="control-label">Fecha fin *</label>
		<input type="date" class="form-control" id="fechaFin" name="fechaFin"  value="{{$distri->fechaFin}}"/>
    </div>
    <div class="alert alert-danger" role="alert" id="infoErrorForm" style="display: none;"></div>
	<button type="submit" class="btn btn-success">Copiar Distribucion</button>
</form>