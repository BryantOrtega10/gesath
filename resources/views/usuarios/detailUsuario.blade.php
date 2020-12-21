<div class="form-group">
    <label for="email">Correo electrónico</label>
    <input type="email" class="form-control" id="email" name = "email" value = "{{ $usuario->email }}" disabled>
</div>
<div class="form-group">
    <label for="username">Nombre de usuario</label>
    <input type="text" class="form-control" id="username" name = "username" value = "{{ $usuario->username }}" disabled>
</div>
<div class="form-group">
    <label for="fkRol">Rol</label>
    <select name="fkRol" class="form-control" id = "fkRol" disabled>
        <option value="">-- Seleccione una opción --</option>
        <option value="2"
            @if ($usuario->fkRol == 2)
                selected="selected"
            @endif
        >Administrador</option>
        <option value="3"
            @if ($usuario->fkRol == 3)
                selected="selected"
            @endif
        >Superadministrador</option>
    </select>
</div>