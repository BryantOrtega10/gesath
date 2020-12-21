<div class="afiliacion" data-id="{{$num}}">
    <input type="hidden" name="idAfiliacion[]" value="-1" />
    <div class="row">
        <div class="col-12 text-right">
            <a href="#" class="btn btn-outline-danger quitarAfiliacion" data-id="{{$num}}">Quitar</a><br><br>
        </div>
    </div>
    <div class="row">
        <div class="col-3">
            <div class="form-group">
                <label for="afiliacionTipoAfilicacion{{$num}}" class="control-label">Tipo afiliación *</label>
                <select class="form-control afiliacionTipoAfilicacion nuevoRegistro" id="afiliacionTipoAfilicacion{{$num}}" data-id="{{$num}}" name="afiliacionTipoAfilicacion[]">
                    <option value=""></option>
                    @foreach ($tipoafilicaciones as $tipoafilicacion)
                        <option value="{{$tipoafilicacion->idTipoAfiliacion}}">{{$tipoafilicacion->nombre}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label for="afiliacionEntidad{{$num}}" class="control-label">Entidad *</label>
                <select class="form-control" id="afiliacionEntidad{{$num}}" name="afiliacionEntidad[]">
                    <option value=""></option>
                </select>
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label for="afiliacionFecha{{$num}}" class="control-label">Fecha Afiliación *</label>
                <input type="date" class="form-control" id="afiliacionFecha{{$num}}" name="afiliacionFecha[]" />
            </div>
        </div>
    </div>
</div>