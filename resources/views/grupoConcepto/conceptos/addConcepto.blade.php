<div class="anchocompleto_flex ubicacion_{{ $idDom }}">
    <label for="fkConcepto">Concepto {{ $idDom }}</label>
    <select name="fkConcepto[]" id="fkConcepto" class="form-control cont_ub">
        <option value="">-- Seleccione una opción --</option>
        @foreach ($conceptos as $con)
            <option value="{{ $con->idconcepto }}">{{ $con->nombre }}</option>
        @endforeach
    </select>
    <button type = "button" class = "btn btn-danger elim_con" data-id="{{ $idDom }}">
        <i class="fas fa-trash"></i>
    </button>
</div>
<br>