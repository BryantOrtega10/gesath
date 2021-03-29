@extends('layouts.admin')
@section('title', 'Carga datos pasados vacaciones')
@section('menuLateral')
    @include('layouts.partials.menu', [
        'dataUsu' => $dataUsu
    ])
@endsection

@section('contenido')
@if (in_array("90",$dataUsu->permisosUsuario))
<form method="POST" id="" autocomplete="off" class="formGeneral" action="/datosPasadosVac/subirArchivo"
                    enctype="multipart/form-data">
                    @csrf
    <div class="row">
        <div class="col-8">
            <h1 class="granAzul">Carga datos pasados VAC/LRN</h1>
        </div>
        <div class="col-2">
            <div class="seleccionarArchivo gris">
                <label for="archivoCSV">Seleccione un archivo CSV</label>
                <input type="file" name="archivoCSV" id="archivoCSV" required  accept=".csv"/>
            </div>
        </div>
        <div class="col-2">
            <div class="text-center"><input type="submit" value="Cargar csv" class="btnSubmitGen btnAzulGen" /></div>
        </div>
    </div>
</form>
@endif
    <div class="cajaGeneral">
        @if (in_array("89",$dataUsu->permisosUsuario))
        <form method="POST" id="formAdd" autocomplete="off" class="formGeneral"
            action="/datosPasadosVac/insertarManualmente" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-3">
                    <div class="form-group">
                        <label for="infoEmpresa" class="control-label">Empresa</label>
                        <select class="form-control" id="infoEmpresa" name="empresa" required>
                            <option value=""></option>
                            @foreach ($empresas as $empresa)
                                <option value="{{ $empresa->idempresa }}">{{ $empresa->razonSocial }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label for="infoNomina" class="control-label">N&oacute;mina:</label>
                        <select class="form-control" id="infoNomina" name="infoNomina" required>
                            <option value=""></option>
                        </select>
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group busquedaPop" id="busquedaEmpleado">
                        <label for="nombreEmpleado" class="control-label">Empleado:</label>
                        <input type="text" readonly class="form-control" id="nombreEmpleado" name="nombreEmpleado"
                            required />
                        <input type="hidden" class="form-control" id="idEmpleado" name="idEmpleado" required />
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label for="tipo" class="control-label">Tipo:</label>
                        <select class="form-control" id="tipo" name="tipo" required>
                            <option value=""></option>
                            <option value="VAC">VAC</option>
                            <option value="LNR">LNR</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-3">
                    <div class="form-group">
                        <label for="fecha" class="control-label">Fecha:</label>
                        <input type="date" class="form-control" id="fecha" name="fecha"  required/>
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label for="dias" class="control-label">Días:</label>
                        <input type="number" class="form-control" required id="dias" name="dias" />
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label for="fechaInicio" class="control-label">Fecha Inicio:</label>
                        <input type="date" class="form-control" id="fechaInicio" name="fechaInicio" />
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label for="fechaFin" class="control-label">Fecha Fin:</label>
                        <input type="date" class="form-control" id="fechaFin" name="fechaFin" />
                    </div>
                </div>
                
            </div>
            <div class="row">
                <div class="col-3">
                    <div class="text-center"><input type="submit" value="Cargar manualmente" class="btnSubmitGen" /></div>
                </div>
            </div>
        </form>
        @endif
    </div>
    <div class="cajaGeneral">
        <div class="row">
            
            <div class="col-12">
                <table class="table table-hover table-striped ">
                    <tr>
                        <th># Carga</th>
                        <th>Tipo</th>
                        <th>Fecha Carga</th>
                        <th>Porcentaje</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                    @foreach ($cargasDatosPasados as $cargaDatoPasado)
                        <tr>
                            <td>{{ $cargaDatoPasado->idCargaDatosPasados }}</td>
                            <td>{{ $cargaDatoPasado->tipo }}</td>
                            <td>{{ $cargaDatoPasado->fechaCarga }}</td>
                            <td>{{ ceil(($cargaDatoPasado->numActual / $cargaDatoPasado->numRegistros) * 100) }}%</td>
                            <td>{{ $cargaDatoPasado->nombre }}</td>
                            <td>@if (in_array("91",$dataUsu->permisosUsuario))
                                <a href="/datosPasadosVac/verCarga/{{ $cargaDatoPasado->idCargaDatosPasados }}">Ver carga</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
    <div class="modal fade" id="busquedaEmpleadoModal" tabindex="-1" role="dialog" aria-labelledby="empleadoModal" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="cerrarPop" data-dismiss="modal"></div>
                    <div class="resFormBusEmpleado"></div>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="{{ URL::asset('js/datosPasadosVac/datosPasadosVac.js') }}"></script>
@endsection
