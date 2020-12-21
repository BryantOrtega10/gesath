<form method="post" action="/catalogo-contable/crear" class="formGen">
	<h2>Agregar cuenta</h2>
	@csrf
    
    <div class="form-group">
		<label for="cuenta" class="control-label">Cuenta *</label>
		<input type="text" class="form-control" id="cuenta" required name="cuenta" />
    </div>
    <div class="form-group">
		<label for="descripcion" class="control-label">Descripcion *</label>
		<input type="text" class="form-control" id="descripcion" required name="descripcion" />
	</div>
	<div class="form-group">
		<label for="transaccion" class="control-label">Transacción *</label>
		<select class="form-control" id="transaccion" required name="transaccion">
			<option value="Aportes">Aportes</option>
			<option value="Nomina">Nomina</option>
			<option value="Provisiones">Provisiones</option>
		</select>
	</div>
	<div class="form-group">
		<label for="tipoComportamiento" class="control-label">Comportamiento *</label>
		<select class="form-control" id="tipoComportamiento" required name="tipoComportamiento">
			<option value="1">Tipo 1</option>
			<option value="2">Tipo 2</option>
		</select>
	</div>	
	<div class="form-group">
		<label for="fkTipoTercero" class="control-label">Tipo tercero *</label>
		<select class="form-control" id="fkTipoTercero" required name="fkTipoTercero">
			<option value="">Seleccione uno</option>
			@foreach($tipoTerceroCuenta as $tipoCuenta)
				<option value="{{$tipoCuenta->idTipoTerceroCuenta}}">{{$tipoCuenta->nombre}}</option>
			@endforeach
		</select>
	</div>
	<div class="form-group elementoTercero">
		<label for="fkTercero" class="control-label">Tercero *</label>
		<select class="form-control" id="fkTercero" name="fkTercero">
			<option value="">Seleccione uno</option>
			@foreach($terceros as $tercero)
				<option value="{{$tercero->idTercero}}">{{$tercero->razonSocial}}</option>
			@endforeach
		</select>
	</div>
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
	<h3>Grupo<button type="button" class="btn btn-success-secondary" id="addGrupoCuenta" data-id="0"><i class="fas fa-plus-square"></i></button></h3>
	<div class="form-group">
		<label for="tablaConsulta0" class="control-label">Tipo:</label>
		<select class="form-control tablaConsulta" id="tablaConsulta0" data-id="0" required name="tablaConsulta[]">
			<option value="">Seleccione uno</option>
			<option value="1">Grupo concepto</option>
			<option value="2">Provision</option>
			<option value="3">Aporte empleador</option>
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
	<div class="datosCuenta"></div>	
	
	<button type="submit" class="btn btn-success">Crear cuenta</button>
</form>