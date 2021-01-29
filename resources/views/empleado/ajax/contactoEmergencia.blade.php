<div class="emergencia" data-id="{{$num}}">
    <div class="row">
        <div class="col-11"></div>
        <div class="col-1 text-right">
            <a href="#" class="btn btn-outline-danger quitarContactoEmergencia" data-id="{{$num}}">Quitar</a>
        </div>
    </div>
    <input type="hidden" name="idContactoEmergencia[]" value="-1" />
    <div class="row">
        <div class="col-3">
            <div class="form-group">
                <label for="nombreEmergencia{{$num}}" class="control-label">Nombre *</label>
                <input type="text" class="form-control" id="nombreEmergencia{{$num}}" data-id="{{$num}}" name="nombreEmergencia[]" />                    
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label for="telefonoEmergencia{{$num}}" class="control-label">Telefono *</label>
                <input type="text" class="form-control" id="telefonoEmergencia{{$num}}" data-id="{{$num}}" name="telefonoEmergencia[]" />                    
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label for="direccionEmergencia{{$num}}" class="control-label">Direccion *</label>
                <input type="text" class="form-control" id="direccionEmergencia{{$num}}" data-id="{{$num}}" name="direccionEmergencia[]" />                    
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label for="paisEmergencia{{$num}}" class="control-label">Pais *</label>
                <select class="form-control paisEmergencia" id="paisEmergencia{{$num}}" data-id="{{$num}}" name="paisEmergencia[]">
                    <option value=""></option>
                    @foreach ($paises as $pais)
                        <option value="{{$pais->idubicacion}}">{{$pais->nombre}}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-3">
            <div class="form-group">
                <label for="deptoEmergencia{{$num}}" class="control-label">Departamento *</label>
                <select class="form-control deptoEmergencia" id="deptoEmergencia{{$num}}" data-id="{{$num}}" name="deptoEmergencia[]">
                    <option value=""></option>                    
                </select>
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label for="lugarEmergencia{{$num}}" class="control-label">Lugar *</label>
                <select class="form-control lugarEmergencia" id="lugarEmergencia{{$num}}"  data-id="{{$num}}" name="lugarEmergencia[]">
                    <option value=""></option>                            
                </select>
            </div>
        </div>
    </div>
</div>