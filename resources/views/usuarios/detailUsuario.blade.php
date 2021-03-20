<form action="" class="formEdit add_user" method = "POST">
    <div class="form-group">
        <label for="username">Nombre de usuario</label>
        <input type="text" class="form-control" id="username" disabled name = "username" value = "{{ $usuario->username }}">
    </div>
    
    <div class="form-group">
        <label for="fkRol">Rol</label>
        <select name="fkRol" class="form-control" disabled id = "fkRol">
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
    <div class="cont_empresas @if ($usuario->fkRol == 2) activo @endif">
        <div class="cont_empresas_add">
            @php
                $numEmpresa = 0;
            @endphp
            @foreach ($empresas_usuario as $empresaUse)
                <div class="row filaEmpresa" data-id="{{$numEmpresa}}">
                    <div class="col">
                        <div class="form-group empresa">
                            <label for="empresa_{{$numEmpresa}}">Empresa</label>
                            <select name="empresa[]" class="form-control" disabled id = "empresa_{{$numEmpresa}}" required>
                                <option value="">-- Seleccione una opción --</option>
                                @foreach ($empresas as $empresa)
                                    <option value="{{$empresa->idempresa}}" @if ($empresa->idempresa == $empresaUse->fkEmpresa)
                                        selected
                                    @endif                                        
                                        >{{$empresa->razonSocial}}</option>
                                @endforeach
                            </select>
                        </div> 
                    </div>
                    
                </div>
                @php
                    $numEmpresa++;
                @endphp
            @endforeach
        </div>
        <input type="hidden" id="numEmpresa" value="{{$numEmpresa}}"  />
    </div>
    <div class="row">
        <div class="col form-group">
            <label for="primerNombre">Nombre</label>
            <input class = "form-control" disabled type = "text" name = "primerNombre" id = "primerNombre" placeholder = "Primer Nombre" required value="{{$usuario->primerNombreUser}}">
        </div>
        <div class="col form-group">
            <label for="primerApellido">Apellido</label>
            <input class = "form-control" disabled type = "text" name = "primerApellido" id = "primerApellido" placeholder = "Primer Apellido" required value="{{$usuario->primerApellidoUser}}">
        </div>
    </div>   

    <div class="form-group">
        <label for="foto">Foto</label>
        <div class="contFotoUser">                        
            <img src="{{ Storage::url("imgEmpleados/".$usuario->fotoUser) }}" class="" id="foto" />            
        </div>
    </div>    
    
</form>