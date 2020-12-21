function cargando(){
	if(typeof $("#cargando")[0] !== 'undefined'){
		$("#cargando").css("display", "flex");
	}
	else{
		$("body").append('<div id="cargando" style="display: flex;">Cargando...</div>');
	}
}
$(document).ready(function(){
    function cargarAjax(){
        if(parseInt($("#realizarConsulta").val()) == 1){
            cargando();
            const idCargaDatosPasados = $("#idCargaDatosPasados").val();
            $.ajax({
                type:'GET',
                url: "/datosPasadosSal/subir/" + idCargaDatosPasados,
                success:function(data){
                    $("#cargando").css("display", "none");
                    if(data.success){
                        if(data.seguirSubiendo){
                            $("#realizarConsulta").val("1");
                            setTimeout(() => {
                                cargarAjax();
                            }, 500);
                        }
                        else{
                            $("#realizarConsulta").val("0");
                            window.location.reload();
                        }
                        $(".progress-bar").css("width",data.porcentaje);
                        $(".progress-bar").html(data.porcentaje);
                        $("#datosCargados").html(data.mensaje);
                    }
                    
                },
                error: function(data){
                    console.log("error");
                    console.log(data);
                }
            });		
        }
    }
    cargarAjax();
});
