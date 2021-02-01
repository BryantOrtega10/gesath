<form action="/terceros/agregarTercero" class="formGen" method = "POST">
    <div class="form-group">
        <label for="naturalezaTributaria">Naturaleza Tributaria</label>
        <select name="naturalezaTributaria" id="naturalezaTributaria" class="form-control">
            <option value="">-- Seleccione una opción --</option>
            <option value="Juridico">Jurídica</option>
            <option value="Natural">Natural</option>
        </select>
    </div>
    <div class="contenedor_nombres" style = "width: 100%;"> </div>
    <div class="form-group">
        <label for="fk_actividad_economica">Actividad Económica</label>
        <select name="fk_actividad_economica" id="fk_actividad_economica" class="form-control">
            <option value="">-- Seleccione una opción --</option>
            @foreach ($actEconomicas as $econo)
                <option value="{{ $econo->idactividadEconomica}}">{{ $econo->nombre }}</option>
            @endforeach
        </select>
    </div>
    <div class="row">
        <div class="col form-group">
            <label for="fkTipoIdentificacion">Tipo de identificación</label>
            <select name="fkTipoIdentificacion" id="fkTipoIdentificacion" class="form-control">
                <option value="">-- Seleccione una opción --</option>
                @foreach ($tipoIdent as $tipo)
                    <option value="{{ $tipo->idtipoIdentificacion}}">{{ $tipo->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col form-group">
            <label for="numeroIdentificacion">Número de identificación</label>
            <input type="number" class="form-control" id="numeroIdentificacion" name = "numeroIdentificacion">
        </div>
    </div>
    <div class="row">
        <div class="col form-group">
            <div class = "ubicaciones">
                <div class="ubicacion_1">
                    <label for="fkUbicacion">Ubicación 1</label>
                    <select name="fkUbicacion[]" class="form-control">
                        <option value="">-- Seleccione una opción --</option>
                        @foreach ($ubicaciones as $ubi)
                            <option value="{{ $ubi->idubicacion }}"
                            >{{ $ubi->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <br>
            <button type = "button" class="adicional_ubis btn btn-success" dataId = "1">Agregar ubicación</button>
        </div>
    </div>
    <div class="row">
        <div class="col form-group">
            <label for="direccion">Dirección</label>
            <input type="text" class="form-control" id="direccion" name = "direccion">
        </div>
    </div>
    <div class="row">
        <div class="col form-group">
            <label for="telefono">Teléfono</label>
            <input type="number" class="form-control" id="telefono" name = "telefono">
        </div>
        <div class="col form-group">
            <label for="fax">Fax</label>
            <input type="number" class="form-control" id="fax" name = "fax">
        </div>
    </div>
    <div class="form-group">
        <label for="correo">Correo</label>
        <input type="email" class="form-control" id="correo" name = "correo">
    </div>
    <div class="row">
        <div class="col form-group">
            <label for="codigoTercero">Código Tercero</label>
            <input type="text" class="form-control" id="codigoTercero" name = "codigoTercero">
        </div>
        <div class="col form-group">
            <label for="codigoSuperIntendencia">Código Superintendencia</label>
            <input type="text" class="form-control" id="codigoSuperIntendencia" name = "codigoSuperIntendencia">
        </div>
    </div>
    <div class="row">
        <div class="col form-group">
            <label for="fkTipoAporteSeguridadSocial">Aporte Seguridad Social</label>
            <select name="fkTipoAporteSeguridadSocial" id="fkTipoAporteSeguridadSocial" class="form-control">
                <option value="">-- Seleccione una opción --</option>
                @foreach ($tipoAfl as $afl)
                    <option value="{{ $afl->idTipoAporteSeguridadSocial}}">{{ $afl->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col form-group">
            <label for="fkEstado">Estado</label>
            <select name="fkEstado" id="fkEstado" class="form-control">
                <option value="">-- Seleccione una opción --</option>
                @foreach ($estados as $estado)
                    <option value="{{ $estado->idestado}}">{{ $estado->nombre }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="form-check form-group">
        <input class="form-check-input check-privado" type="checkbox" id="privado">
        <label class="form-check-label" for="privado">
            ¿Privado?
        </label>
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-primary">Agregar tercero</button>
    </div>
</form>
<script>
    $("#naturalezaTributaria").change((e) => {
        const valor = e.target.value;
        let htmlAppend = '';
        switch (valor) {
            case 'Juridico':
                htmlAppend += '<div class="form-group">';
                htmlAppend += '<label for="razonSocial">Razón social</label>'
                htmlAppend += '<input type="text" class="form-control" id="razonSocial" name = "razonSocial">'
                htmlAppend += '</div>';
                break;
            case 'Natural':
                htmlAppend += '<div class="row">';
                htmlAppend += '<div class="col form-group">';
                htmlAppend += '<label for="primerNombre">Primer Nombre</label>';
                htmlAppend += '<input type="text" class="form-control" id="primerNombre" name = "primerNombre"></input>';
                htmlAppend += '</div>';
                htmlAppend += '<div class="col form-group">';
                htmlAppend += '<label for="segundoNombre">Segundo Nombre</label>';
                htmlAppend += '<input type="text" class="form-control" id="segundoNombre" name = "segundoNombre">';
                htmlAppend += '</div>';
                htmlAppend += '</div>';

                htmlAppend += '<div class="row">';
                htmlAppend += '<div class="col form-group">';
                htmlAppend += '<label for="primerApellido">Primer Apellido</label>';
                htmlAppend += '<input type="text" class="form-control" id="primerApellido" name = "primerApellido"></input>';
                htmlAppend += '</div>';
                htmlAppend += '<div class="col form-group">';
                htmlAppend += '<label for="segundoApellido">Segundo Apellido</label>';
                htmlAppend += '<input type="text" class="form-control" id="segundoApellido" name = "segundoApellido">';
                htmlAppend += '</div>';
                htmlAppend += '</div>';
                break;
            default:
                htmlAppend += '';
                break;
        }
        $(".contenedor_nombres").empty();
        $(".contenedor_nombres").append(htmlAppend);
    });
</script>