<form class="formActPass" method = "POST">
    @csrf
    <input type="hidden" id = "idEmple" name = "idEmple" value = "{{ $usuario->id }}">
    <div class="form-group">
        <label for="password">Contraseña nueva</label>
        <input type="password" class="form-control" id="password" name = "password" required>
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-primary">Cambiar contraseña</button>
    </div>
</form>