<form method="post" action="/calendario/agregarCalendario" class="formGen">
	<h2>Agregar calendario</h2>
	@csrf
	<div class="form-group">
		<label for="fecha" class="control-label">Fecha *</label>
		<input type="date" class="form-control" id="fecha" required name="fecha" />
	</div>
	<div class="form-group">
		<label for="fechaInicioSemana" class="control-label">Fecha inicio semana *</label>
		<input type="date" class="form-control" id="fechaInicioSemana" required name="fechaInicioSemana" />
	</div>
	<div class="form-group">
		<label for="fechaFinSemana" class="control-label">Fecha fin semana *</label>
		<input type="date" class="form-control" id="fechaFinSemana" required name="fechaFinSemana" />
	</div>
	<button type="submit" class="btn btn-success">Crear calendario</button>
</form>