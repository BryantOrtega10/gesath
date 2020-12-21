@foreach ($conceptosFK as $key => $co)
<div class="conceptos_{{ $key + 1 }}">
    <label for="fkUbicacion">Concepto {{ $key + 1 }}</label>
    <select name="fkUbicacion[]" id="fkUbicacion" class="form-control" disabled>
        <option value="">-- Seleccione una opción --</option>
        @foreach ($conceptos as $con)
            <option value="{{ $con->idconcepto }}"
                @if ($con->idconcepto == old('fkConcepto', $co->fkConcepto))
                selected="selected"
            @endif
            >{{ $con->nombre }}</option>
        @endforeach
    </select>
</div>
@endforeach
