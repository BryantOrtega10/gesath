<form method="post" class="formEdit">
    <h2>Detalle calendario</h2>
    
	@csrf
	<div class="form-group">
		<label for="selectAnio">Seleccione un año</label>
		<select name="selectAnio" id="selectAnio" class="form-control">
			<option value="">-- Seleccione un año --</option>
			<option value="2022">2022</option>
			<option value="2023">2023</option>
			<option value="2024">2024</option>
			<option value="2025">2025</option>
		</select>
	</div>
	<div id="fullCalendar_cont">
	</div>
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
		let calendarEl = document.getElementById('fullCalendar_cont');
    	let calendar = new FullCalendar.Calendar(calendarEl, {
			locale: 'es',
			initialView: 'dayGridMonth',
			dayCellDidMount: (date, cell) => renderCalendario(date),
			headerToolbar: {
				left: 'prev,next today',
				center: 'title',
				right: 'dayGridMonth'
			}
		});
		
		calendar.render();
		$("body").on("change", "#selectAnio", (e) => {
			const valorAnio = $('#selectAnio').val();
			const fechaActual = moment(new Date(), 'YYYY-MM-DD');
			const mesActual = fechaActual.format('M');
			const diaActual = fechaActual.format('D');
			const fechaMover = `${valorAnio}-${mesActual}-${diaActual}`;
			calendar.gotoDate(moment(fechaMover).toISOString());
		});
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