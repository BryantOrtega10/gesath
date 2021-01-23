<form method="post" class="formGen">
	<h2>Agregar calendario</h2>
	@csrf
	<div id="fullCalendar_cont">

	</div>
	<button type="submit" class="btn btn-success">Crear calendario</button>
	{{-- <div class="form-group">
		<label for="fecha" class="control-label">Fecha *</label>
		<input type="date" class="form-control" id="fecha" required name="fecha" />
	</div>
	<div class="form-group">
		<label for="fechaInicioSemana" class="control-label">Fecha inicio semana *</label>
		<input type="date" class="form-control" id="fechaInicioSemana" required name="fechaInicioSemana" />
	</div>
	<div class="form-group">
		<label for="fechaFinSemana" class="control-label">Fecha fin semana *</label>
		<input type="date" class="form-control" id="fechaFinSemana" required name="fechaFinSemana" />
	</div>
	 --}}
</form>

<script>
	arrDias = [];
	arrIniciosSemana = [];
	arrFinesSemana = [];
	diaSeleccionado = '';
	$(document).ready(() => {
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});
		let calendarEl = document.getElementById('fullCalendar_cont');
		let calendar = new FullCalendar.Calendar(calendarEl, {
			locale: 'es',
				initialView: 'dayGridMonth',
			dateClick : (info) => clicAFecha(info)
		});
		calendar.render();
		
		$("body").on("submit", ".formGen", (e) => {
			e.preventDefault();
			const formData = new FormData();
			formData.append('fecha', arrDias);
			formData.append('fechaInicioSemana', arrIniciosSemana);
			formData.append('fechaFinSemana', arrIniciosSemana);
			$.ajax({
				type: 'POST',
				url: '/calendario/agregarCalendario',
				cache: false,
				processData: false,
				contentType: false,
				data: formData,
				success: function(data) {
					if (data.success) {
						retornarAlerta(
							'¡Hecho!',
							data.mensaje,
							'success',
							'Aceptar'
						);
						window.location.reload();
					} else {
						$("#infoErrorForm").css("display", "block");
						$("#infoErrorForm").html(data.mensaje);
					}
				},
				error: function(data) {
					const error = data.responseJSON;
					if (error.error_code === 'VALIDATION_ERROR') {
						mostrarErrores(error.errors);
					} else {
						console.log("error");
					}
				}
			});
		});
	});
	function clicAFecha(info) {
		const dateString = info.dateStr;
		diaSeleccionado = moment(info.dateStr, 'YYYY-MM-DD');
		const diaInicioSemana = diaSeleccionado.startOf('isoweek').format('YYYY-MM-DD');
		const diaFinSemana = diaSeleccionado.endOf('isoweek').format('YYYY-MM-DD');
		let color = '#166F93';
		let colorLetra = '#fff';
		const validarFechaSeleccionada = arrDias.find( x => x === dateString);
		if(validarFechaSeleccionada === undefined) {
			arrDias.push(dateString);
			arrIniciosSemana.push(dateString);
			arrFinesSemana.push(dateString);
		} else {
			const index = arrDias.indexOf(dateString);
			if (index > -1) {
				arrDias.splice(index, 1);
				arrIniciosSemana.splice(index, 1);
				arrFinesSemana.splice(index, 1);
			}
			color = 'transparent';
			colorLetra = '#212529';
		}
		info.dayEl.style.backgroundColor = color;
		info.dayEl.style.color = colorLetra;
	}
</script>