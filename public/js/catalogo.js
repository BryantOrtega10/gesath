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
	$("body").on("change","#fkTipoTerceroCred",function(e){
        $(".elementoTerceroCred").removeClass("activo");;
        if($(this).val() == "8"){
            $(".elementoTerceroCred").addClass("activo");
        }
	});
	$("body").on("change","#fkTipoTerceroDeb",function(e){
        $(".elementoTerceroDeb").removeClass("activo");;
        if($(this).val() == "8"){
            $(".elementoTerceroDeb").addClass("activo");
        }
	});


	$("body").on("click",".quitarGrupo",function(e){
		const dataId = $(this).attr("data-id");
		$(".contGrupoCuenta[data-id="+dataId+"]").remove();
		
	});
	
	$("body").on("click","#addGrupoCuenta",function(e){
		var dataId = $(this).attr("data-id");
		dataId++;
		$(this).attr("data-id",dataId)
        if(typeof $("#cargando")[0] !== 'undefined'){
			$("#cargando").css("display", "flex");
		}
		else{
			$("body").append('<div id="cargando" style="display: flex;">Cargando...</div>');
		}
		$.ajax({
			type:'GET',
			url: "/catalogo-contable/getGrupos/"+dataId,
			success:function(data){
				$("#cargando").css("display", "none");
				$(".datosCuenta").append(data);;
		   },
		   error: function(data){
				   console.log("error");
				console.log(data);
			}
		});	
	});
	

	


	$("body").on("change",".tablaConsulta",function(e){
		const tablaConsulta = $(this).attr("data-id");
		$(".grupoConceptoCuenta[data-id="+tablaConsulta+"]").removeClass("activo");
		$(".grupoProvision[data-id="+tablaConsulta+"]").removeClass("activo");
		$(".grupoAporteEmpleador[data-id="+tablaConsulta+"]").removeClass("activo");
		$(".conceptoCuenta[data-id="+tablaConsulta+"]").removeClass("activo");

		if($(this).val() == "1"){
			$(".grupoConceptoCuenta[data-id="+tablaConsulta+"]").addClass("activo");
		}
		else if($(this).val() == "2"){
			$(".grupoProvision[data-id="+tablaConsulta+"]").addClass("activo");
		}
		else if($(this).val() == "3"){
			$(".grupoAporteEmpleador[data-id="+tablaConsulta+"]").addClass("activo");
		}
		else if($(this).val() == "4"){
			$(".conceptoCuenta[data-id="+tablaConsulta+"]").addClass("activo");
		}
	});



	
	$("body").on("change","#cuentaCred",function(e){
		if($(this).val()=="nueva"){
			$(".cuentaCreditoNueva").addClass("activo");
		}
		else{
			$(".cuentaCreditoNueva").removeClass("activo");
		}
	});
	$("body").on("change","#cuentaDeb",function(e){
		if($(this).val()=="nueva"){
			$(".cuentaDebitoNueva").addClass("activo");
		}
		else{
			$(".cuentaDebitoNueva").removeClass("activo");
		}

	});

	$("body").on("change","#fkCentroCosto",function(e){
		$("#cuentaCred").html('<option value="nueva">Nueva</option>');
		$("#cuentaDeb").html('<option value="nueva">Nueva</option>');
		actualizarCuentas();
	});
	$("body").on("change","#fkEmpresa",function(e){
        

		
		e.preventDefault();
		$("#fkCentroCosto").html('<option value="">Todos</option>');
		$("#cuentaCred").html('<option value="nueva">Nueva</option>');
		$("#cuentaDeb").html('<option value="nueva">Nueva</option>');
		actualizarCuentas();
		const idEmpresa = $(this).val();
		if(idEmpresa != ""){
			if(typeof $("#cargando")[0] !== 'undefined'){
				$("#cargando").css("display", "flex");
			}
			else{
				$("body").append('<div id="cargando" style="display: flex;">Cargando...</div>');
			}
			$.ajax({
				type:'GET',
				url: "/catalogo-contable/getCentrosCosto/"+idEmpresa,
				success:function(data){
					$("#cargando").css("display", "none");
					$("#fkCentroCosto").append(data.html);
					$("#fkCentroCosto").trigger("change");

			   },
			   error: function(data){
					   console.log("error");
					console.log(data);
				}
			});	
		}
		
	});


	function actualizarCuentas(){
		
		const idEmpresa = $("#fkEmpresa").val();
		const idCentroCosto = $("#fkCentroCosto").val();
		if(typeof $("#cargando")[0] !== 'undefined'){
			$("#cargando").css("display", "flex");
		}
		else{
			$("body").append('<div id="cargando" style="display: flex;">Cargando...</div>');
		}
		$.ajax({
			type:'GET',
			url: "/catalogo-contable/getCuentas/"+idEmpresa+"/"+idCentroCosto,
			success:function(data){
				$("#cargando").css("display", "none");
				$("#cuentaCred").append(data.html);
				$("#cuentaDeb").append(data.html);
				$("#cuentaDeb").trigger("change");
				$("#cuentaCred").trigger("change");

		   },
		   error: function(data){
				console.log("error");
				console.log(data);
			}
		});	
	}

	
	

	$("#addCuenta").click(function(e){
		e.preventDefault();
		if(typeof $("#cargando")[0] !== 'undefined'){
			$("#cargando").css("display", "flex");
		}
		else{
			$("body").append('<div id="cargando" style="display: flex;">Cargando...</div>');
		}
		$.ajax({
			type:'GET',
			url: "/catalogo-contable/getForm/add",
			success:function(data){
				$("#cargando").css("display", "none");
				$(".respForm").html(data);
				$('#catalogoModal').modal('show');
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
				$('#catalogoModal').modal('show');
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
					$(".print-error").find("ul").html('');
                    $(".print-error").css('display','block');
                    $.each(data.error, function( key, value ) {
                        $(".print-error").find("ul").append('<li>'+value+'</li>');
                    });
				}
		   },
		   error: function(data){
		   		console.log("error");
				console.log(data);
			}
		});	
	});
	$(".recargar").click(function(e){
		e.preventDefault();
		window.open("/catalogo-contable/","_self");
	});
});