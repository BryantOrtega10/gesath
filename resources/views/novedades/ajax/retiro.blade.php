<div class="subTitulo">
    <h2>Retiro</h2>
    <hr />
</div>
<form action="/novedades/insertarNovedadRetiro" method="POST" class="formGeneral" id="formDatosNovedad" autocomplete="off">
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
                <label for="fechaRetiro" class="control-label">Fecha Retiro:</label>
                <input type="date" class="form-control" id="fechaRetiro" name="fechaRetiro"  />
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label for="fechaRetiroReal" class="control-label">Fecha Real:</label>
                <input type="date" class="form-control" id="fechaRetiroReal" name="fechaRetiroReal"  />
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label for="motivoRetiro" class="control-label">Motivo Retiro:</label>
                <select class="form-control" id="motivoRetiro" name="motivoRetiro">
                    <option value=""></option>
                    @foreach ($motivosRetiro as $motivoRetiro)
                        <option value="{{$motivoRetiro->idMotivoRetiro}}">{{$motivoRetiro->nombre}}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-3">
            <div class="form-group">
                <label for="indemnizacion" class="control-label">Indemnizacion:</label>
                <select class="form-control" id="indemnizacion" name="indemnizacion">
                    <option value=""></option>
                    <option value="1">SI</option>
                    <option value="0">NO</option>                   
                </select>
            </div>
        </div>
    </div>
    <div class="alert alert-danger print-error-msg-DatosNovedad" style="display:none">
        <ul></ul>
    </div>
    <div class="text-center"><input type="submit" value="AGREGAR" class="btnSubmitGen" /></div>
</form>