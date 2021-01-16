<form method="post" action="/reportes/reporteador/modificar" class="formGen">
	<h2>Modificar Reporte</h2>
    @csrf
    <input type="hidden" name="idReporte" value="{{$reporte->idReporte}}" />
    <div class="form-group">
		<label for="nombre" class="control-label">Nombre:</label>
		<input type="text" class="form-control" id="nombre" name="nombre" required value="{{$reporte->nombre}}" />
    </div>
    <div class="form-group">
		<label for="tipoReporte" class="control-label">Tipo Reporte:</label>
		<select class="form-control" id="tipoReporte" name="tipoReporte" required="">
			<option value="">Seleccione uno</option>
			@foreach($tipo_reportes as $tipo_reporte)
				<option value="{{$tipo_reporte->idTipoReporte}}" @if ($reporte->fkTipoReporte == $tipo_reporte->idTipoReporte)
                    selected
                @endif>
                    {{$tipo_reporte->nombre}}
                </option>
			@endforeach
		</select>
    </div>
    <div id="contItems" class="selectsMultiples">
        @include('reportes.reporteadorSelect', [
            'items_tipo_reporte' => $items_tipo_reporte,
            'items_tipo_reporte_select' => $items_tipo_reporte_select
        ])
    </div>
	<div id="infoErrorForm" class="alert alert-danger" style="display: none;">
	</div>
	<button type="submit" class="btn btn-success">Modificar reporte</button>
</form>
