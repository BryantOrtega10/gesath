<form method="post" action="/variables/crear" class="formGen">
	<h2>Agregar variable</h2>
	@csrf
	@include('variables.forms.formulario')
	<button type="submit" class="btn btn-success">Agregar</button>
</form>