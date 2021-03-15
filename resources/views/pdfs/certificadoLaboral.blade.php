<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>PDF</title>
    <style>
        @font-face {
            font-family: 'ArialNarrow';
            font-style: normal;
            font-weight: normal;
            src: url(/fonts/ARIALNB.TTF) format('truetype');
        }

        @page {
            margin: 0cm 0cm;
            position: absolute;
        }

        * {
            font-family: 'ArialNarrow', sans-serif;
            font-size: 13.3px;
        }

        body {
            margin: 0;
            font-family: sans-serif;
            font-size: 12px;
        }
        
        .page_break { 
            page-break-before: always; 
        }
    </style>
</head>
<body style = "max-width: 800px; width: 100%; margin: auto;">
    <div style = "width: 670px; margin: auto;">
        {{ $fechaCarta }}
        <br><br><br>
        <p>
            Señores<br>
            <b>A QUIEN INTERESE</b><br>
            Bogotá D.C.
        </p>
        <br>
        <p style = "text-align: right">
            <b>Ref.</b> Certificación Laboral GESTION SERVICIOS TEMPORALES SAS.
        </p>
        <br>
        <p>Estimados señores,</p>
        <p style = "text-align: justify;">
            Nos permitimos certificar que el(la) señor(a) <b>{{ $dataEmpleado->primerNombre }} {{ $dataEmpleado->segundoNombre ?? '' }} {{ $dataEmpleado->primerApellido }} {{ $dataEmpleado->segundoApellido ?? '' }}</b>, identificado(a) con cédula de
            ciudadanía <b>{{ $dataEmpleado->numeroIdentificacion }}</b> está(estuvo) vinculado(a) a <b>GESTION SERVICIOS TEMPORALES SAS NIT 900.994.075-1</b> con
            contrato en misión por el término que dure la obra o labor en la empresa usuaria <b>{{ $dataEmpleado->razonSocial }}</b> durante los siguientes periodos:
        </p>
        @foreach($empresasEmpleado as $emp)
            <p>
                Nombre de cargo: {{ $emp->nombreCargo }}<br>
                Fecha Inicio Contrato: {{ $emp->fechaInicio }}<br>
                Fecha Fin Contrato: {{ $emp->fechaFin ?? 'Actual' }}<br>
                Salario: {{ number_format($emp->sueldoConceptoFijo ?? $emp->sueldoPeriodo, '2', ".", ".") }}<br>
            </p>
        @endforeach
        <p style = "text-align: justify;">
            La información de la presente certificación contiene una firma digitalizada válida para todos sus efectos de
            conformidad con lo dispuesto en la Ley 527 de 1999 y debe ser confirmada telefónicamente al número +57 (1) 309
            94 63 Ext. 112; para más información visité www. gestionth.com
        </p>
        <p><b>#AvanzandoJuntos,</b></p>
        <p>
            <b>GESTION SERVICIOS TEMPORALES SAS</b><br>
            Gestión Humana
        </p>
        <p>
            <b>GESTION SAS | #AvanzandoJuntos</b><br>
            CR 28c # 85 - 25 Bogotá, D.C. CO<br>
            +57 (1) 309 94 63<br>
            contacto@gestionth.com<br>
            <a style = "text-decoration: none; color: black;" href = "https://gestionth.com">www.gestionth.com</a>
        </p>
    </div>
</body>
</html>