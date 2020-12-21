<div class="cajaGeneral">
    <h2>Filtros de B&uacute;squeda</h2>
    <hr>
    <form autocomplete="off" action="/varios/codigosDiagnostico" method="GET" id="filtrarCodigo">
        @csrf
        <input type="text" name="codigo" placeholder="C&oacute;digo:" @isset($req->codigo) value="{{$req->codigo}}" @endisset/><br>
        <input type="text" name="nombre" placeholder="Nombre:"  @isset($req->nombre) value="{{$req->nombre}}" @endisset/>
        <input type="submit" value="Consultar"/><input type="reset" class="recargar" value="" /> 
    </form>
</div>
<div class="cajaGeneral">
    <div class="container">
        <div class="row">
            <div class="col-4 font-weight-bold">C&oacute;digo</div>
            <div class="col-8 font-weight-bold">Nombre</div>
            
        </div>
        @foreach ($codigosDiagnostico as $codigoDiagnostico)
            <div class="row">
                <div class="col-4 text-left">
                    <a href="#" class="seleccionarCodigo" data-id="{{$codigoDiagnostico->idCodDiagnostico}}">
                    {{$codigoDiagnostico->idCodDiagnostico}}
                    </a>
                </div>
                <div class="col-8 text-left">{{$codigoDiagnostico->nombre}}</div>
            </div>
        @endforeach
    </div>
    <br>
    {{ $codigosDiagnostico->appends($arrConsulta)->links() }}
</div>
