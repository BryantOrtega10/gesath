@extends('layouts.admin')
@section('title', 'Valor Upc Adicional')
@section('menuLateral')
    @include('layouts.partials.menu', [
        'dataUsu' => $dataUsu
    ])
@endsection

@section('contenido')
<div class="row">
    <div class="col-12">
        <h1 class="granAzul">Valor Upc Adicional</h1>
    </div>
</div>
<div class="cajaGeneral">
    <form method="POST" id="" autocomplete="off" class="formGen formGeneral formRedondeos" action="/ActualizarDatos/cambiarUpc" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-12">            
                <table class="table  table-striped ">
                    <tr>
                        <th>Grupo</th>
                        <th>Rango de edad</th>
                        <th>Zona normal</th>
                        <th>Zona especial</th>
                        <th>Grandes ciudades</th>
                        <th>Zonas alejadas</th>
                    </tr>
                    @php
                        $prevEdad = $tarifas[0]->fkUpcEdad;
                        $contador = 1;
                    @endphp
                        <tr>
                            <td>{{$contador}}</td>
                            <td>{{$edades[$contador - 1]}}</td>
                    @foreach ($tarifas as $tarifa)
                        @if ($prevEdad != $tarifa->fkUpcEdad)
                            @php
                                $contador++;
                                $prevEdad = $tarifa->fkUpcEdad;
                            @endphp
                            </tr>
                            <tr>
                                <td>{{$contador}}</td>
                                <td>{{$edades[$contador - 1]}}</td>
                        @endif
                        <td><input type="text" required class="separadorMiles" value="{{$tarifa->valor}}" name="tarifa_{{$tarifa->idUpcAdicionalTarifas}}" /></td>

                    @endforeach
                        </tr>

                </table>
            </div>
        </div>
        <input type="submit" value="Modificar" class="btnSubmitGen" />
    </form>
    <script type="text/javascript" src="{{ URL::asset('js/jquery.inputmask.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/jquery.inputmask.numeric.extensions.js') }}"></script>
    <script>
        $(document).ready((e) => {
            $(".separadorMiles").inputmask({ alias: "currency", removeMaskOnSubmit: true });
            $("body").on("submit", ".formGen", function(e) {
                e.preventDefault();
                var formdata = new FormData(this);
                $.ajax({
                    type: 'POST',
                    url: $(this).attr("action"),
                    cache: false,
                    processData: false,
                    contentType: false,
                    data: formdata,
                    success: function(data) {
                        $(".separadorMiles").inputmask({ alias: "currency", removeMaskOnSubmit: true });
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
                                '¡Hecho!',
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
        });
    </script>
@endsection
