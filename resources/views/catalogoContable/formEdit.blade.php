<form method="post" action="/catalogo-contable/modificar" class="formGen">
	<h2>Modificar cuenta</h2>
	@csrf
    <input type="hidden" class="form-control" name="idCatalgoContable" value="{{$catalogo->idCatalgoContable}}" />
    <div class="form-group">
		<label for="cuenta" class="control-label">Cuenta *</label>
		<input type="text" class="form-control" id="cuenta" required name="cuenta" value="{{$catalogo->cuenta}}" />
    </div>
    <div class="form-group">
		<label for="descripcion" class="control-label">Descripcion *</label>
		<input type="text" class="form-control" id="descripcion" required name="descripcion" value="{{$catalogo->descripcion}}" />
	</div>
	<div class="form-group">
		<label for="transaccion" class="control-label">Transacción *</label>
		<select class="form-control" id="transaccion" required name="transaccion">
			<option value="Aportes" @if($catalogo->transaccion == "Aportes") selected @endif>Aportes</option>
			<option value="Nomina" @if($catalogo->transaccion == "Nomina") selected @endif>Nomina</option>
			<option value="Provisiones" @if($catalogo->transaccion == "Provisiones") selected @endif>Provisiones</option>
		</select>
	</div>
	<div class="form-group">
		<label for="tipoComportamiento" class="control-label">Comportamiento *</label>
		<select class="form-control" id="tipoComportamiento" required name="tipoComportamiento">
			<option value="1" @if ($catalogo->tipoComportamiento == "1") selected @endif>Tipo 1</option>
			<option value="2" @if ($catalogo->tipoComportamiento == "2") selected @endif>Tipo 2</option>
		</select>
	</div>	
	<div class="form-group">
		<label for="fkTipoTercero" class="control-label">Tipo tercero *</label>
		<select class="form-control" id="fkTipoTercero" required name="fkTipoTercero">
			<option value="">Seleccione uno</option>
			@foreach($tipoTerceroCuenta as $tipoCuenta)
				<option value="{{$tipoCuenta->idTipoTerceroCuenta}}" @if ($catalogo->fkTipoTercero == $tipoCuenta->idTipoTerceroCuenta) selected @endif>{{$tipoCuenta->nombre}}</option>
			@endforeach
		</select>
	</div>
	<div class="form-group elementoTercero">
		<label for="fkTercero" class="control-label">Tercero *</label>
		<select class="form-control" id="fkTercero" name="fkTercero">
			<option value="">Seleccione uno</option>
			@foreach($terceros as $tercero)
				<option value="{{$tercero->idTercero}}" @if ($catalogo->fkTercero == $tercero->idTercero) selected @endif>{{$tercero->razonSocial}}</option>
			@endforeach
		</select>
	</div>
	<div class="form-group">
		<label for="fkEmpresa" class="control-label">Empresa *</label>
		<select class="form-control" id="fkEmpresa" required name="fkEmpresa">
			<option value="">Seleccione uno</option>
			@foreach($empresas as $empresa)
				<option value="{{$empresa->idempresa}}" @if ($empresa->idempresa == $catalogo->fkEmpresa) selected @endif>{{$empresa->razonSocial}}</option>
			@endforeach
		</select>
	</div>
	<div class="form-group">
		<label for="fkCentroCosto" class="control-label">Centro costo *</label>
		<select class="form-control" id="fkCentroCosto" name="fkCentroCosto">
            <option value="">Todos</option>            
            @foreach($centrosCosto as $centroCosto)
				<option value="{{$centroCosto->idcentroCosto}}" @if ($centroCosto->idcentroCosto == $catalogo->fkCentroCosto) selected @endif>{{$centroCosto->nombre}}</option>
			@endforeach
		</select>
	</div>
    <h3>Grupo<button type="button" class="btn btn-success-secondary" id="addGrupoCuenta" data-id="{{sizeof($datoscuenta) - 1}}"><i class="fas fa-plus-square"></i></button></h3>
    <div class="datosCuenta">
        @foreach ($datoscuenta as $key => $datoCuenta)
        <div class="contGrupoCuenta" data-id="{{$key}}">
            @if ($key!=0)
                <button type="button" class="btn btn-danger quitarGrupo" data-id="{{$key}}"><i class="fas fa-window-close"></i></button>
            @endif
            <div class="form-group">
                <label for="tablaConsulta{{$key}}" class="control-label">Tipo:</label>
                <select class="form-control tablaConsulta" id="tablaConsulta{{$key}}" data-id="{{$key}}" required name="tablaConsulta[]">
                    <option value="">Seleccione uno</option>
                    <option value="1" @if($datoCuenta->tablaConsulta == "1") selected @endif>Grupo concepto</option>
                    <option value="2" @if($datoCuenta->tablaConsulta == "2") selected @endif>Provision</option>
                    <option value="3" @if($datoCuenta->tablaConsulta == "3") selected @endif>Aporte empleador</option>
                </select>
            </div>
            <div class="form-group grupoConceptoCuenta  @if($datoCuenta->tablaConsulta == "1") activo @endif" data-id="{{$key}}">
                <label for="fkGrupoConcepto{{$key}}" class="control-label">Grupo concepto *</label>
                <select class="form-control" id="fkGrupoConcepto{{$key}}" name="fkGrupoConcepto[]">
                    <option value="">Seleccione uno</option>
                    @foreach($gruposConcepto as $grupoConcepto)
                        <option value="{{$grupoConcepto->idgrupoConcepto}}" @if($datoCuenta->fkGrupoConcepto == $grupoConcepto->idgrupoConcepto) selected @endif>{{$grupoConcepto->nombre}}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group grupoProvision @if($datoCuenta->tablaConsulta == "2") activo @endif" data-id="{{$key}}">
                <label for="subTipoProvision{{$key}}" class="control-label">Tipo Provision *</label>
                <select class="form-control" id="subTipoProvision{{$key}}" name="subTipoProvision[]">
                    <option value="">Seleccione uno</option>
                    <option value="1" @if($datoCuenta->subTipoConsulta == "1") selected @endif>PRIMA</option>
                    <option value="2" @if($datoCuenta->subTipoConsulta == "2") selected @endif>CESANTIAS</option>
                    <option value="3" @if($datoCuenta->subTipoConsulta == "3") selected @endif>INTERESES DE CESANTIA</option>
                    <option value="4" @if($datoCuenta->subTipoConsulta == "4") selected @endif>VACACIONES</option>
                </select>
            </div>
            <div class="form-group grupoAporteEmpleador @if($datoCuenta->tablaConsulta == "3") activo @endif" data-id="{{$key}}">
                <label for="subTipoAporteEmpleador{{$key}}" class="control-label">Tipo Aporte *</label>
                <select class="form-control" id="subTipoAporteEmpleador{{$key}}" name="subTipoAporteEmpleador[]">
                    <option value="">Seleccione uno</option>
                    <option value="1" @if($datoCuenta->subTipoConsulta == "1") selected @endif>PENSIÓN</option>
                    <option value="2" @if($datoCuenta->subTipoConsulta == "2") selected @endif>SALUD</option>
                    <option value="3" @if($datoCuenta->subTipoConsulta == "3") selected @endif>ARL</option>
                    <option value="4" @if($datoCuenta->subTipoConsulta == "4") selected @endif>CCF</option>
                    <option value="5" @if($datoCuenta->subTipoConsulta == "5") selected @endif>ICBF</option>
                    <option value="6" @if($datoCuenta->subTipoConsulta == "6") selected @endif>SENA</option>
                </select>
            </div>
        </div>
        @endforeach
    </div>	
	
	<button type="submit" class="btn btn-success">Modificar cuenta</button>
</form>