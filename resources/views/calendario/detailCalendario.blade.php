<form method="post" action="/calendario/agregarCalendario" class="formGen">
    <h2>Detalle calendario</h2>
    
	@csrf
	<div class="form-group">
		<label for="fecha" class="control-label">Fecha *</label>
		<input type="date" class="form-control" id="fecha" disabled name="fecha" value = "{{ $calendario->fecha->format('Y-m-d') }}" />
	</div>
	<div class="form-group">
		<label for="fechaInicioSemana" class="control-label">Fecha inicio semana *</label>
		<input type="date" class="form-control" id="fechaInicioSemana" disabled name="fechaInicioSemana" value = "{{ $calendario->fechaInicioSemana->format('Y-m-d') }}" />
	</div>
	<div class="form-group">
		<label for="fechaFinSemana" class="control-label">Fecha fin semana *</label>
		<input type="date" class="form-control" id="fechaFinSemana" disabled name="fechaFinSemana" value = "{{ $calendario->fechaFinSemana->format('Y-m-d') }}" />
	</div>
</form>