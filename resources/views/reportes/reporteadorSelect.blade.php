<div class="row">
    <div class="col-4">
        <select class="selectMultiple" multiple id="opcionesDisponibles" name="opcionesDisponibles[]">
            @foreach ($items_tipo_reporte as $item_tipo_reporte)
                <option value="{{$item_tipo_reporte->IdItemTipoReporte}}">{{$item_tipo_reporte->nombre}}</option>
            @endforeach
        </select>
    </div>
    <div class="col-2">
        <button type="button" class="btn btn-primary" id="pasarTodos">Pasar todos</button>
        <button type="button" class="btn btn-primary" id="pasarUno" disabled>Pasar Sel.</button>
        <button type="button" class="btn btn-primary" id="quitarUno" disabled>Quitar Sel.</button>
        <button type="button" class="btn btn-primary" id="quitarTodos" disabled>Quitar todos</button>
    </div>
    <div class="col-4">
        <select class="selectMultiple" required multiple id="opcionesSeleccionadas" name="opcionesSeleccionadas[]">
            @isset($items_tipo_reporte_select)
                @foreach ($items_tipo_reporte_select as $item_tipo_reporte_select)
                    <option value="{{$item_tipo_reporte_select->IdItemTipoReporte}}">{{$item_tipo_reporte_select->nombre}}</option>
                @endforeach
            @endisset           
        </select>
    </div>
    <div class="col-2">
        <button type="button" class="btn btn-primary" id="primero" disabled>Primero</button>
        <button type="button" class="btn btn-primary " id="subir" disabled>Subir</button>
        <button type="button" class="btn btn-primary" id="bajar" disabled>Bajar</button>
        <button type="button" class="btn btn-primary" id="ultimo" disabled>Ultimo</button>
    </div>
</div>