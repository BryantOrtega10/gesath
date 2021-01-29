function cargando() {
    if (typeof $("#cargando")[0] !== 'undefined') {
        $("#cargando").css("display", "flex");
    } else {
        $("body").append('<div id="cargando" style="display: flex;">Cargando...</div>');
    }
}
$(document).ready(function() {
    $("#variables").DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.22/i18n/Spanish.json'
        }
    });
    $(document).on('show.bs.modal', '.modal', function(event) {
        var zIndex = 1040 + (10 * $('.modal:visible').length);
        $(this).css('z-index', zIndex);
        setTimeout(function() {
            $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
        }, 0);
    });
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $("body").on("submit", "#formFormulaVariable", function(e) {
        e.preventDefault();
        cargando();
        var formdata = new FormData(this);
        $.ajax({
            type: 'POST',
            url: $(this).attr("action"),
            cache: false,
            processData: false,
            contentType: false,
            data: formdata,
            success: function(data) {
                $("#cargando").css("display", "none");
                if (data.success) {
                    $("#valor").val(data.valorFinal);
                    $('#valor').prop('readonly', true);
                    $(".respFormulaVariable").html(data.html);
                    $('#formulaVariableModal').modal('hide');
                }
            },
            error: function(data) {
                console.log("error");
                console.log(data);
            }
        });
    });

    $("body").on("click", ".quitarOperacion", function(e) {
        e.preventDefault();
        $(".operacionAdicional[data-id='" + $(this).attr("data-id") + "']").remove();
    });


    $("body").on("click", "#masOperaciones", function(e) {
        e.preventDefault();
        cargando();
        var numOperacion = $("#numOperacion").val();
        numOperacion++;
        var url = "/variables/getFormulaVariable/masFormulas/" + numOperacion;

        $.ajax({
            type: 'GET',
            url: url,
            success: function(data) {
                $("#cargando").css("display", "none");

                $("#numOperacion").val(numOperacion)
                $('.respMasOperaciones').append(data);
            },
            error: function(data) {
                console.log("error");
                console.log(data);
            }
        });
    });




    $("body").on("change", ".tipoFin", function() {
        const dataId = $(this).attr("data-id");
        $(".variableFin[data-id='" + dataId + "']").removeClass("activo");
        $(".valorFin[data-id='" + dataId + "']").removeClass("activo");

        $(".variableFin[data-id='" + dataId + "']").addClass("oculto");
        $(".valorFin[data-id='" + dataId + "']").addClass("oculto");

        $("." + $(this).val() + "Fin[data-id='" + dataId + "']").addClass("activo");
        $("." + $(this).val() + "Fin[data-id='" + dataId + "']").removeClass("oculto");
    });

    $("body").on("change", "#tipoInicio", function() {
        $(".variableInicial").removeClass("activo");
        $(".valorInicial").removeClass("activo");

        $(".variableInicial").addClass("oculto");
        $(".valorInicial").addClass("oculto");

        $("." + $(this).val() + "Inicial").addClass("activo");
        $("." + $(this).val() + "Inicial").removeClass("oculto");
    });



    $("body").on("change", "#tipoGeneracion", function() {

        if ($(this).val() == "Formula") {
            cargando();
            var url = "/variables/getFormulaVariable/";
            if (typeof $("#idVariable")[0] !== 'undefined') {
                url = "/variables/getFormulaVariable/" + $("#idVariable").val();
            }
            $.ajax({
                type: 'GET',
                url: url,
                success: function(data) {
                    $("#cargando").css("display", "none");
                    $(".respForm[data-para='formulaVariable']").html(data);
                    $('#formulaVariableModal').modal('show');
                },
                error: function(data) {
                    console.log("error");
                    console.log(data);
                }
            });
        } else {
            $('#valor').prop('readonly', false);
            $('.respFormulaVariable').html("");
        }

    });


    $("body").on("change", "#tipoCampo", function() {
        if (typeof $("#cargando")[0] !== 'undefined') {
            $("#cargando").css("display", "flex");
        } else {
            $("body").append('<div id="cargando" style="display: flex;">Cargando...</div>');
        }
        $.ajax({
            type: 'GET',
            url: "/variables/getForm/getTipoCampo/" + $(this).val(),
            success: function(data) {
                $("#cargando").css("display", "none");
                if ($("#tipoGeneracion").val() != "Formula") {
                    $("#valor").val("");
                    $("#valor").attr("data-validar", data.tipoValidacion);
                }

            },
            error: function(data) {
                console.log("error");
                console.log(data);
            }
        });
    });

    $("body").on("keypress", "input[data-validar='decimal']", function(e) {
        var key = (e.which) ? e.which : e.keyCode;
        if (key == 46) {
            if ($(this).val().indexOf(".") === -1) {
                return true;
            }
        }

        if (key >= 48 && key <= 57) {
            return true;
        } else {
            return false;
        }

    });



    $("#addVariable").click(function(e) {
        e.preventDefault();
        if (typeof $("#cargando")[0] !== 'undefined') {
            $("#cargando").css("display", "flex");
        } else {
            $("body").append('<div id="cargando" style="display: flex;">Cargando...</div>');
        }
        $.ajax({
            type: 'GET',
            url: "/variables/getForm/add",
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".respForm[data-para='variable']").html(data);
                $('#variableModal').modal('show');
            },
            error: function(data) {
                console.log("error");
                console.log(data);
            }
        });
    });
    $(".editar").click(function(e) {
        e.preventDefault();
        if (typeof $("#cargando")[0] !== 'undefined') {
            $("#cargando").css("display", "flex");
        } else {
            $("body").append('<div id="cargando" style="display: flex;">Cargando...</div>');
        }
        $.ajax({
            type: 'GET',
            url: $(this).attr("href"),
            success: function(data) {
                $("#cargando").css("display", "none");
                $(".respForm[data-para='variable']").html(data);
                $('#variableModal').modal('show');
            },
            error: function(data) {
                console.log("error");
                console.log(data);
            }
        });
    });
    $("body").on("submit", ".formGen", function(e) {
        e.preventDefault();
        cargando();
        var formdata = new FormData(this);
        $.ajax({
            type: 'POST',
            url: $(this).attr("action"),
            cache: false,
            processData: false,
            contentType: false,
            data: formdata,
            success: function(data) {

                if (data.success) {
                    window.location.reload();
                } else {
                    $("#infoErrorForm").css("display", "block");
                    $("#infoErrorForm").html(data.mensaje);
                }
            },
            error: function(data) {
                console.log("error");
                console.log(data);
            }
        });
    });
});