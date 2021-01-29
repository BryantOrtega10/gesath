<form class="formPerfilEmple" method = "POST">
    @csrf
    <input type="hidden" name="idEmpleado" id = "idEmpleado" value = "{{ $idEmpleado }}">
    <div class="form-group">
        <label for="celular">Celular</label>
        <input type="text" class="form-control" id="celular" name = "celular" value = "{{ $datosEmple->celular }}">
    </div>
    <div class="form-group">
        <label for="telefonoFijo">Teléfono fijo</label>
        <input type="text" class="form-control" id="telefonoFijo" name = "telefonoFijo" value = "{{ $datosEmple->telefonoFijo }}">
    </div>
    <div class="form-group">
        <label for="correo">Correo principal</label>
        <input type="text" class="form-control" id="correo" name = "correo" value = "{{ $datosEmple->correo }}">
    </div>
    <div class="form-group">
        <label for="correo2">Correo alternativo</label>
        <input type="text" class="form-control" id="correo2" name = "correo2" value = "{{ $datosEmple->correo2 }}">
    </div>
    <div class="form-group">
        <label for = "pais">País</label>
        <select name="pais" id="pais" class="form-control">
            <option value="">-- Seleccione una opción --</option>
            @foreach ($paises as $p)
                <option value="{{ $p->idubicacion }}"
                 @if ($p->idubicacion == old('ubi_tres', $datosEmple->ubi_tres))
                    selected="selected"
                @endif
                >{{ $p->nombre }}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label for = "deptos">Departamento</label>
        <select name="deptos" id="deptos" class="form-control">
            <option value="">-- Seleccione una opción --</option>
            @foreach ($deptos as $d)
                <option value="{{ $d->idubicacion }}"
                 @if ($d->idubicacion == old('ubi_dos', $datosEmple->ubi_dos))
                    selected="selected"
                @endif
                >{{ $d->nombre }}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label for = "fkUbicacion">Ciudad</label>
        <select name="fkUbicacion" id="fkUbicacion" class="form-control">
            <option value="">-- Seleccione una opción --</option>
            @foreach ($ciudades as $c)
                <option value="{{ $c->idubicacion }}"
                 @if ($c->idubicacion == old('ubi', $datosEmple->ubi))
                    selected="selected"
                @endif
                >{{ $c->nombre }}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label for="direccion">Dirección</label>
        <input type="text" class="form-control" id="direccion" name = "direccion" value = "{{ $datosEmple->direccion }}">
    </div>
    <div class="form-group">
        <label for="barrio">Barrio</label>
        <input type="text" class="form-control" id="barrio" name = "barrio" value = "{{ $datosEmple->barrio }}">
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-primary">Guardar cambios</button>
    </div>
</form>