<form action="/usuarios/agregarUsuario" class="formGen" method = "POST">
    <div class="form-group">
        <label for="email">Correo electrónico</label>
        <input type="email" class="form-control" id="email" name = "email" required>
    </div>
    <div class="form-group">
        <label for="username">Nombre de usuario</label>
        <input type="text" class="form-control" id="username" name = "username" required>
    </div>
    <div class="form-group">
        <label for="password">Contraseña</label>
        <input type="password" class="form-control" id="password" name = "password" required>
    </div>
    <div class="form-group">
        <label for="fkRol">Rol</label>
        <select name="fkRol" class="form-control" id = "fkRol" required>
            <option value="">-- Seleccione una opción --</option>
            <option value="2">Administrador</option>
            <option value="3">Superadministrador</option>
        </select>
    </div>
    <div class="form-group">
        <label for="fkEmpresa">Empresa pertenece</label>
        <select name="fkEmpresa" class="form-control" id = "fkEmpresa" required>
            <option value="">-- Seleccione una opción --</option>
            @foreach($empresas as $e)
                <option value="{{ $e->idempresa }}">{{ $e->razonSocial }}</option>
            @endforeach
        </select>
    </div>
    <div class="row">
        <div class="col form-group">
            <label for="primerNombre">Nombre</label>
            <input class = "form-control" type = "text" name = "primerNombre" id = "primerNombre" placeholder = "Primer Nombre" required>
        </div>
        <div class="col form-group">
            <label for="primerApellido">Apellido</label>
            <input class = "form-control" type = "text" name = "primerApellido" id = "primerApellido" placeholder = "Primer Apellido" required>
        </div>
    </div>
    
    <div class="form-group">
        <label for="foto">Foto</label>
        <input class = "form-control" type = "file" name = "foto" id = "foto">
    </div>
    
    <div class="form-group">
        <button type="submit" class="btn btn-primary">Agregar usuario</button>
    </div>
</form>