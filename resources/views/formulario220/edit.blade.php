<form method="post" action="/formulario220/modificar" class="formGen">
    <h2>Modificar Año Formulario 220</h2>
    <input type="hidden" name="idFormulario" value="{{$formulario->idFormulario220}}" />
    <input type="hidden" name="fotoAnt" value="{{$formulario->rutaImagen}}" />
    @csrf
    <div class="form-group">
		<label for="anio" class="control-label">Año:</label>
		<input type="text" class="form-control" id="anio" name="anio" required value="{{$formulario->anio}}" />
    </div>
    <div class="form-group">
		<label for="imagen" class="control-label">Imagen:</label>
		<input type="file" accept="image/png" class="form-control" id="imagen" name="imagen" />
    </div>
	<div class="form-group">
		<label for="punto1" class="control-label">Punto 1:</label>
		<input type="text" class="form-control" id="punto1" name="punto1" required value="{{$formulario->punto1}}" />
    </div>
    <div class="form-group">
		<label for="punto2" class="control-label">Punto 2:</label>
		<input type="text" class="form-control" id="punto2" name="punto2" required value="{{$formulario->punto2}}"/>
    </div>
    <div class="form-group">
		<label for="punto3" class="control-label">Punto 3:</label>
		<input type="text" class="form-control" id="punto3" name="punto3" required value="{{$formulario->punto3}}" />
    </div>
    <div class="form-group">
		<label for="punto4" class="control-label">Punto 4:</label>
		<input type="text" class="form-control" id="punto4" name="punto4" required value="{{$formulario->punto4}}" />
    </div>
    <div class="form-group">
		<label for="punto5" class="control-label">Punto 5:</label>
		<input type="text" class="form-control" id="punto5" name="punto5" required value="{{$formulario->punto5}}" />
    </div>
    <div class="form-group">
        <label for="punto6" class="control-label">Punto 6:</label>
		<input type="text" class="form-control" id="punto6" name="punto6" required value="{{$formulario->punto6}}" />
    </div>
	<div id="infoErrorForm" class="alert alert-danger" style="display: none;">
	</div>
	<button type="submit" class="btn btn-success">Modificar</button>
</form>