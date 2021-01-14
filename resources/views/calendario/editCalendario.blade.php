<form method="post" action="/calendario/editarCalendario/{{ $calendario->idCalendario }}" class="formGen">
	<h2>Editar calendario</h2>
    @csrf
    <input type="hidden" name="idCalendario" value = "{{ $calendario->idCalendario }}">
	<div class="form-group">
		<label for="fecha" class="control-label">Fecha *</label>
		<input type="date" class="form-control" id="fecha" required name="fecha" value = "{{ $calendario->fecha->format('Y-m-d') }}" />
	</div>
	<div class="form-group">
		<label for="fechaInicioSemana" class="control-label">Fecha inicio semana *</label>
		<input type="date" class="form-control" id="fechaInicioSemana" required name="fechaInicioSemana" value = "{{ $calendario->fechaInicioSemana->format('Y-m-d') }}" />
	</div>
	<div class="form-group">
		<label for="fechaFinSemana" class="control-label">Fecha fin semana *</label>
		<input type="date" class="form-control" id="fechaFinSemana" required name="fechaFinSemana" value = "{{ $calendario->fechaFinSemana->format('Y-m-d') }}" />
	</div>
	<button type="submit" class="btn btn-success">Guardar cambios</button>
</form>