function cargando(){
	if(typeof $("#cargando")[0] !== 'undefined'){
		$("#cargando").css("display", "flex");
	}
	else{
		$("body").append('<div id="cargando" style="display: flex;">Cargando...</div>');
	}
}
$(document).ready(function(){
	$.ajaxSetup({
	    headers: {
	        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
	    }
	});
	$("body").on("click","#masItems", function(){
		cargando();
		var numItem = $("#numItem").val();
		numItem++;
		var url =  "/concepto/condiciones/masItems/"+numItem;

		$.ajax({
			type:'GET',
			url: url,
			success:function(data){
				$("#cargando").css("display", "none");
				$("#numItem").val(numItem);
				$(".contItemCondicion").append(data.html);
		   },
		   error: function(data){
		   		console.log("error");
				console.log(data);
			}
		});	
	});

	
	$("body").on("change","#tipoInicio", function(){

		$(".conceptoInicial").removeClass("activo");
		$(".grupoInicial").removeClass("activo");

		
		$(".conceptoInicial").addClass("oculto");
		$(".grupoInicial").addClass("oculto");
		
		$(".multiplicadorInicial").addClass("activo");
		$(".multiplicadorInicial").removeClass("oculto");
		

		$("."+$(this).val()+"Inicial").addClass("activo");
		$("."+$(this).val()+"Inicial").removeClass("oculto");
		$(".multiplicadorInicial").val("1");

		
	});
	$("body").on("change", ".selectTipoFin1", function(e){
		e.preventDefault();
		const dataId = $(this).attr("data-id");
		$(".variableFin1[data-id='"+dataId+"']").removeClass("activo");
		$(".valorFin1[data-id='"+dataId+"']").removeClass("activo");
		$(".conceptoFin1[data-id='"+dataId+"']").removeClass("activo");
		$(".grupoFin1[data-id='"+dataId+"']").removeClass("activo");

		
		$(".variableFin1[data-id='"+dataId+"']").addClass("oculto");
		$(".valorFin1[data-id='"+dataId+"']").addClass("oculto");
		$(".conceptoFin1[data-id='"+dataId+"']").addClass("oculto");
		$(".grupoFin1[data-id='"+dataId+"']").addClass("oculto");


		$("."+$(this).val()+"Fin1[data-id='"+dataId+"']").addClass("activo");
		$("."+$(this).val()+"Fin1[data-id='"+dataId+"']").removeClass("oculto");

		$(".multiplicadorFin1[data-id='"+dataId+"']").addClass("activo");
		$(".multiplicadorFin1[data-id='"+dataId+"']").removeClass("oculto");
		$(".multiplicadorFin1[data-id='"+dataId+"']").val("1");

	});
	$("body").on("change", ".selectTipoFin2", function(e){
		e.preventDefault();
		const dataId = $(this).attr("data-id");
		$(".variableFin2[data-id='"+dataId+"']").removeClass("activo");
		$(".valorFin2[data-id='"+dataId+"']").removeClass("activo");
		$(".conceptoFin2[data-id='"+dataId+"']").removeClass("activo");
		$(".grupoFin2[data-id='"+dataId+"']").removeClass("activo");

		
		$(".variableFin2[data-id='"+dataId+"']").addClass("oculto");
		$(".valorFin2[data-id='"+dataId+"']").addClass("oculto");
		$(".conceptoFin2[data-id='"+dataId+"']").addClass("oculto");
		$(".grupoFin2[data-id='"+dataId+"']").addClass("oculto");


		$("."+$(this).val()+"Fin2[data-id='"+dataId+"']").addClass("activo");
		$("."+$(this).val()+"Fin2[data-id='"+dataId+"']").removeClass("oculto");


		$(".multiplicadorFin2[data-id='"+dataId+"']").addClass("activo");
		$(".multiplicadorFin2[data-id='"+dataId+"']").removeClass("oculto");
		$(".multiplicadorFin2[data-id='"+dataId+"']").val("1");
	});
	$("body").on("change", ".selectTipoFin3", function(e){
		e.preventDefault();
		const dataId = $(this).attr("data-id");
		$(".variableFin3[data-id='"+dataId+"']").removeClass("activo");
		$(".valorFin3[data-id='"+dataId+"']").removeClass("activo");
		$(".conceptoFin3[data-id='"+dataId+"']").removeClass("activo");
		$(".grupoFin3[data-id='"+dataId+"']").removeClass("activo");

		
		$(".variableFin3[data-id='"+dataId+"']").addClass("oculto");
		$(".valorFin3[data-id='"+dataId+"']").addClass("oculto");
		$(".conceptoFin3[data-id='"+dataId+"']").addClass("oculto");
		$(".grupoFin3[data-id='"+dataId+"']").addClass("oculto");


		$("."+$(this).val()+"Fin3[data-id='"+dataId+"']").addClass("activo");
		$("."+$(this).val()+"Fin3[data-id='"+dataId+"']").removeClass("oculto");

		$(".multiplicadorFin3[data-id='"+dataId+"']").addClass("activo");
		$(".multiplicadorFin3[data-id='"+dataId+"']").removeClass("oculto");
		$(".multiplicadorFin3[data-id='"+dataId+"']").val("1");
	});
	
	$("body").on("change", ".operador", function(e){
		
		e.preventDefault();
		cargando();
		var dataid= $(this).attr("data-id");
		const url = "/concepto/condiciones/camposOperador/" + $(this).val();
		$.ajax({
			type:'GET',
			url: url,
			success:function(data){
				$("#cargando").css("display", "none");
				$(".selectTipoFin1").val("");
				$(".selectTipoFin2").val("");
				$(".selectTipoFin3").val("");
				

				if(data.numCampos == 2){


					$(".tipoFin1[data-id='"+dataid+"']").addClass("activo");
					$(".tipoFin2[data-id='"+dataid+"']").removeClass("activo");
					$(".tipoFin2[data-id='"+dataid+"']").addClass("oculto");
					$(".tipoFin3[data-id='"+dataid+"']").removeClass("activo");
					$(".tipoFin3[data-id='"+dataid+"']").addClass("oculto");
				}
				else if(data.numCampos == 3){
					$(".tipoFin1[data-id='"+dataid+"']").addClass("activo");
					$(".tipoFin2[data-id='"+dataid+"']").addClass("activo");

					$(".tipoFin3[data-id='"+dataid+"']").removeClass("activo");
					$(".tipoFin3[data-id='"+dataid+"']").addClass("oculto");

					
				}

				$(".variableFin1[data-id='"+dataid+"']").removeClass("activo");
				$(".conceptoFin1[data-id='"+dataid+"']").removeClass("activo");
				$(".grupoFin1[data-id='"+dataid+"']").removeClass("activo");
				$(".valorFin1[data-id='"+dataid+"']").removeClass("activo");				
				$(".multiplicadorFin1[data-id='"+dataid+"']").removeClass("activo");
				$(".variableFin1[data-id='"+dataid+"']").addClass("oculto");
				$(".conceptoFin1[data-id='"+dataid+"']").addClass("oculto");
				$(".grupoFin1[data-id='"+dataid+"']").addClass("oculto");
				$(".valorFin1[data-id='"+dataid+"']").addClass("oculto");
				$(".multiplicadorFin1[data-id='"+dataid+"']").addClass("oculto");

				$(".variableFin2[data-id='"+dataid+"']").removeClass("activo");
				$(".conceptoFin2[data-id='"+dataid+"']").removeClass("activo");
				$(".grupoFin2[data-id='"+dataid+"']").removeClass("activo");
				$(".valorFin2[data-id='"+dataid+"']").removeClass("activo");
				$(".multiplicadorFin2[data-id='"+dataid+"']").removeClass("activo");
				$(".variableFin2[data-id='"+dataid+"']").addClass("oculto");
				$(".conceptoFin2[data-id='"+dataid+"']").addClass("oculto");
				$(".grupoFin2[data-id='"+dataid+"']").addClass("oculto");
				$(".valorFin2[data-id='"+dataid+"']").addClass("oculto");
				$(".multiplicadorFin2[data-id='"+dataid+"']").addClass("oculto");

				$(".variableFin3[data-id='"+dataid+"']").removeClass("activo");
				$(".conceptoFin3[data-id='"+dataid+"']").removeClass("activo");
				$(".grupoFin3[data-id='"+dataid+"']").removeClass("activo");
				$(".valorFin3[data-id='"+dataid+"']").removeClass("activo");
				$(".multiplicadorFin3[data-id='"+dataid+"']").removeClass("activo");
				$(".variableFin3[data-id='"+dataid+"']").addClass("oculto");
				$(".conceptoFin3[data-id='"+dataid+"']").addClass("oculto");
				$(".grupoFin3[data-id='"+dataid+"']").addClass("oculto");
				$(".valorFin3[data-id='"+dataid+"']").addClass("oculto");
				$(".multiplicadorFin3[data-id='"+dataid+"']").addClass("oculto");
				
				
		   },
		   error: function(data){
		   		console.log("error");
				console.log(data);
			}
		});	



	});


	$("#addCondicion").click(function(e){
		e.preventDefault();
		cargando();
		$.ajax({
			type:'GET',
			url: "/concepto/condiciones/getForm/add/"+$("#idConc").val(),
			success:function(data){
				$("#cargando").css("display", "none");
				$(".respForm[data-para='condicion']").html(data);
				$('#condicionModal').modal('show');
		   },
		   error: function(data){
		   		console.log("error");
				console.log(data);
			}
		});	
	});
	$(".editar").click(function(e){
		e.preventDefault();
		if(typeof $("#cargando")[0] !== 'undefined'){
			$("#cargando").css("display", "flex");
		}
		else{
			$("body").append('<div id="cargando" style="display: flex;">Cargando...</div>');
		}
		$.ajax({
			type:'GET',
			url: $(this).attr("href"),
			success:function(data){
				$("#cargando").css("display", "none");
				$(".respForm").html(data);
				$('#ubicacionModal').modal('show');
		   },
		   error: function(data){
		   		console.log("error");
				console.log(data);
			}
		});	
	});
	$("body").on("submit", ".formGen", function(e){
		e.preventDefault();
		if(typeof $("#cargando")[0] !== 'undefined'){
			$("#cargando").css("display", "flex");
		}
		else{
			$("body").append('<div id="cargando" style="display: flex;">Cargando...</div>');
		}
		var formdata = new FormData(this);
		$.ajax({
			type:'POST',
			url: $(this).attr("action"),
			cache:false,
			processData: false,
            contentType: false,
			data: formdata,
			success:function(data){
				
				if(data.success){
					window.location.reload();
				}
				else{
					$("#infoErrorForm").css("display", "block");
					$("#infoErrorForm").html(data.mensaje);
				}
		   },
		   error: function(data){
		   		console.log("error");
				console.log(data);
			}
		});	
	});
});