<form method="post" action="/transacciones/crear" class="formGen">
	<h2>Agregar Transaccion</h2>
	@csrf
	
	<div class="form-group">
		<label for="fkGrupoConcepto" class="control-label">Grupos de concepto *</label>
		<select class="form-control" id="fkGrupoConcepto" required name="fkGrupoConcepto">
			<option value="">Seleccione uno</option>
			@foreach($grupoconceptos as $grupoconcepto)
				<option value="{{$grupoconcepto->idgrupoConcepto}}">{{$grupoconcepto->nombre}}</option>
			@endforeach
		</select>
	</div>
	<div class="form-group">
		<label for="fkCuentaDebito" class="control-label">Cuenta Debito *</label>
		<select class="form-control" id="fkCuentaDebito" required name="fkCuentaDebito">
			<option value="">Seleccione uno</option>
			@foreach($cuentas as $cuenta)
				<option value="{{$cuenta->idCatalgoContable}}">{{$cuenta->cuenta}} - {{$cuenta->descripcion}}</option>
			@endforeach
		</select>
	</div>
	<div class="form-group">
		<label for="fkCuentaCredito" class="control-label">Cuenta Credito *</label>
		<select class="form-control" id="fkCuentaCredito" required name="fkCuentaCredito">
			<option value="">Seleccione uno</option>
			@foreach($cuentas as $cuenta)
				<option value="{{$cuenta->idCatalgoContable}}">{{$cuenta->cuenta}} - {{$cuenta->descripcion}}</option>
			@endforeach
		</select>
	</div>
	<div class="form-group">
		<label for="fkCentroCosto" class="control-label">Centro de costo *</label>
		<select class="form-control" id="fkCentroCosto" required name="fkCentroCosto">
			<option value="">Seleccione uno</option>
			@foreach($centrosCosto as $centroCosto)
				<option value="{{$centroCosto->idcentroCosto}}">{{$centroCosto->razonSocial}} - {{$centroCosto->nombre}}</option>
			@endforeach
		</select>
	</div>
	
	
	<button type="submit" class="btn btn-success">Crear transaccion</button>
</form>