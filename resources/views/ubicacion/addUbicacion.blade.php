<form method="post" action="/ubicacion/crear" class="formGen">
	<h2>Agregar Ubicacion</h2>
    @csrf
    <div class="form-group">
		<label for="tUbicacion" class="control-label">Tipo ubicacion:</label>
		<select class="form-control" id="tUbicacion" name="tUbicacion" required="">
			<option value="">Seleccione uno</option>
			@foreach($tUbicacion as $tUbicacio)
				<option value="{{$tUbicacio->idtipoUbicacion}}">{{$tUbicacio->nombre}}</option>
			@endforeach
		</select>
    </div>
    <div class="resTipoUbicacion"></div>
	<div class="form-group">
		<label for="codigo" class="control-label">C&oacute;digo:</label>
		<input type="text" class="form-control" id="codigo" name="codigo" />
    </div>
    <div class="form-group">
		<label for="nombre" class="control-label">Nombre:</label>
		<input type="text" class="form-control" id="nombre" name="nombre" />
    </div>
	<button type="submit" class="btn btn-success">Crear ubicacion</button>
</form>