<div class="cajaGeneral">
    <h2>Filtros de B&uacute;squeda</h2>
    <hr>
    <form autocomplete="off" action="/empleado/cargarFormEmpleadosxNomina" method="GET" id="filtrarEmpleado">
        @csrf
        <input type="hidden" name="idNomina" @isset($req->idNomina) value="{{$req->idNomina}}" @endisset/>
        <input type="text" name="nombre" placeholder="Nombre" @isset($req->nombre) value="{{$req->nombre}}" @endisset/>
        <input type="text" name="numDoc" placeholder="N&uacute;mero Documento" @isset($req->numDoc) value="{{$req->numDoc}}" @endisset/>
        <select name="tipoPersona">
            <option value="">Tipo Persona</option>
            <option value="empleado"  @isset($req->numDoc) @if ($req->numDoc == "empleado") selected @endif @endisset>Empleado</option>
            <option value="contratista" @isset($req->numDoc) @if ($req->numDoc == "contratista") selected @endif @endisset>Contratista</option>
            <option value="aspirante"  @isset($req->numDoc) @if ($req->numDoc == "aspirante") selected @endif @endisset>Aspirante</option>
        </select>
        <select name="ciudad">
            <option value="">Ciudad Donde Labora</option>
            @foreach ($ciudades as $ciudad)
                <option value="{{$ciudad->idubicacion}}"  @isset($req->ciudad) @if ($req->ciudad == $ciudad->idubicacion) selected @endif @endisset>{{$ciudad->nombre}}</option>   
            @endforeach
        </select>
        <select name="centroCosto">
            <option value="">Centro de costo</option>
            @foreach ($centrosDeCosto as $centroDeCosto)
                <option value="{{$centroDeCosto->idcentroCosto}}"  @isset($req->centroCosto) @if ($req->centroCosto == $centroDeCosto->idcentroCosto) selected @endif @endisset>{{$centroDeCosto->nombre}}</option>   
            @endforeach
        </select>
        <input type="submit" value="Consultar"/> <input type="reset" class="recargar" value="" style="margin-left: 5px;"/> 
    </form>
</div>
<div class="cajaGeneral">
    <div class="container">
        <div class="row">
            <div class="col-3 font-weight-bold">N&uacute;mero</div>
            <div class="col-3 font-weight-bold">Tipo Documento</div>
            <div class="col-3 font-weight-bold">Nombre</div>
            <div class="col-3 font-weight-bold">Estado</div>
        </div>
        @foreach ($empleados as $empleado)
            <div class="row">
                <div class="col-3 font-weight-bold">{{$empleado->numeroIdentificacion}}</div>
                <div class="col-3 font-weight-bold">{{$empleado->nombre}}</div>
                
                <div class="col-3 text-left">
                    <a href="#" class="seleccionarEmpleado" data-id="{{$empleado->idempleado}}">
                    {{$empleado->primerNombre." ".$empleado->segundoNombre." ".$empleado->primerApellido." ".$empleado->segundoApellido}}
                    </a>
                </div>
                <div class="col-3 font-weight-bold">{{$empleado->estado}}</div>
            </div>
        @endforeach
    </div><br>
    {{ $empleados->appends($arrConsulta)->links() }}
</div>
