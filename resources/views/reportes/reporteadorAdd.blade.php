<form method="post" action="/reportes/reporteador/crear" class="formGen">
	<h2>Agregar Reporte</h2>
    @csrf
    
    <div class="form-group">
		<label for="nombre" class="control-label">Nombre:</label>
		<input type="text" class="form-control" id="nombre" name="nombre" required />
    </div>
    <div class="form-group">
		<label for="tipoReporte" class="control-label">Tipo Reporte:</label>
		<select class="form-control" id="tipoReporte" name="tipoReporte" required="">
			<option value="">Seleccione uno</option>
			@foreach($tipo_reportes as $tipo_reporte)
				<option value="{{$tipo_reporte->idTipoReporte}}">{{$tipo_reporte->nombre}}</option>
			@endforeach
		</select>
    </div>
    <div id="contItems" class="selectsMultiples"></div>
	<div id="infoErrorForm" class="alert alert-danger" style="display: none;">
	</div>
	<button type="submit" class="btn btn-success">Crear reporte</button>
</form>
