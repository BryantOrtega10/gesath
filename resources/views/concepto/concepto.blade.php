@extends('layouts.admin')
@section('title', 'Concepto')
@section('menuLateral')
    @include('layouts.partials.menu')
@endsection

@section('contenido')
<h1>Concepto</h1>
<a class="btn btn-primary" href="#" id="addConcepto">Agregar concepto</a>
<div class="cajaGeneral">
    <form autocomplete="off" action="/concepto/" method="GET" id="filtrarEmpleado">
        @csrf    
        <div class="row">
            <div class="col-4"><input type="text" name="nombre" placeholder="Nombre" @isset($req->nombre) value="{{$req->nombre}}" @endisset/></div>
            <div class="col-4">
                <select class="form-control" name="naturaleza">
                    <option value="">Seleccione uno</option>
                    @foreach($naturalezas as $naturaleza)
                        <option value="{{$naturaleza->idnaturalezaConcepto}}" @isset($req->naturaleza) @if ($req->naturaleza == $naturaleza->idnaturalezaConcepto) selected @endif @endisset>{{$naturaleza->nombre}}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-4"><input type="submit" value="Consultar"/><input type="reset" class="recargar" value="" /> </div>
        </div>        
    </form>
    <br>
        <div class="table-responsive">
            <table class="table table-hover table-striped" id = "conceptos">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Nombre</th>
                        <th scope="col">Naturaleza</th>
                        <th scope="col">Tipo</th>
                        <th scope="col">SubTipo</th>
                        <th scope="col">Condiciones</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($conceptos as $concepto)
                    <tr>
                        <th scope="row">{{ $concepto->idconcepto }}</th>
                        <td>{{ $concepto->nombre }}</td>
                        <td>{{ $concepto->naturaleza }}</td>
                        <td>{{ $concepto->tipoConcepto }}</td>
                        <td>{{ $concepto->subTipo }}</td>
                        <td><a href="/concepto/condiciones/{{ $concepto->idconcepto }}">Condiciones</a></td>
                        <td>
                            <a href="/concepto/getForm/edit/{{ $concepto->idconcepto }}" class="editar"><i class="fas fa-edit"></i></a>
                            <a href="/concepto/getForm/copy/{{ $concepto->idconcepto }}" class="editar"><i class="fas fa-copy"></i></a>
                            
                        
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{-- {{ $conceptos->appends($arrConsulta)->links() }} --}}
</div>
<div class="modal fade" id="conceptoModal" tabindex="-1" role="dialog" aria-labelledby="conceptoModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="cerrarPop" data-dismiss="modal"></div>
                <div class="respForm" data-para='concepto'></div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="formulaConceptoModal" tabindex="-1" role="dialog" aria-labelledby="conceptoModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="cerrarPop" data-dismiss="modal"></div>
                <div class="respForm" data-para='formulaConcepto'></div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="{{ URL::asset('js/concepto.js') }}"></script>
@endsection
