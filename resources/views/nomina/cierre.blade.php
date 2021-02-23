@extends('layouts.admin')
@section('title', 'Cierre de periodo')
@section('menuLateral')
    @include('layouts.partials.menu')
@endsection

@section('contenido')
<div class="row">
    <div class="col-12">
        <h1 class="granAzul">Cierre de periodo</h1>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="cajaGeneral">
            <form method="POST" id="formCierre" autocomplete="off" class="formGeneral" action="/nomina/generarCierre">
                @csrf
                <div class="row">
                    <div class="col-3">
                        <div class="form-group">
                            <label for="infoEmpresa" class="control-label">Empresa</label>
                            <select class="form-control" id="infoEmpresa" name="empresa">
                                <option value=""></option>        
                                @foreach ($empresas as $empresa)
                                    <option value="{{$empresa->idempresa}}">{{$empresa->razonSocial}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>                       
                    <div class="col-3">
                        <div class="form-group">
                            <label for="fechaCierre" class="control-label">Fecha Cierre</label>
                            <input type="date" class="form-control" id="fechaCierre" name="fechaCierre" />
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="text-center"><input type="submit" value="Cerrar periodo" class="btnSubmitGen" /></div>
                    </div>
                </div>                
            </form>
        </div>
    </div>
</div>

@endsection