@foreach ($ubisTer as $key => $ub)
<div class="anchocompleto_flex ubicacion_{{ $key + 1 }}">
    <label for="fkUbicacion">Ubicación {{ $key + 1 }}</label>
    <select name="fkUbicacion[]" id="fkUbicacion" class="form-control cont_ub">
        <option value="">-- Seleccione una opción --</option>
        @foreach ($ubicaciones as $ubi)
            <option value="{{ $ubi->idubicacion }}"
                @if ($ubi->idubicacion == old('id_ubi', $ub->id_ubi))
                selected="selected"
            @endif
            >{{ $ubi->nombre }}</option>
        @endforeach
    </select>
    <button type = "button" class = "btn btn-danger elim_ub" data-id = "{{ $key + 1 }}">
        <i class="fas fa-trash" data-id = "{{ $key + 1 }}"></i>
    </button>
</div>
@endforeach
