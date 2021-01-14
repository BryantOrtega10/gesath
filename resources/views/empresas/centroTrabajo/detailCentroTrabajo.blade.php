<form method="post" action="/centroTrabajo/editarCentroTrabajo/{{ $centro->idCentroTrabajo }}" class="formGen">
	<h2>Detalle calendario</h2>
	@csrf
	<div class="form-group">
		<label for="codigo" class="control-label">Código *</label>
		<input type="number" class="form-control" id="codigo" disabled name="codigo" value = "{{ $centro->codigo }}" />
	</div>
	<div class="form-group">
		<label for="nombre" class="control-label">Nombre *</label>
		<input type="text" class="form-control" id="nombre" disabled name="nombre" value = "{{ $centro->nombre }}" />
	</div>
	<div class="form-group">
		<label for="fkNivelArl" class="control-label">Nivel ARL *</label>
        <select class="form-control" id="fkNivelArl" disabled name="fkNivelArl">
            <option value = ''>-- Seleccione una opción --</option>
            @foreach($nivelArl as $n)
            <option value = '{{ $n->idnivel_arl }}'
                @if ($n->idnivel_arl == old('idnivel_arl', $centro->fkNivelArl))
                    selected="selected"
                @endif
            >{{ $n->nombre }}</option>
            @endforeach
        </select>
    </div>
</form>