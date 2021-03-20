<div class="row filaEmpresa" data-id="{{$numEmpresa}}">
    <div class="col">
        <div class="form-group empresa">
            <label for="empresa_{{$numEmpresa}}">Empresa</label>
            <select name="empresa[]" class="form-control" id = "empresa_{{$numEmpresa}}" required>
                <option value="">-- Seleccione una opción --</option>
                @foreach ($empresas as $empresa)
                    <option value="{{$empresa->idempresa}}" >{{$empresa->razonSocial}}</option>
                @endforeach
            </select>
        </div> 
    </div>
    <div class="col-1 align-bottom"><br>
        <a href="#" class="btn btn-danger quitarEmpresa" data-id="{{$numEmpresa}}"><i class="fas fa-trash"></i></a>
    </div>
</div>