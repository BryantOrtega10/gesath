<div class="personaV" data-id="{{$num}}">
    <div class="row">
        <div class="col-11">
            Persona con quien vive 
        </div>
        <div class="col-1 text-right">
            <a href="#" class="btn btn-outline-danger quitarPersonaVive" data-id="{{$num}}">Quitar</a>
        </div>
    </div>
    <input type="hidden" name="idNucleoFamiliar[]" value="-1" />
    <div class="row">
        <div class="col-3">
            <div class="form-group">
                <label for="nombrePersonaV{{$num}}" class="control-label">Nombre *</label>
                <input type="text" class="form-control" id="nombrePersonaV{{$num}}" data-id="{{$num}}" name="nombrePersonaV[]" />                    
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label for="fechaNacimientoPersonaV{{$num}}" class="control-label">Fecha Nacimiento *</label>
                <input type="date" class="form-control" id="fechaNacimientoPersonaV{{$num}}" data-id="{{$num}}" name="fechaNacimientoPersonaV[]" />                    
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label for="escolaridadPersonaV{{$num}}" class="control-label">Escolaridad *</label>
                <select class="form-control" id="escolaridadPersonaV{{$num}}" data-id="{{$num}}" name="escolaridadPersonaV[]">
                    <option value=""></option>
                    @foreach($escolaridades as $escolaridad)
                        <option value="{{$escolaridad->idEscolaridad}}">{{$escolaridad->nombre}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-3">
            <div class="form-group">
                <label for="parentescoPersonaV{{$num}}" class="control-label">Parentesco *</label>
                <select class="form-control" id="parentescoPersonaV{{$num}}" data-id="{{$num}}" name="parentescoPersonaV[]">
                    <option value=""></option>
                    @foreach($parentescos as $parentesco)
                        <option value="{{$parentesco->idParentesco}}">{{$parentesco->nombre}}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>