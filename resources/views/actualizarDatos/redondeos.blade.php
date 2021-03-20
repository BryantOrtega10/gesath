@extends('layouts.admin')
@section('title', 'Redondeos')
@section('menuLateral')
    @include('layouts.partials.menu', [
        'dataUsu' => $dataUsu
    ])
@endsection

@section('contenido')
<div class="row">
    <div class="col-12">
        <h1 class="granAzul">Redondeos</h1>
    </div>
</div>
<div class="cajaGeneral">
    <form method="POST" id="" autocomplete="off" class="formGen formGeneral formRedondeos" action="/ActualizarDatos/cambiarRedondeos" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-12">            
                <table>
                    <tr>
                        <th>D&iacute;as </th>
                        <th>Ibc</th>
                        <th>Pensi&oacute;n</th>
                        <th>Salud 12.5%</th>
                        <th>Salud 12%</th>
                        <th>Salud 10%</th>
                        <th>Salud 8%</th>
                        <th>Salud 4%</th>
                        <th>Ccf</th>
                        <th>Riesgos Lv5</th>
                        <th>Riesgos Lv4</th>
                        <th>Riesgos Lv3</th>
                        <th>Riesgos Lv2</th>
                        <th>Riesgos Lv1</th>
                        <th>Sena 0.5%</th>
                        <th>Sena 2%</th>
                        <th>Icbf</th>
                        <th>Esap</th>
                        <th>Men</th>
                    </tr>
                    @foreach ($redondeos as $redondeo)
                        <tr>
                            <td>{{$redondeo->dias}}</td>
                            <td><input type="text" required name="ibc_{{$redondeo->id}}" class="separadorMiles" value="{{$redondeo->ibc}}" required /></td>
                            <td><input type="text" required name="pension_{{$redondeo->id}}" class="separadorMiles" value="{{$redondeo->pension}}" required /></td>
                            <td><input type="text" required name="salud_12_5_{{$redondeo->id}}" class="separadorMiles" value="{{$redondeo->salud_12_5}}" required /></td>
                            <td><input type="text" required name="salud_12_{{$redondeo->id}}" class="separadorMiles" value="{{$redondeo->salud_12}}" required /></td>
                            <td><input type="text" required name="salud_10_{{$redondeo->id}}" class="separadorMiles" value="{{$redondeo->salud_10}}" required /></td>
                            <td><input type="text" required name="salud_8_{{$redondeo->id}}" class="separadorMiles" value="{{$redondeo->salud_8}}" required /></td>
                            <td><input type="text" required name="salud_4_{{$redondeo->id}}" class="separadorMiles" value="{{$redondeo->salud_4}}" required /></td>
                            <td><input type="text" required name="ccf_{{$redondeo->id}}" class="separadorMiles" value="{{$redondeo->ccf}}" required /></td>
                            <td><input type="text" required name="riesgos_5_{{$redondeo->id}}" class="separadorMiles" value="{{$redondeo->riesgos_5}}" required /></td>
                            <td><input type="text" required name="riesgos_4_{{$redondeo->id}}" class="separadorMiles" value="{{$redondeo->riesgos_4}}" required /></td>
                            <td><input type="text" required name="riesgos_3_{{$redondeo->id}}" class="separadorMiles" value="{{$redondeo->riesgos_3}}" required /></td>
                            <td><input type="text" required name="riesgos_2_{{$redondeo->id}}" class="separadorMiles" value="{{$redondeo->riesgos_2}}" required /></td>
                            <td><input type="text" required name="riesgos_1_{{$redondeo->id}}" class="separadorMiles" value="{{$redondeo->riesgos_1}}" required /></td>
                            <td><input type="text" required name="sena_0_5_{{$redondeo->id}}" class="separadorMiles" value="{{$redondeo->sena_0_5}}" required /></td>
                            <td><input type="text" required name="sena_2_{{$redondeo->id}}" class="separadorMiles" value="{{$redondeo->sena_2}}" required /></td>
                            <td><input type="text" required name="icbf_{{$redondeo->id}}" class="separadorMiles" value="{{$redondeo->icbf}}" required /></td>
                            <td><input type="text" required name="esap_{{$redondeo->id}}" class="separadorMiles" value="{{$redondeo->esap}}" required /></td>
                            <td><input type="text" required name="men_{{$redondeo->id}}" class="separadorMiles" value="{{$redondeo->men}}" required /></td>
                        </tr>
                    @endforeach
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
                            console.log("error");
                        }
                    }
                });
            });
        });
    </script>
@endsection
