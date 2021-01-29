<form method="post" class="formGen" action="/variables/modificar">
	<h2>Modificar variable</h2>
	@csrf
	@include('variables.forms.formulario')
	<div class="alert alert-danger" role="alert" id="infoErrorForm" style="display: none;"></div>
	<button type="submit" class="btn btn-success">Modificar</button>
</form>