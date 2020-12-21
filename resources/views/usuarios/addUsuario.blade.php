<form action="/usuarios/agregarUsuario" class="formGen" method = "POST">
    <div class="form-group">
        <label for="email">Correo electrónico</label>
        <input type="email" class="form-control" id="email" name = "email">
    </div>
    <div class="form-group">
        <label for="username">Nombre de usuario</label>
        <input type="text" class="form-control" id="username" name = "username">
    </div>
    <div class="form-group">
        <label for="password">Contraseña</label>
        <input type="password" class="form-control" id="password" name = "password">
    </div>
    <div class="form-group">
        <label for="fkRol">Rol</label>
        <select name="fkRol" class="form-control" id = "fkRol">
            <option value="">-- Seleccione una opción --</option>
            <option value="2">Administrador</option>
            <option value="3">Superadministrador</option>
        </select>
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-primary">Agregar usuario</button>
    </div>
</form>