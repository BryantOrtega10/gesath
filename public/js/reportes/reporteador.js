function cargando() {
    if (typeof $("#cargando")[0] !== 'undefined') {
        $("#cargando").css("display", "flex");
    } else {
        $("body").append('<div id="cargando" style="display: flex;">Cargando...</div>');
    }
}
$(document).ready(function () {

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $("body").on("change", "#tipoReporte", function (e) {
        e.preventDefault();
        const tipoReporte = $(this).val();
        if (typeof $("#cargando")[0] !== 'undefined') {
            $("#cargando").css("display", "flex");
        } else {
            $("body").append('<div id="cargando" style="display: flex;">Cargando...</div>');
        }
        $.ajax({
            type: 'GET',
            url: "/reportes/reporteador/getItemsxReporte/" + tipoReporte,
            success: function (data) {
                $("#cargando").css("display", "none");
                $("#contItems").html(data);

            },
            error: function (data) {
                console.log("error");
                console.log(data);
            }
        });
    });
    function verificarBtnsSelects(){
        if($("#opcionesDisponibles option:selected").length){
            $("#pasarUno").prop( "disabled", false );
        }else{
            $("#pasarUno").prop( "disabled", true );
        }

        if($("#opcionesSeleccionadas option:selected").length){
            $("#quitarUno").prop( "disabled", false );
        }else{
            $("#quitarUno").prop( "disabled", true );
        }


        if($("#opcionesDisponibles option").length){
            $("#pasarTodos").prop( "disabled", false );
        }else{
            $("#pasarTodos").prop( "disabled", true );
        }

        if($("#opcionesSeleccionadas option").length){
            $("#quitarTodos").prop( "disabled", false );
        }else{
            $("#quitarTodos").prop( "disabled", true );
        }


        if($("#opcionesSeleccionadas option:selected").length){
            $("#primero").prop( "disabled", false );
            $("#subir").prop( "disabled", false );
            $("#bajar").prop( "disabled", false );
            $("#ultimo").prop( "disabled", false );
        }else{
            $("#primero").prop( "disabled", true );
            $("#subir").prop( "disabled", true );
            $("#bajar").prop( "disabled", true );
            $("#ultimo").prop( "disabled", true );
        }
    }
    $("body").on("click", "#primero", function () {

        
        var arrOpcionesArriba = [];
        $("#opcionesSeleccionadas option:selected").each(function(){
            arrOpcionesArriba.push($(this));
            $(this).remove();
        });

        var arrOpcionesNoSel = [];

        $("#opcionesSeleccionadas option").each(function(){
            arrOpcionesNoSel.push($(this));
            $(this).remove();
        });

        arrOpcionesArriba.forEach(element => $("#opcionesSeleccionadas").append(element));
        arrOpcionesNoSel.forEach(element => $("#opcionesSeleccionadas").append(element));
        



    });
    $("body").on("click", "#subir", function () {
        var arrOpcionesArriba = [];
        var arrOpciones = [];
        $("#opcionesSeleccionadas option").each(function(index){
            if(this.selected){
                arrOpcionesArriba.push(index);
            }
            arrOpciones.push($(this));
            
            $(this).remove();
        });

        arrOpcionesArriba.forEach(function(element){
            if(element > 0){
                let place = arrOpciones[element - 1];
                arrOpciones[element - 1] = arrOpciones[element];
                arrOpciones[element] = place;
            }
        });
       
        
        arrOpciones.forEach(function(element){
            $("#opcionesSeleccionadas").append(element)
        });

    });
    $("body").on("click", "#bajar", function () {
        
        var arrOpcionesArriba = [];
        var arrOpciones = [];
        $("#opcionesSeleccionadas option").each(function(index){
            if(this.selected){
                arrOpcionesArriba.push(index);
            }
            arrOpciones.push($(this));
            
            $(this).remove();
        });

        arrOpcionesArriba.forEach(function(element){
            if(element < arrOpciones.length){
                let place = arrOpciones[element + 1];
                arrOpciones[element + 1] = arrOpciones[element];
                arrOpciones[element] = place;
            }
        });
       
        
        arrOpciones.forEach(function(element){
            $("#opcionesSeleccionadas").append(element)
        });
    });
    $("body").on("click", "#ultimo", function () {
        var arrOpcionesArriba = [];
        $("#opcionesSeleccionadas option:selected").each(function(){
            arrOpcionesArriba.push($(this));
            $(this).remove();
        });

        var arrOpcionesNoSel = [];

        $("#opcionesSeleccionadas option").each(function(){
            arrOpcionesNoSel.push($(this));
            $(this).remove();
        });

        arrOpcionesNoSel.forEach(element => $("#opcionesSeleccionadas").append(element));
        arrOpcionesArriba.forEach(element => $("#opcionesSeleccionadas").append(element));
        
    });


    $("body").on("click", "#pasarTodos", function () {
        $("#opcionesDisponibles option").each(function(){
            $("#opcionesSeleccionadas").append($(this));
        });
        verificarBtnsSelects();


    });

    $("body").on("click", "#quitarTodos", function () {
        $("#opcionesSeleccionadas option").each(function(){
            $("#opcionesDisponibles").append($(this));
        });
        verificarBtnsSelects();
    });


    $("body").on("click", "#pasarUno",function () {
        $("#opcionesDisponibles option:selected").each(function(){
            $("#opcionesSeleccionadas").append($(this));
        });
        verificarBtnsSelects();
    });

    $("body").on("click", "#quitarUno",function () {
        $("#opcionesSeleccionadas option:selected").each(function(){
            $("#opcionesDisponibles").append($(this));
        });
        verificarBtnsSelects();
    });

    $("body").on("change", "#opcionesSeleccionadas", function () {
        verificarBtnsSelects();
    });
    $("body").on("change", "#opcionesDisponibles", function () {
        verificarBtnsSelects();
    });

    $("#addReporte").click(function (e) {
        e.preventDefault();
        if (typeof $("#cargando")[0] !== 'undefined') {
            $("#cargando").css("display", "flex");
        } else {
            $("body").append('<div id="cargando" style="display: flex;">Cargando...</div>');
        }
        $.ajax({
            type: 'GET',
            url: "/reportes/reporteador/getForm/add",
            success: function (data) {
                $("#cargando").css("display", "none");
                $(".respForm").html(data);
                $('#reporteadorModal').modal('show');
            },
            error: function (data) {
                console.log("error");
                console.log(data);
            }
        });
    });

    $("body").on("click",".quitarFiltro",function (e) {

        $(this).parent().parent().remove();
    });

    $("body").on("click","#addFiltro",function (e) {
        e.preventDefault();
        let campo = $("#campo").val();
        if(campo != ""){
            if (typeof $("#cargando")[0] !== 'undefined') {
                $("#cargando").css("display", "flex");
            } else {
                $("body").append('<div id="cargando" style="display: flex;">Cargando...</div>');
            }
            $.ajax({
                type: 'GET',
                url: "/reportes/reporteador/getForm/filtro/"+campo,
                success: function (data) {
                    $("#cargando").css("display", "none");
                    $(".filtros").append(data);
                },
                error: function (data) {
                    console.log("error");
                    console.log(data);
                }
            });
        }        
    });


    
    $(".generarReporte").click(function (e) {
        e.preventDefault();
        if (typeof $("#cargando")[0] !== 'undefined') {
            $("#cargando").css("display", "flex");
        } else {
            $("body").append('<div id="cargando" style="display: flex;">Cargando...</div>');
        }
        $.ajax({
            type: 'GET',
            url: $(this).attr("href"),
            success: function (data) {
                $("#cargando").css("display", "none");
                $(".respForm").html(data);
                $('#reporteadorModal').modal('show');
            },
            error: function (data) {
                console.log("error");
                console.log(data);
            }
        });
    });

    $(".editar").click(function (e) {
        e.preventDefault();
        if (typeof $("#cargando")[0] !== 'undefined') {
            $("#cargando").css("display", "flex");
        } else {
            $("body").append('<div id="cargando" style="display: flex;">Cargando...</div>');
        }
        $.ajax({
            type: 'GET',
            url: $(this).attr("href"),
            success: function (data) {
                $("#cargando").css("display", "none");
                $(".respForm").html(data);
                $('#reporteadorModal').modal('show');
            },
            error: function (data) {
                console.log("error");
                console.log(data);
            }
        });
    });
    $("body").on("submit", ".formGen", function (e) {
        e.preventDefault();

        
        $("#opcionesSeleccionadas option").each(function(){
            $(this).prop("selected", true);
        });


        if (typeof $("#cargando")[0] !== 'undefined') {
            $("#cargando").css("display", "flex");
        } else {
            $("body").append('<div id="cargando" style="display: flex;">Cargando...</div>');
        }
        var formdata = new FormData(this);
        $.ajax({
            type: 'POST',
            url: $(this).attr("action"),
            cache: false,
            processData: false,
            contentType: false,
            data: formdata,
            success: function (data) {

                if (data.success) {
                    alert("Base de datos actualizada");
                    window.location.reload();
                } else {
                    $("#infoErrorForm").css("display", "block");
                    $("#infoErrorForm").html(data.mensaje);
                }
            },
            error: function (data) {
                console.log("error");
                console.log(data);
            }
        });
    });
});