<div class="subTitulo">
    <h2>Otros</h2>
    <hr />
</div>
<form action="/novedades/insertarNovedadOtros" method="POST" class="formGeneral" id="formDatosNovedad" autocomplete="off">
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
                <label for="valor" class="control-label">Valor:</label>
                <input type="text" class="form-control separadorMiles" id="valor" name="valor" />
            </div>
        </div>
        <div class="col-3">
            <div class="form-group hasText">
                <label for="sumaResta" class="control-label">Operacion:</label>
                <select class="form-control" id="sumaResta" name="sumaResta">
                    <option value=""></option>
                    <option value="1" selected>Suma</option>
                    <option value="-1">Resta</option>
                </select>
            </div>
        </div>  
    </div>

    <div class="alert alert-danger print-error-msg-DatosNovedad" style="display:none">
        <ul></ul>
    </div>
    <div class="text-center"><input type="submit" value="AGREGAR" class="btnSubmitGen" /></div>
</form>