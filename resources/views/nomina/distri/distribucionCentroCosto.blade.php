@extends('layouts.admin')
@section('title', 'Distibucion centro de costos')
@section('menuLateral')
    @include('layouts.partials.menu', [
        'dataUsu' => $dataUsu
    ])
@endsection

@section('contenido')
<div class="row">
    <div class="col-9">
        <h1 class="granAzul">Distibucion centro de costos</h1>
    </div>
    <div class="col-3 text-right">
        @if (in_array("84",$dataUsu->permisosUsuario))
            <a class="btnGeneral btnAzulGen btnGra text-center" href="#" id="addDistri">Agregar nueva distribucion</a>
        @endif
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="cajaGeneral">
            
            <table class="table table-hover table-striped">
                <tr>
                    <th>&num;</th>
                    <th>Fecha Inicio</th>
                    <th>Fecha Fin</th>
                    <th>Nomina</th>
                    <th></th>
                </tr>
                @foreach ($distris_centro_costo as $distri_centro_costo)
                    <tr>
                        <th>
                            {{$distri_centro_costo->id_distri_centro_costo }}
                        </th>
                        <td>
                            {{$distri_centro_costo->fechaInicio }}
                        </td>
                        <td>
                            {{$distri_centro_costo->fechaFin }}
                        </td>
                        <td>
                            {{$distri_centro_costo->nombre }}
                        </td>
                        <td>
                            <div class="btn-group">
                                <i class="fas fa-ellipsis-v dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></i>
                                <div class="dropdown-menu dropdown-menu-right">
                                    @if (in_array("86",$dataUsu->permisosUsuario))
                                        <a href="/nomina/distri/modificarDistri/{{$distri_centro_costo->id_distri_centro_costo}}" class="dropdown-item"><i class="fas fa-edit"></i> Modificar</a>
                                    @endif
                                    @if (in_array("88",$dataUsu->permisosUsuario))
                                        <a href="/nomina/distri/copiarDistri/{{$distri_centro_costo->id_distri_centro_costo}}" class=" dropdown-item copiarDistri"><i class="fas fa-copy"></i> Copiar</a>
                                    @endif

                                </div>
                            </div>
                            
                        </td>
                    </tr>
                @endforeach
            </table>
            {{ $distris_centro_costo->links() }}
        </div>
        
    </div>
</div>
<div class="modal fade" id="distriModal" tabindex="-1" role="dialog" aria-labelledby="distriModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="cerrarPop" data-dismiss="modal"></div>
                <div class="respForm"></div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="{{ URL::asset('js/distri.js')}}"></script>
@endsection