<form method="post" action="/catalogo-contable/modificar" class="formGen">
	<h2>Modificar cuenta</h2>
	@csrf
	<input type="hidden" name="idDatosCredito" value="{{$datosCuentaCred->idDatosCuenta}}" />
	<input type="hidden" name="idDatosDebito"  value="{{$datosCuentaDeb->idDatosCuenta}}"/>
	<div class="form-group">
		<label for="fkEmpresa" class="control-label">Empresa *</label>
		<select class="form-control" id="fkEmpresa" required name="fkEmpresa">
			<option value="">Seleccione uno</option>
			@foreach($empresas as $empresa)
				<option value="{{$empresa->idempresa}}" @if($empresa->idempresa == $empresaSelect) selected @endif>{{$empresa->razonSocial}}</option>
			@endforeach
		</select>
	</div>
	<div class="form-group">
		<label for="fkCentroCosto" class="control-label">Centro costo *</label>
		<select class="form-control" id="fkCentroCosto" name="fkCentroCosto">
			<option value="">Todos</option>
			@foreach($centrosCosto as $centroCosto)
				<option value="{{$centroCosto->idcentroCosto}}" @if($centroCosto->idcentroCosto == $centroCostoSelect) selected @endif>{{$centroCosto->nombre}}</option>
			@endforeach
		</select>
	</div>
    <div class="form-group">
		<label for="cuentaCred" class="control-label">Cuenta Credito *</label>
		<select class="form-control" id="cuentaCred" name="cuentaCred">
			<option value="nueva">Nueva</option>
			@foreach($cuentas as $cuenta)
				<option value="{{$cuenta->idCatalgoContable}}" @if($cuenta->idCatalgoContable == $datosCuentaCred->fkCuenta) selected @endif>{{$cuenta->cuenta}} - {{$cuenta->descripcion}}</option>
			@endforeach
		</select>
	</div>
	<div class="cuentaCreditoNueva activo2">
		<div class="form-group">
			<label for="cuentaCred2" class="control-label">Cuenta credito *</label>
			<input type="text" class="form-control" id="cuentaCred2" name="cuentaCred2" value="{{$datosCuentaCred->cuenta}}" />
		</div>	
		<div class="form-group">
			<label for="descripcionCred" class="control-label">Descripcion cuenta credito *</label>
			<input type="text" class="form-control" id="descripcionCred" name="descripcionCred" value="{{$datosCuentaCred->descripcion}}" />
		</div>	
		<div class="form-group">
			<label for="fkTipoTerceroCred" class="control-label">Tipo tercero credito*</label>
			<select class="form-control" id="fkTipoTerceroCred"  name="fkTipoTerceroCred">
				<option value="">Seleccione uno</option>
				@foreach($tipoTerceroCuenta as $tipoCuenta)
					<option value="{{$tipoCuenta->idTipoTerceroCuenta}}" @if($datosCuentaCred->fkTipoTercero==$tipoCuenta->idTipoTerceroCuenta) selected @endif >{{$tipoCuenta->nombre}}</option>
				@endforeach
			</select>
		</div>
		<div class="form-group elementoTerceroCred @if ($datosCuentaCred->fkTipoTercero == "8") activo @endif">
			<label for="fkTerceroCred" class="control-label">Tercero credito*</label>
			<select class="form-control" id="fkTerceroCred" name="fkTerceroCred">
				<option value="">Seleccione uno</option>
				@foreach($terceros as $tercero)
					<option value="{{$tercero->idTercero}}" @if($datosCuentaCred->fkTercero==$tercero->idTercero) selected @endif >{{$tercero->razonSocial}}</option>
				@endforeach
			</select>
		</div>		
	</div>
	<div class="form-group">
		<label for="cuentaDeb" class="control-label">Cuenta Debito *</label>
		<select class="form-control" id="cuentaDeb" name="cuentaDeb">
			<option value="nueva">Nueva</option>
			@foreach($cuentas as $cuenta)
				<option value="{{$cuenta->idCatalgoContable}}" @if($cuenta->idCatalgoContable == $datosCuentaDeb->fkCuenta) selected @endif>{{$cuenta->cuenta}} - {{$cuenta->descripcion}}</option>
			@endforeach
		</select>
	</div>
	<div class="cuentaDebitoNueva activo2">
		<div class="form-group">
			<label for="cuentaDeb2" class="control-label">Cuenta debito *</label>
			<input type="text" class="form-control" id="cuentaDeb2" name="cuentaDeb2" value="{{$datosCuentaDeb->cuenta}}"/>
		</div>	
		<div class="form-group">
			<label for="descripcionDeb" class="control-label">Descripcion cuenta debito *</label>
			<input type="text" class="form-control" id="descripcionDeb" name="descripcionDeb"   value="{{$datosCuentaDeb->descripcion}}"/>
		</div>	
		<div class="form-group">
			<label for="fkTipoTerceroDeb" class="control-label">Tipo tercero debito*</label>
			<select class="form-control" id="fkTipoTerceroDeb" name="fkTipoTerceroDeb">
				<option value="">Seleccione uno</option>
				@foreach($tipoTerceroCuenta as $tipoCuenta)
					<option value="{{$tipoCuenta->idTipoTerceroCuenta}}"  @if($datosCuentaDeb->fkTipoTercero == $tipoCuenta->idTipoTerceroCuenta) selected @endif>{{$tipoCuenta->nombre}}</option>
				@endforeach
			</select>
		</div>
		<div class="form-group elementoTerceroDeb @if ($datosCuentaCred->fkTipoTercero == "8") activo @endif">
			<label for="fkTerceroDeb" class="control-label">Tercero debito*</label>
			<select class="form-control" id="fkTerceroDeb" name="fkTerceroDeb">
				<option value="">Seleccione uno</option>
				@foreach($terceros as $tercero)
					<option value="{{$tercero->idTercero}}" @if($datosCuentaDeb->fkTercero== $tercero->idTercero) selected @endif>{{$tercero->razonSocial}}</option>
				@endforeach
			</select>
		</div>		
	</div>
	<div class="form-group">
		<label for="transaccion" class="control-label">Transacción *</label>
		<select class="form-control" id="transaccion" required name="transaccion">
			<option value="Aportes" @if($datosCuentaCred->transaccion == "Aportes") selected @endif>Aportes</option>
			<option value="Provisiones" @if($datosCuentaCred->transaccion == "Provisiones") selected @endif>Provisiones</option>
			<option value="Nomina" @if($datosCuentaCred->transaccion == "Nomina") selected @endif>Nomina</option>
		</select>
	</div>
	
	<div class="form-group">
		<label for="tablaConsulta0" class="control-label">Tipo:</label>
		<select class="form-control tablaConsulta" id="tablaConsulta0" data-id="0" required name="tablaConsulta[]">
			<option value="">Seleccione uno</option>
			<option value="1" @if($datosCuentaCred->tablaConsulta == "1") selected @endif>Grupo concepto</option>
			<option value="2" @if($datosCuentaCred->tablaConsulta == "2") selected @endif>Provision</option>
			<option value="3" @if($datosCuentaCred->tablaConsulta == "3") selected @endif>Aporte empleador</option>
			<option value="4" @if($datosCuentaCred->tablaConsulta == "4") selected @endif>Concepto</option>
		</select>
	</div>
	<div class="form-group grupoConceptoCuenta @if($datosCuentaCred->tablaConsulta == "1") activo @endif" data-id="0">
		<label for="fkGrupoConcepto0" class="control-label">Grupo concepto *</label>
		<select class="form-control" id="fkGrupoConcepto0" name="fkGrupoConcepto[]">
			<option value="">Seleccione uno</option>
			@foreach($gruposConcepto as $grupoConcepto)
				<option value="{{$grupoConcepto->idgrupoConcepto}}" @if($datosCuentaCred->fkGrupoConcepto == $grupoConcepto->idgrupoConcepto) selected @endif>{{$grupoConcepto->nombre}}</option>
			@endforeach
		</select>
	</div>
	<div class="form-group grupoProvision @if($datosCuentaCred->tablaConsulta == "2") activo @endif" data-id="0">
		<label for="subTipoProvision0" class="control-label">Tipo Provision *</label>
		<select class="form-control" id="subTipoProvision0" name="subTipoProvision[]">
			<option value="">Seleccione uno</option>
			<option value="1" @if($datosCuentaCred->subTipoConsulta == "1" && $datosCuentaCred->tablaConsulta == "2") selected @endif>PRIMA</option>
			<option value="2" @if($datosCuentaCred->subTipoConsulta == "2" && $datosCuentaCred->tablaConsulta == "2") selected @endif>CESANTIAS</option>
			<option value="3" @if($datosCuentaCred->subTipoConsulta == "3" && $datosCuentaCred->tablaConsulta == "2") selected @endif>INTERESES DE CESANTIA</option>
			<option value="4" @if($datosCuentaCred->subTipoConsulta == "4" && $datosCuentaCred->tablaConsulta == "2") selected @endif>VACACIONES</option>
		</select>
	</div>
	<div class="form-group grupoAporteEmpleador @if($datosCuentaCred->tablaConsulta == "3") activo @endif" data-id="0">
		<label for="subTipoAporteEmpleador0" class="control-label">Tipo Aporte *</label>
		<select class="form-control" id="subTipoAporteEmpleador0" name="subTipoAporteEmpleador[]">
			<option value="">Seleccione uno</option>
			<option value="1" @if($datosCuentaCred->subTipoConsulta == "1" && $datosCuentaCred->tablaConsulta == "3") selected @endif>PENSIÓN</option>
			<option value="2" @if($datosCuentaCred->subTipoConsulta == "2" && $datosCuentaCred->tablaConsulta == "3") selected @endif>SALUD</option>
			<option value="3" @if($datosCuentaCred->subTipoConsulta == "3" && $datosCuentaCred->tablaConsulta == "3") selected @endif>ARL</option>
			<option value="4" @if($datosCuentaCred->subTipoConsulta == "4" && $datosCuentaCred->tablaConsulta == "3") selected @endif>CCF</option>
			<option value="5" @if($datosCuentaCred->subTipoConsulta == "5" && $datosCuentaCred->tablaConsulta == "3") selected @endif>ICBF</option>
			<option value="6" @if($datosCuentaCred->subTipoConsulta == "6" && $datosCuentaCred->tablaConsulta == "3") selected @endif>SENA</option>
		</select>
	</div>
	<div class="form-group conceptoCuenta @if($datosCuentaCred->tablaConsulta == "4") activo @endif" data-id="0">
		<label for="fkConcepto0" class="control-label">Concepto *</label>
		<select class="form-control" id="fkConcepto0" name="fkConcepto[]">
			<option value="">Seleccione uno</option>
			@foreach($conceptos as $concepto)
				<option value="{{$concepto->idconcepto}}" @if($datosCuentaCred->subTipoConsulta == $datosCuentaCred->fkConcepto) selected @endif>{{$concepto->nombre}}</option>
			@endforeach
		</select>
	</div>
	<div class="alert alert-danger print-error" style="display:none">
		<ul></ul>
	</div>
	
	<button type="submit" class="btn btn-success">Modificar</button>
</form>