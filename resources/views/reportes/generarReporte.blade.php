<form method="post" action="/reportes/reporteador/generarFinalReporteador" class="formGen2" target="_blank">
	<h2>Generar Reporte</h2>
    @csrf
    <input type="hidden" name="idReporte" value="{{$reporte->idReporte}}" />
    <div class="form-group">
		<label for="nombre" class="control-label">Nombre:</label>
		<input type="text" class="form-control" id="nombre" name="nombre" readonly value="{{$reporte->nombre}}" />
    </div>
    <div class="form-group">
		<label for="tipoReporte" class="control-label">Tipo Reporte:</label>
		<input type="text" class="form-control" id="tipoReporte" name="tipoReporte" readonly value="{{$reporte->tipo_reporte}}" />
    </div>
    <div class="row">
        <div class="col-9">
            <div class="form-group">
                <label for="campo" class="control-label">Campos:</label>
                <select class="form-control" id="campo" name="campo" required="">
                    <option value="">Seleccione uno</option>
                    @foreach($itemsReporte as $itemReporte)
                        <option value="{{$itemReporte->idReporteItem}}">{{$itemReporte->nombre}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-3 text-right"><button type="button" class="btn btn-success" id="addFiltro" style="margin-top: 28px">Adicionar Filtro</button></div>
    </div>
    <div class="filtros">
        <h2>Filtros</h2>
        <div class="row">
            <div class="col-3">Campo</div>
            <div class="col-2">Operador</div>
            <div class="col-3">Valor</div>
            <div class="col-2">Concector</div>
            <div class="col-2"></div>
        </div>
    </div>


	<div id="infoErrorForm" class="alert alert-danger" style="display: none;">
    </div>
    <br>
	<button type="submit" class="btn btn-success">Generar Reporte</button>
</form>
