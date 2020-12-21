<div class="anchocompleto_flex ubicacion_{{ $idDom }}">
    <label for="fkUbicacion">Ubicación {{ $idDom }}</label>
    <select name="fkUbicacion[]" id="fkUbicacion" class="form-control cont_ub">
        <option value="">-- Seleccione una opción --</option>
        @foreach ($ubicaciones as $ubi)
            <option value="{{ $ubi->idubicacion }}">{{ $ubi->nombre }}</option>
        @endforeach
    </select>
    <button type = "button" class = "btn btn-danger elim_ub" data-id="{{ $idDom }}">
        <i class="fas fa-trash"></i>
    </button>
</div>