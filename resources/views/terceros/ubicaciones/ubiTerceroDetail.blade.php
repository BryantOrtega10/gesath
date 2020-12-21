@foreach ($ubisTer as $key => $ub)
<div class="ubicacion_{{ $key + 1 }}">
    <label for="fkUbicacion">Ubicación {{ $$key + 1 }}</label>
    <select name="fkUbicacion[]" id="fkUbicacion" class="form-control" disabled>
        <option value="">-- Seleccione una opción --</option>
        @foreach ($ubicaciones as $ubi)
            <option value="{{ $ubi->idubicacion }}"
                @if ($ubi->idubicacion == old('id_ubi', $ub->id_ubi))
                selected="selected"
            @endif
            >{{ $ubi->nombre }}</option>
        @endforeach
    </select>
</div>
@endforeach
