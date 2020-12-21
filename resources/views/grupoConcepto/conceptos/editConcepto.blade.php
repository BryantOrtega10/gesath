@foreach($conceptosFK as $key => $cFK)
<div class="anchocompleto_flex conceptos_{{ $key + 1 }}">
    <label for="fkConcepto">Concepto {{ $key + 1 }}</label>
    <select name="fkConcepto[]" id="fkConcepto" class="form-control cont_ub">
        <option value="">-- Seleccione una opción --</option>
        @foreach($conceptos as $concepto)
        <option value="{{$concepto->idconcepto}}"
            @if ($concepto->idconcepto == old('fkConcepto', $cFK->fkConcepto))
            selected="selected"
            @endif
            >{{$concepto->nombre}}</option>
        @endforeach
    </select>
    <button type = "button" class = "btn btn-danger elim_con" data-id = "{{ $key + 1 }}">
        <i class="fas fa-trash" data-id = "{{ $key + 1 }}"></i>
    </button>
</div>
@endforeach