<div class="subTitulo">
    <h2>Horas Extras - Total Horas</h2>
    <hr />
</div>
<form action="/novedades/insertarNovedadHoraTipo2" method="POST" class="formGeneral" id="formDatosNovedad" autocomplete="off">
    @csrf
    <input type="hidden" name="fkTipoNovedad" value="{{$req->tipo_novedad}}" />
    <input type="hidden" name="fkTipoReporte" value="{{$req->tipo_reporte}}" />
    <input type="hidden" name="fkNomina" value="{{$req->nomina}}" />
    <input type="hidden" name="fechaRegistro" value="{{$req->fecha}}" />

    @include('novedades.ajaxAdicional.horas2', [
        'idRow' => $idRow,
        'conceptos' => $conceptos,
        'req' => $req
    ])
    <div class="contAdicional"></div>
    <div class="alert alert-danger print-error-msg-DatosNovedad" style="display:none">
        <ul></ul>
      </div>
    <div class="text-center"><input type="submit" value="AGREGAR" class="btnSubmitGen" /></div>
</form>