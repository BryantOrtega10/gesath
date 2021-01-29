<div class="subTitulo">
    <h2>Licencia</h2>
    <hr />
</div>
<form action="/novedades/insertarNovedadLicencia" method="POST" class="formGeneral" id="formDatosNovedad" autocomplete="off">
    @csrf
    <input type="hidden" name="fkTipoNovedad" value="{{$req->tipo_novedad}}" />
    <input type="hidden" name="fkNomina" value="{{$req->nomina}}" />
    <input type="hidden" name="fechaRegistro" value="{{$req->fecha}}" />
    <div class="row">
        <div class="col-3">
            <div class="form-group busquedaPop" id="busquedaEmpleado">
                <label for="nombreEmpleado" class="control-label">Empleado:</label>
                <input type="text" readonly class="form-control" id="nombreEmpleado" name="nombreEmpleado" />
                <input type="hidden" class="form-control" id="idEmpleado" name="idEmpleado" />
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label for="concepto" class="control-label">Concepto:</label>
                <select class="form-control" id="concepto" name="concepto">
                    <option value=""></option>
                    @foreach ($conceptos as $concepto)
                        <option value="{{$concepto->idconcepto}}">{{$concepto->nombre}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label for="fechaInicial" class="control-label">Fecha Inicial:</label>
                <input type="date" class="form-control" id="fechaInicial" name="fechaInicial" min="{{$req->fechaMinima}}" />
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label for="dias" class="control-label">Dias:</label>
                <input type="text" class="form-control" id="dias" name="dias" />
            </div>
        </div>
    </div>
    <div class="row">
        
        <div class="col-3">
            <div class="form-group">
                <label for="fechaFinal" class="control-label">Fecha Final:</label>
                <input type="date" class="form-control" id="fechaFinal" name="fechaFinal" readonly min="{{$req->fechaMinima}}" />
            </div>
        </div>
    </div>
    <div class="alert alert-danger print-error-msg-DatosNovedad" style="display:none">
        <ul></ul>
    </div>
    <div class="text-center"><input type="submit" value="AGREGAR" class="btnSubmitGen" /></div>
</form>