<form action="/usuarios/agregarUsuario" class="formGen add_user" method = "POST">

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
    <div class="cont_empresas">
        <a href="/usuarios/addEmpresa" class="btn btn-secondary addEmpresa">Agregar empresa</a>
        <br><br>
        <div class="cont_empresas_add">
        </div>
        <input type="hidden" id="numEmpresa" value="1"  />
    </div>
    <div class="cont_permisos">
        <ul class="permisos_lv1">
        @foreach ($arrMenu as $menu)
            <li>
                <div class="form-check">
                    <input class="form-check-input" checked type="checkbox" name="permiso[]" value="{{$menu->idMenu}}" id="permiso_{{$menu->idMenu}}" /> 
                    <label class="form-check-label" for="permiso_{{$menu->idMenu}}">{{$menu->nombre}}</label>
                </div>
                
                @if (sizeof($menu->subItems) > 0)
                    <ul class="permisos_lv2">
                    @foreach ($menu->subItems as $menulv2)
                    <li>
                        <div class="form-check">
                            <input class="form-check-input" checked type="checkbox"  name="permiso[]" value="{{$menulv2->idMenu}}" id="permiso_{{$menulv2->idMenu}}"  /> 
                            <label class="form-check-label" for="permiso_{{$menulv2->idMenu}}">{{$menulv2->nombre}}</label>
                        </div>
                        @if (sizeof($menulv2->subItems) > 0)
                            <ul class="permisos_lv3">
                            @foreach ($menulv2->subItems as $menulv3)
                                <li>
                                    <div class="form-check">
                                        <input class="form-check-input" checked type="checkbox" name="permiso[]" value="{{$menulv3->idMenu}}" id="permiso_{{$menulv3->idMenu}}" />
                                        <label class="form-check-label" for="permiso_{{$menulv3->idMenu}}">{{$menulv3->nombre}}</label>
                                    </div>
                                </li>
                            @endforeach
                            </ul>
                        @endif
                    </li>
                    @endforeach
                    </ul>
                @endif
            </li>
        @endforeach
        </ul>
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
        <input class = "form-control" type="file" name = "foto" id = "foto">
    </div>    
    <div class="form-group">
        <button type="submit" class="btn btn-primary">Agregar usuario</button>
    </div>
</form>