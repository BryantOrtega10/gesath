<form class="formActPass" method = "POST">
    @csrf
    <input type="hidden" id = "idEmple" name = "idEmple" value = "{{ $usuario->id }}">
    <div class="form-group">
        <label for="password">Contraseña nueva</label>
        <div class="position-relative">
            <input type="password" class="form-control" id="password" name = "password" required>
            <div class="ojo-password" data-para="password"><i class="fas fa-eye"></i></div>
        </div>
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-primary">Cambiar contraseña</button>
    </div>
</form>