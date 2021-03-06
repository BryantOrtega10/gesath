<form method = "POST" target="_blank" class="submit_2020" action = "/reportes/generarFormulario220">
    <div class="form-group">
        @csrf
        <input type="hidden" id = "empresa" name="empresa">
        <input type="hidden" id = "infoNomina" name="infoNomina">
        <input type="hidden" id = "idEmpleado" name="idEmpleado">
        <input type="hidden" id = "fechaExp" name="fechaExp">
        <input type="hidden" id = "reporte" name="reporte">
        <input type="hidden" id = "agenteRetenedor" name="agenteRetenedor">
        <label for = "idformulario">Seleccione un año para generar el formulario</label>
        <select name="anio" id="idformulario" class="form-control">
            <option value="">-- Seleccione una opción --</option>
            @foreach ($formularios as $f)
                <option value="{{ $f->idFormulario220 }}">{{ $f->anio }}</option>
            @endforeach
        </select>
    </div>
    <button type = "submit" class="btn btn-primary">
        Enviar
    </button>
</form>