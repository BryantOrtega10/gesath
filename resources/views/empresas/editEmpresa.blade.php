<form action="/empresa/editarEmpresa" class="formEdit" method = "POST">
    @csrf
    <input type="hidden" id = "idempresa" name="idempresa" value = "{{ $empresa->idempresa }}">
    <div class="form-group text-center">
        <h3>Logo</h3>
        <img style = "max-width: 200px;" src = "/storage/logosEmpresas/{{ $empresa->logoEmpresa }}">
    </div>
    <div class="form-group">
        <label for="documento">Cambiar logo</label>
        <input type="file" class="form-control" id="logoEmpresa" name = "logoEmpresa">
    </div>
    <div class="row">
        <div class="col form-group">
            <label for="fkTipoCompania">Tipo de compañia</label>
            <select name="fkTipoCompania" id="fkTipoCompania" class="form-control">
                <option value="">-- Seleccione una opción --</option>
                @foreach ($tipoComp as $comp)
                    <option value="{{ $comp->idtipoCompania}}"
                    @if ($comp->idtipoCompania == old('fkTipoCompania', $empresa->fkTipoCompania))
                        selected="selected"
                    @endif
                    >{{ $comp->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col form-group">
            <label for="fkTipoAportante">Tipo de aportante</label>
            <select name="fkTipoAportante" id="fkTipoAportante" class="form-control">
                <option value="">-- Seleccione una opción --</option>
                @foreach ($tipoApor as $apor)
                    <option value="{{ $apor->idtipoAportante}}"
                    @if ($apor->idtipoAportante == old('fkTipoAportante', $empresa->fkTipoAportante))
                        selected="selected"
                    @endif
                    >{{ $apor->nombre }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="razonSocial">Razón Social</label>
        <input type="text" class="form-control" id="razonSocial" name = "razonSocial" value = "{{ $empresa->razonSocial}}">
    </div>

    <div class="form-group">
        <label for="fkTipoIdentificacion">Tipo de identificación</label>
        <select name="fkTipoIdentificacion" id="fkTipoIdentificacion" class="form-control">
            <option value="">-- Seleccione una opción --</option>
            @foreach ($tipoIdent as $tipo)
                <option value="{{ $tipo->idtipoIdentificacion}}"
                @if ($tipo->idtipoIdentificacion == old('fkTipoIdentificacion', $empresa->fkTipoIdentificacion))
                    selected="selected"
                @endif
                >{{ $tipo->nombre }}</option>
            @endforeach
        </select>
    </div>

    <div class="row">
        <div class="col form-group">
            <label for="documento">NIT</label>
            <input type="number" class="form-control" id="documento" name = "documento" value = "{{ $empresa->documento }}">
        </div>
        <div class="col form-group">
            <label for="digitoVerificacion">Dígito verificación</label>
            <input type="number" class="form-control" id="digitoVerificacion" name = "digitoVerificacion" value = "{{ $empresa->digitoVerificacion }}">
        </div>
    </div>

    <div class="row">
        <div class="col form-group">
            <label for="sigla">Sigla</label>
            <input type="text" class="form-control" id="sigla" name = "sigla" value = "{{ $empresa->sigla }}" >
        </div>
        <div class="col form-group">
            <label for="dominio">Dominio</label>
            <input type="text" class="form-control" id="dominio" name = "dominio" value = "{{ $empresa->dominio }}">
        </div>
    </div>

    <div class="form-group">
        <label for="representanteLegal">Nombre Representante Legal</label>
        <input type="text" class="form-control" id="representanteLegal" name = "representanteLegal" value = "{{ $empresa->representanteLegal}}">
    </div>   
    
    <div class="form-group">
        <label for="docRepresentante">Tipo de identificación representante</label>
        <select name="docRepresentante" id="docRepresentante" class="form-control">
            <option value="">-- Seleccione una opción --</option>
            @foreach ($tipoIdent as $tipo)
                <option value="{{ $tipo->idtipoIdentificacion}}"
                @if ($tipo->idtipoIdentificacion == old('docRepresentante', $empresa->docRepresentante))
                    selected="selected"
                @endif
                >{{ $tipo->nombre }}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label for="numDocRepresentante">Número Representante Legal</label>
        <input type="text" class="form-control" id="numDocRepresentante" name = "numDocRepresentante" value = "{{ $empresa->numDocRepresentante }}">
    </div>

    {{-- <div class="row">
        <div class="col form-group">
            <label for="fkActividadEconomica">Tipo de actividad económica</label>
            <select name="fkActividadEconomica" id="fkActividadEconomica" class="form-control">
                <option value="">-- Seleccione una opción --</option>
                @foreach ($actEconomicas as $actEc)
                    <option value="{{ $actEc->idactividadEconomica}}"
                    @if ($actEc->idactividadEconomica == old('fkActividadEconomica', $empresa->fkActividadEconomica))
                        selected="selected"
                    @endif
                    >{{ $actEc->nombre }}</option>
                @endforeach
            </select>
        </div>
    </div> --}}

    <div class="form-group">
        <label for="fkTercero_ARL">Tercero ARL</label>
        <select name="fkTercero_ARL" id="fkTercero_ARL" class="form-control">
            <option value="">-- Seleccione una opción --</option>
            @foreach ($terceroArl as $terArl)
                <option value="{{ $terArl->idTercero}}"
                    @if (isset($terArl->idTercero) && $terArl->idTercero == old('ubi', $empresa->fkTercero_ARL))
                        selected="selected"
                    @endif
                    >{{ $terArl->razonSocial }}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label for="pais">País</label>
        <select name="pais" id="pais" class="form-control" >
            <option value="">-- Seleccione una opción --</option>
            @foreach ($paises as $p)
                <option value="{{ $p->idubicacion }}"
                 @if ($p->idubicacion == old('ubi_tres', $empresa->ubi_tres))
                    selected="selected"
                @endif
                >{{ $p->nombre }}</option>
            @endforeach
        </select>
    </div>

    <div class="row">
        <div class="col form-group">
            <label for="deptos">Departamento</label>
            <select name="deptos" id="deptos" class="form-control" >
                <option value="">-- Seleccione una opción --</option>
                @foreach ($deptos as $d)
                    <option value="{{ $d->idubicacion }}"
                     @if ($d->idubicacion == old('ubi_dos', $empresa->ubi_dos))
                        selected="selected"
                    @endif
                    >{{ $d->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col form-group">
            <label for="direccion">Ciudad</label>
            <select name="fkUbicacion" id="fkUbicacion" class="form-control" >
                <option value="">-- Seleccione una opción --</option>
                @foreach ($ciudades as $c)
                    <option value="{{ $c->idubicacion }}"
                     @if ($c->idubicacion == old('ubi', $empresa->ubi))
                        selected="selected"
                    @endif
                    >{{ $c->nombre }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="direccion">Dirección</label>
        <input type="text" class="form-control" id="direccion" name = "direccion" value = "{{ $empresa->direccion }}" >
    </div>

    <div class="form-group">
        <label for="paginaWeb">Página Web</label>
        <input type="text" class="form-control" id="paginaWeb" name = "paginaWeb" value = "{{ $empresa->paginaWeb }}" >
    </div>

    <div class="row">
        <div class="col form-group">
            <label for="telefonoFijo">Teléfono</label>
            <input type="number" class="form-control" id="telefonoFijo" name = "telefonoFijo" value = "{{ $empresa->telefonoFijo }}" >
        </div>
        <div class="col form-group">
            <label for="celular">Celular</label>
            <input type="number" class="form-control" id="celular" name = "celular" value = "{{ $empresa->celular }}" >
        </div>
    </div>

    <div class="row">
        <div class="col form-group">
            <label for="email1">Correo 1</label>
            <input type="email" class="form-control" id="email1" name = "email1" value = "{{ $empresa->email1 }}" >
        </div>
        <div class="col form-group">
            <label for="email2">Correo 2</label>
            <input type="email" class="form-control" id="email2" name = "email2" value = "{{ $empresa->email2 }}" >
        </div>
    </div>

    <div class="row para15Dias" @if(!isset($nominasQuincenlaes)) style="display: none;"  @endif>
        <div class="col form-group">
            <label for="fkPeriocidadRetencion">Periocidad retefuente </label>
            <select name="fkPeriocidadRetencion" id="fkPeriocidadRetencion" class="form-control">
                @foreach ($periocidad as $p)
                    <option value="{{ $p->per_id}}" @if ($empresa->fkPeriocidadRetencion == $p->per_id )
                        selected
                    @endif>{{ $p->per_nombre }}</option>
                @endforeach
            </select>
        </div>
    </div>      
    
    <div class="form-check">
        <input  type="checkbox" class="form-check-input" id="exento"  value="1"
            @if($empresa->exento == 1)
                checked
            @endif
        >
        <label class="form-check-label" for="exento">¿Exento de parafiscales?</label>
    </div><br>
    
    <div class="form-check">
        <input  type="checkbox" class="form-check-input" value="1" id="vacacionesNegativas"
            @if($empresa->vacacionesNegativas == 1)
                checked
            @endif>
        <label class="form-check-label" for="vacacionesNegativas">¿Vacaciones negativas?</label>
    </div><br>

    <div class="form-check">
        <input  type="checkbox" class="form-check-input" value="1" id="LRN_cesantias"
            @if($empresa->LRN_cesantias == 1)
                checked
            @endif>
        <label class="form-check-label" for="LRN_cesantias">¿LRN para cesantias?</label>
    </div><br>


    <div class="form-check">
        <input  type="checkbox" class="form-check-input"  value="1" id="pagoParafiscales"
            @if($empresa->pagoParafiscales == 1)
                checked
            @endif>
        <label class="form-check-label" for="pagoParafiscales">Pago paraficales (sobre el 100% del salario integral)?</label>
    </div><br>
    <div class="form-group">
        <button type="submit" class="btn btn-primary">Modificar Empresa</button>
    </div>
</form>