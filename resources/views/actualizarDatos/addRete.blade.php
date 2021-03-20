<form method="post" action="/ActualizarDatos/valoresRetencion/insert" class="formGen">
	<h2>Agregar cargo</h2>
	@csrf
	<div class="form-group">
		<label for="minimo" class="control-label">Minimo</label>
		<input type="number" min="0" class="form-control" id="minimo" name="minimo" />
	</div>

    <div class="form-group">
		<label for="maximo" class="control-label">Maximo</label>
		<input type="number" min="0" class="form-control" id="maximo" name="maximo" />
	</div>

    <div class="form-group">
		<label for="adicion" class="control-label">Adicion</label>
		<input type="number" required min="0" class="form-control" id="adicion" name="adicion" />
	</div>
    <div class="row">
        <div class="col-11">
            <div class="form-group">
                <label for="porcentaje" class="control-label">Porcentaje</label>
                <input type="number" required min="0"  max="100" class="form-control" id="porcentaje" name="porcentaje" />
            </div>
        </div>
        <div class="col-1">
            <div style="padding-top: 40px">%</div>
        </div>
    </div>

	<button type="submit" class="btn btn-success">Crear</button>
</form>