<form>
    <div class="form-group">
        <label for="naturalezaTributaria">Naturaleza Tributaria</label>
        <select disabled name="naturalezaTributaria" id="naturalezaTributaria" class="form-control">
            <option value="">-- Seleccione una opción --</option>
            <option value="Juridico"
            @if ($tercero->naturalezaTributaria == 'Juridico')
                selected="selected"
            @endif
            >Jurídica</option>
            <option value="Natural"
            @if ($tercero->naturalezaTributaria == 'Natural')
                selected="selected"
            @endif
            >Natural</option>
        </select>
    </div>
    <div class="contenedor_nombres" style = "width: 100%;">
        @if($tercero->naturalezaTributaria == 'Juridico')
            <div class="form-group">
                <label for="razonSocial">Razón social</label>
                <input disabled type="text" class="form-control" id="razonSocial" name = "razonSocial" value = "{{ $tercero->razonSocial }}">
            </div>
        @else
            <div class="row">
                <div class="col form-group">
                    <label for="primerNombre">Primer Nombre</label>
                    <input disabled type="text" class="form-control" id="primerNombre" name = "primerNombre" value = "{{ $tercero->primerNombre }}">
                </div>
                <div class="col form-group">
                    <label for="segundoNombre">Segundo Nombre</label>
                    <input disabled type="text" class="form-control" id="segundoNombre" name = "segundoNombre" value = "{{ $tercero->segundoNombre }}">
                </div>
            </div>
            <div class="row">
                <div class="col form-group">
                    <label for="primerApellido">Primer Apellido</label>
                    <input disabled type="text" class="form-control" id="primerApellido" name = "primerApellido" value = "{{ $tercero->primerApellido }}">
                </div>
                <div class="col form-group">
                    <label for="segundoApellido">Segundo Apellido</label>
                    <input disabled type="text" class="form-control" id="segundoApellido" name = "segundoApellido" value = "{{ $tercero->segundoApellido }}">
                </div>
            </div>
        @endif
    </div>
    <div class="form-group">
        <label for="fk_actividad_economica">Actividad Económica</label>
        <select disabled name="fk_actividad_economica" id="fk_actividad_economica" class="form-control">
            <option value="">-- Seleccione una opción --</option>
            @foreach ($actEconomicas as $econo)
                <option value="{{ $econo->idactividadEconomica}}"
                @if ($econo->idactividadEconomica == old('fk_actividad_economica', $tercero->fk_actividad_economica))
                    selected="selected"
                @endif
                >{{ $econo->nombre }}</option>
            @endforeach
        </select>
    </div>
    <div class="row">
        <div class="col form-group">
            <label for="fkTipoIdentificacion">Tipo de identificación</label>
            <select disabled name="fkTipoIdentificacion" id="fkTipoIdentificacion" class="form-control">
                <option value="">-- Seleccione una opción --</option>
                @foreach ($tipoIdent as $tipo)
                    <option value="{{ $tipo->idtipoIdentificacion}}"
                    @if ($tipo->idtipoIdentificacion == old('fkTipoIdentificacion', $tercero->fkTipoIdentificacion))
                        selected="selected"
                    @endif
                    >{{ $tipo->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col form-group">
            <label for="numeroIdentificacion">Número de identificación</label>
            <input disabled type="number" class="form-control" id="numeroIdentificacion" name = "numeroIdentificacion" value = "{{ $tercero->numeroIdentificacion}}">
        </div>
    </div>
    <div class="row">
        <div class="col form-group">
            <div class = "ubicaciones">
                {!! $DOMUbis !!}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col form-group">
            <label for="direccion">Dirección</label>
            <input type="text" class="form-control" id="direccion" name = "direccion" value = "{{ $tercero->direccion}}" disabled>
        </div>
    </div>
    <div class="row">
        <div class="col form-group">
            <label for="telefono">Teléfono</label>
            <input disabled type="number" class="form-control" id="telefono" name = "telefono" value = "{{ $tercero->telefono}}">
        </div>
        <div class="col form-group">
            <label for="fax">Fax</label>
            <input disabled type="number" class="form-control" id="fax" name = "fax" value = "{{ $tercero->fax }}">
        </div>
    </div>
    <div class="form-group">
        <label for="correo">Correo</label>
        <input disabled type="email" class="form-control" id="correo" name = "correo" value = "{{ $tercero->correo }}">
    </div>
    <div class="row">
        <div class="col form-group">
            <label for="codigoTercero">Código Tercero</label>
            <input disabled type="text" class="form-control" id="codigoTercero" name = "codigoTercero" value = "{{ $tercero->codigoTercero }}">
        </div>
        <div class="col form-group">
            <label for="codigoSuperIntendencia">Código Superintendencia</label>
            <input disabled type="text" class="form-control" id="codigoSuperIntendencia" name = "codigoSuperIntendencia" value = "{{ $tercero->codigoSuperIntendencia }}">
        </div>
    </div>
    <div class="row">
        <div class="col form-group">
            <label for="fkTipoAporteSeguridadSocial">Aporte Seguridad Social</label>
            <select disabled name="fkTipoAporteSeguridadSocial" id="fkTipoAporteSeguridadSocial" class="form-control">
                <option value="">-- Seleccione una opción --</option>
                @foreach ($tipoAfl as $afl)
                    <option value="{{ $afl->idTipoAporteSeguridadSocial}}"
                    @if ($afl->idTipoAporteSeguridadSocial == old('fkTipoAporteSeguridadSocial', $tercero->fkTipoAporteSeguridadSocial))
                        selected="selected"
                    @endif
                    >{{ $afl->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col form-group">
            <label for="fkEstado">Estado</label>
            <select disabled name="fkEstado" id="fkEstado" class="form-control">
                <option value="">-- Seleccione una opción --</option>
                @foreach ($estados as $estado)
                    <option value="{{ $estado->idestado}}"
                    @if ($estado->idestado == old('fkEstado', $tercero->fkEstado))
                        selected="selected"
                    @endif
                    >{{ $estado->nombre }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="form-check form-group">
        <input disabled class="form-check-input check-privado" type="checkbox" id="privado"
        @if ($tercero->privado == 1)
            checked="checked"
        @endif
        >
        <label class="form-check-label" for="privado">
            ¿Privado?
        </label>
    </div>
</form>