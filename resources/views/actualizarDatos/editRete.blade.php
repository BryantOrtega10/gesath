<form method="post" action="/ActualizarDatos/valoresRetencion/update/{{$tabla->idTablaRetencion}}" class="formGen">
	<h2>Agregar cargo</h2>
	@csrf
	<div class="form-group">
		<label for="minimo" class="control-label">Minimo</label>
		<input type="number" min="0" class="form-control" id="minimo" name="minimo" value="{{$tabla->minimo}}" />
	</div>

    <div class="form-group">
		<label for="maximo" class="control-label">Maximo</label>
		<input type="number" min="0" class="form-control" id="maximo" name="maximo" value="{{$tabla->maximo}}"/>
	</div>

    <div class="form-group">
		<label for="adicion" class="control-label">Adicion</label>
		<input type="number" required min="0" class="form-control" id="adicion" name="adicion" value="{{$tabla->adicion}}" />
	</div>
    <div class="row">
        <div class="col-11">
            <div class="form-group">
                <label for="porcentaje" class="control-label">Porcentaje</label>
                <input type="number" required min="0"  max="100" class="form-control" id="porcentaje" name="porcentaje" value="{{($tabla->porcentaje * 100)}}" />
            </div>
        </div>
        <div class="col-1">
            <div style="padding-top: 40px">%</div>
        </div>
    </div>

	<button type="submit" class="btn btn-success">Modificar</button>
</form>