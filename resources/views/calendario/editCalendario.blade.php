<form method="post" class="formEdit">
	<h2>Editar calendario</h2>

    @csrf
    {{--<input type="hidden" name="idCalendario" value = "{{ $calendario->idCalendario }}">--}}
	<div class="form-group">
		<label for="selectAnio">Seleccione un año</label>
		<select name="selectAnio" id="selectAnio" class="form-control">
			<option value="">-- Seleccione un año --</option>
			<option value="2021">2021</option>
			<option value="2022">2022</option>
			<option value="2023">2023</option>
			<option value="2024">2024</option>
			<option value="2025">2025</option>
		</select>
	</div>
	<div id="fullCalendar_cont">
	</div>
	<button type="submit" class="btn btn-success">Guardar cambios</button>
</form>
<script>
	$(document).ready(() => {
		let arrDatosFechas = '{{ $calendario }}';
		let fechasListas = JSON.parse(arrDatosFechas.replace(/&quot;/g,'"'));
		let arrDias = [];
		let arrIniciosSemana = [];
		let arrFinesSemana = [];
		let diaSeleccionado = '';
		Object.keys(fechasListas).forEach((i, el) => {
			arrDias.push(fechasListas[i]['fecha']);
			arrIniciosSemana.push(fechasListas[i]['fechaInicioSemana']);
			arrFinesSemana.push(fechasListas[i]['fechaFinSemana']);
		});
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});
    	let calendarEl = document.getElementById('fullCalendar_cont');
    	let calendar = new FullCalendar.Calendar(calendarEl, {
			locale: 'es',
			initialView: 'dayGridMonth',
			dateClick : (info) => clicAFecha(info),
			dayCellDidMount: (date, cell) => renderCalendario(date),
			headerToolbar: {
				left: 'prev,next today',
				center: 'title',
				right: 'dayGridMonth'
			}
		});
		calendar.render();
		setTimeout(() => {
			$('.fc-dayGridMonth-button').trigger('click');
		}, 200);

		$("body").on("change", "#selectAnio", (e) => {
			const valorAnio = $('#selectAnio').val();
			const fechaActual = moment(new Date(), 'YYYY-MM-DD');
			const mesActual = fechaActual.format('M');
			const diaActual = fechaActual.format('D');
			const fechaMover = `${valorAnio}-${mesActual}-${diaActual}`;
			calendar.gotoDate(moment(fechaMover).toISOString());
		});
		
		$("body").on("submit", ".formEdit", function(e) {
			e.preventDefault();
			const formData = new FormData();
			formData.append('fecha', arrDias);
			formData.append('fechaInicioSemana', arrIniciosSemana);
			formData.append('fechaFinSemana', arrFinesSemana);
			$.ajax({
				type: 'POST',
				url: '/calendario/editarCalendario',
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
						retornarAlerta(
							'¡Error!',
							data.mensaje,
							'error',
							'Aceptar'
						);
					}
				},
				error: function(data) {
					const error = data.responseJSON;
					if (error.error_code === 'VALIDATION_ERROR') {
						mostrarErrores(error.errors);
					} else {
						$("#cargando").css("display", "none");
                        retornarAlerta(
                            data.responseJSON.exception,
                            data.responseJSON.message + ", en la linea: " + data.responseJSON.line,
                            'error',
                            'Aceptar'
                        );
                        console.log("error");
                        console.log(data);
					}
				}
			});
		});
		function clicAFecha(info) {
			let color = '#166F93';
			let colorLetra = '#fff';
			const pintado = pintarFechaClick(info.dateStr);
			if (!pintado) {
				color = 'transparent';
				colorLetra = '#212529';
			}
			info.dayEl.style.backgroundColor = color;
			info.dayEl.style.color = colorLetra;
		}
		function pintarFechaClick(fecha) {
			retorno = false;
			const dateString = fecha;
			diaSeleccionado = moment(fecha, 'YYYY-MM-DD');
			const diaInicioSemana = diaSeleccionado.startOf('isoweek').format('YYYY-MM-DD');
			const diaFinSemana = diaSeleccionado.endOf('isoweek').format('YYYY-MM-DD');
			
			const validarFechaSeleccionada = arrDias.find( x => x === dateString);
			if(validarFechaSeleccionada === undefined) {
				arrDias.push(dateString);
				arrIniciosSemana.push(diaInicioSemana);
				arrFinesSemana.push(diaFinSemana);
				retorno = true;
			} else {
				const index = arrDias.indexOf(dateString);
				if (index > -1) {
					arrDias.splice(index, 1);
					arrIniciosSemana.splice(index, 1);
					arrFinesSemana.splice(index, 1);
				}
			}
			return retorno;
		}
		function renderCalendario(date, cell) {
			let color = 'transparent';
			let colorLetra = '#212529';
			const fecha = moment(date.date).format('YYYY-MM-DD');
			const pintado = pintarFechaRender(fecha);
			if (pintado) {
				color = '#166F93';
				colorLetra = '#fff';
			}
			date.el.style.backgroundColor = color;
			date.el.style.color = colorLetra;
		}
		function pintarFechaRender(fecha) {
			retorno = false;
			const dateString = fecha;			
			const validarFechaSeleccionada = arrDias.find( x => x === dateString);
			if(validarFechaSeleccionada !== undefined) {
				retorno = true;
			}
			return retorno;
		}
	});	
</script>