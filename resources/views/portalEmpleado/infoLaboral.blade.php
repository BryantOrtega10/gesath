<h3 style = "text-align: center;">Información laboral</h3>
<div class="card puntero">
    <div class="card-body">
        @foreach($dataEmple as $empl)
            <table class="table table-bordered table-hover table-sm">
                <tr>
                    <th>Identificación:</th>
                    <td>{{ $empl->numeroIdentificacion}}</td>
                </tr>
                <tr>
                    <th>Nombre completo:</th>
                    <td>{{ $empl->primerNombre }} {{ $empl->segundoNombre }} {{ $empl->primerApellido }} {{ $empl->segundoApellido }}</td>
                </tr>
                <tr>
                    <th>Empresa:</th>
                    <td>{{ $empl->razonSocial}}</td>
                </tr>
                <tr>
                    <th>Centro de costo:</th>
                    <td>{{ $empl->nombre }}</td>
                </tr>
                <tr>
                    <th>Cargo:</th>
                    <td>{{ $empl->nombreCargo }}</td>
                </tr>
                <tr>
                    <th>Fecha de ingreso:</th>
                    <td>{{ $empl->fechaIngreso }}</td>
                </tr>
                <tr>
                    <th>Total salario básico:</th>
                    <td>$ {{ number_format($empl->valor, 0,".",",") }}</td>
                </tr>
                
            </table>
        @endforeach
    </div>
</div>
<h4>Afiliaciones</h4>
<table class = "table table-bordered table-hover table-sm">
    <thead>
        <th>Fecha</th>
        <th>Razón Social</th>
        <th>Tipo</th>
    </thead>
    <tbody>
        @foreach($afiliaciones as $af)
        <tr>
            <td>{{ $af->fechaAfiliacion}}</td>
            <td>{{ $af->razonSocial}}</td>
            <td>{{ $af->nombre}}</td>
        </tr>
        @endforeach
    </tbody>
</table>