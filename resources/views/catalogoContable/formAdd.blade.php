<form method="post" action="/catalogo-contable/crear" class="formGen">
	<h2>Agregar cuenta</h2>
	@csrf
	<div class="form-group">
		<label for="fkEmpresa" class="control-label">Empresa *</label>
		<select class="form-control" id="fkEmpresa" required name="fkEmpresa">
			<option value="">Seleccione uno</option>
			@foreach($empresas as $empresa)
				<option value="{{$empresa->idempresa}}">{{$empresa->razonSocial}}</option>
			@endforeach
		</select>
	</div>
	<div class="form-group">
		<label for="fkCentroCosto" class="control-label">Centro costo *</label>
		<select class="form-control" id="fkCentroCosto" name="fkCentroCosto">
			<option value="">Todos</option>
		</select>
	</div>

    <div class="form-group">
		<label for="cuentaCred" class="control-label">Cuenta Credito *</label>
		<select class="form-control" id="cuentaCred" name="cuentaCred">
			<option value="nueva">Nueva</option>
		</select>
	</div>
	<div class="cuentaCreditoNueva activo">
		<div class="form-group">
			<label for="cuentaCred2" class="control-label">Cuenta credito *</label>
			<input type="text" class="form-control" id="cuentaCred2" name="cuentaCred2" />
		</div>	
		<div class="form-group">
			<label for="descripcionCred" class="control-label">Descripcion cuenta credito *</label>
			<input type="text" class="form-control" id="descripcionCred" name="descripcionCred" />
		</div>	
		<div class="form-group">
			<label for="fkTipoTerceroCred" class="control-label">Tipo tercero credito*</label>
			<select class="form-control" id="fkTipoTerceroCred"  name="fkTipoTerceroCred">
				<option value="">Seleccione uno</option>
				@foreach($tipoTerceroCuenta as $tipoCuenta)
					<option value="{{$tipoCuenta->idTipoTerceroCuenta}}">{{$tipoCuenta->nombre}}</option>
				@endforeach
			</select>
		</div>
		<div class="form-group elementoTerceroCred">
			<label for="fkTerceroCred" class="control-label">Tercero credito*</label>
			<select class="form-control" id="fkTerceroCred" name="fkTerceroCred">
				<option value="">Seleccione uno</option>
				@foreach($terceros as $tercero)
					<option value="{{$tercero->idTercero}}">{{$tercero->razonSocial}}</option>
				@endforeach
			</select>
		</div>		
	</div>
	<div class="form-group">
		<label for="cuentaDeb" class="control-label">Cuenta Debito *</label>
		<select class="form-control" id="cuentaDeb" name="cuentaDeb">
			<option value="nueva">Nueva</option>
		</select>
	</div>
	<div class="cuentaDebitoNueva activo">
		<div class="form-group">
			<label for="cuentaDeb2" class="control-label">Cuenta debito *</label>
			<input type="text" class="form-control" id="cuentaDeb2" name="cuentaDeb2" />
		</div>	
		<div class="form-group">
			<label for="descripcionDeb" class="control-label">Descripcion cuenta debito *</label>
			<input type="text" class="form-control" id="descripcionDeb" name="descripcionDeb" />
		</div>	
		<div class="form-group">
			<label for="fkTipoTerceroDeb" class="control-label">Tipo tercero debito*</label>
			<select class="form-control" id="fkTipoTerceroDeb" name="fkTipoTerceroDeb">
				<option value="">Seleccione uno</option>
				@foreach($tipoTerceroCuenta as $tipoCuenta)
					<option value="{{$tipoCuenta->idTipoTerceroCuenta}}">{{$tipoCuenta->nombre}}</option>
				@endforeach
			</select>
		</div>
		<div class="form-group elementoTerceroDeb">
			<label for="fkTerceroDeb" class="control-label">Tercero debito*</label>
			<select class="form-control" id="fkTerceroDeb" name="fkTerceroDeb">
				<option value="">Seleccione uno</option>
				@foreach($terceros as $tercero)
					<option value="{{$tercero->idTercero}}">{{$tercero->razonSocial}}</option>
				@endforeach
			</select>
		</div>		
	</div>
	<div class="form-group">
		<label for="transaccion" class="control-label">Transacción *</label>
		<select class="form-control" id="transaccion" required name="transaccion">
			<option value="Aportes">Aportes</option>
			<option value="Provisiones">Provisiones</option>
			<option value="Nomina">Nomina</option>
		</select>
	</div>
	
	<div class="form-group">
		<label for="tablaConsulta0" class="control-label">Tipo:</label>
		<select class="form-control tablaConsulta" id="tablaConsulta0" data-id="0" required name="tablaConsulta[]">
			<option value="">Seleccione uno</option>
			<option value="1">Grupo concepto</option>
			<option value="2">Provision</option>
			<option value="3">Aporte empleador</option>
			<option value="4">Concepto</option>
		</select>
	</div>
	<div class="form-group grupoConceptoCuenta" data-id="0">
		<label for="fkGrupoConcepto0" class="control-label">Grupo concepto *</label>
		<select class="form-control" id="fkGrupoConcepto0" name="fkGrupoConcepto[]">
			<option value="">Seleccione uno</option>
			@foreach($gruposConcepto as $grupoConcepto)
				<option value="{{$grupoConcepto->idgrupoConcepto}}">{{$grupoConcepto->nombre}}</option>
			@endforeach
		</select>
	</div>
	<div class="form-group grupoProvision" data-id="0">
		<label for="subTipoProvision0" class="control-label">Tipo Provision *</label>
		<select class="form-control" id="subTipoProvision0" name="subTipoProvision[]">
			<option value="">Seleccione uno</option>
			<option value="1">PRIMA</option>
			<option value="2">CESANTIAS</option>
			<option value="3">INTERESES DE CESANTIA</option>
			<option value="4">VACACIONES</option>
		</select>
	</div>
	<div class="form-group grupoAporteEmpleador" data-id="0">
		<label for="subTipoAporteEmpleador0" class="control-label">Tipo Aporte *</label>
		<select class="form-control" id="subTipoAporteEmpleador0" name="subTipoAporteEmpleador[]">
			<option value="">Seleccione uno</option>
			<option value="1">PENSIÓN</option>
			<option value="2">SALUD</option>
			<option value="3">ARL</option>
			<option value="4">CCF</option>
			<option value="5">ICBF</option>
			<option value="6">SENA</option>
		</select>
	</div>
	<div class="form-group conceptoCuenta" data-id="0">
		<label for="fkConcepto0" class="control-label">Concepto *</label>
		<select class="form-control" id="fkConcepto0" name="fkConcepto[]">
			<option value="">Seleccione uno</option>
			@foreach($conceptos as $concepto)
				<option value="{{$concepto->idconcepto}}">{{$concepto->nombre}}</option>
			@endforeach
		</select>
	</div>
	<div class="alert alert-danger print-error" style="display:none">
		<ul></ul>
	</div>
	
	<button type="submit" class="btn btn-success">Crear</button>
</form>