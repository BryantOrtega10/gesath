@extends('layouts.admin')
@section('title', 'Usuarios')
@section('menuLateral')
    @include('layouts.partials.menu', [
        'dataUsu' => $dataUsu
    ])
@endsection

@section('contenido')
<h1>Usurarios</h1>
<a class="btn btn-primary" href="#" id="addUsuario">Agregar usuario</a>
<div class="cajaGeneral">
    <div class="table-responsive">
        <table id = "usuarios" class="table table-hover table-striped">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Correo</th>
                    <th scope="col">Usuario</th>
                    <th scope="col">Rol</th>
                    <th scope="col">Estado</th>
                    <th scope="col"></th>
                </tr>
            </thead>
            <tbody>
                @if (sizeof($usuarios) > 0)
                    @foreach ($usuarios as $usuario)
                    <tr>
                        <th scope="row">{{ $usuario->id }}</th>
                        <td>{{ $usuario->email }}</td>
                        <td>{{ $usuario->username }}</td>
                        <td>{{ $usuario->nombre }}</td>
                        @if ( $usuario->estado == 1 )
                            <td>Activo</td>
                        @elseif ( $usuario->estado == 0 )
                            <td>Inactivo</td>
                        @endif
                        <td>
                            <div class="dropdown">
                                <i class="fas fa-ellipsis-v dropdown-toggle" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false" id="dropdownMenuButton"></i>
                                <div class="dropdown-menu"  aria-labelledby="dropdownMenuButton">
                                    <a dataId ="{{ $usuario->id }}" class="dropdown-item detalle"><i class="far fa-eye"></i> Ver Usuario</a>
                                    <a dataId ="{{ $usuario->id }}" class="dropdown-item editar"><i class="fas fa-edit"></i> Editar Usuario</a>
                                    @if($usuario->estado == 1)
                                        <a dataId ="{{ $usuario->id }}" class="dropdown-item hab_deshab" dataActivo = "1"><i class="far fa-eye-slash"></i> Desactivar Usuario</a>
                                    @elseif($usuario->estado == 0)
                                        <a dataId ="{{ $usuario->id }}" class="dropdown-item hab_deshab" dataActivo = "0"><i class="far fa-eye"></i> Activar Usuario</a>
                                    @endif
                                    <a dataId ="{{ $usuario->id }}" class="dropdown-item cambiar_pass"><i class="fas fa-unlock"></i> Cambiar contraseña</a>
                                    <a dataId ="{{ $usuario->id }}" class="dropdown-item color_rojo eliminar"><i class="fas fa-trash"></i> Eliminar Usuario</a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                @elseif (sizeof($usuarios) == 0)
                    <td colspan = "6"> No se han creado usuarios</td>
                @endif            
            </tbody>
        </table>
    </div>
</div>
<div class="modal fade" id="usuariosModal" tabindex="-1" role="dialog" aria-labelledby="usuariosModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="cerrarPop" data-dismiss="modal"></div>
                <div class="respForm" data-para='usuarios'></div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="{{ URL::asset('js/usuarios.js') }}"></script>
@endsection
