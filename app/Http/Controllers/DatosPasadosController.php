<?php

namespace App\Http\Controllers;

use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;

class DatosPasadosController extends Controller
{
    
    public function index(Request $req){
        $cargasDatosPasados = DB::table("carga_datos_pasados","cdp")
        ->join("estado as e", "e.idEstado", "=", "cdp.fkEstado")
        ->orderBy("cdp.idCargaDatosPasados", "desc")
        ->get();

        return view('/datosPasados.index', ["cargasDatosPasados" => $cargasDatosPasados]);
    }
    public function subirArchivo(Request $req){
    
        $csvDatosPasados = $req->file("archivoCSV");
        


        
        $reader = Reader::createFromFileObject($csvDatosPasados->openFile());
        $reader->setDelimiter(';');
        $csvDatosPasados = $csvDatosPasados->store("public/csvFiles");

        
        $idCargaDatosPasados  = DB::table("carga_datos_pasados")->insertGetId([
            "rutaArchivo" => $csvDatosPasados,
            "fkEstado" => "3",
            "numActual" => 0,
            "numRegistros" => sizeof($reader)
        ], "idCargaDatosPasados");

        return redirect('datosPasados/verCarga/'.$idCargaDatosPasados);

    }

    public function verCarga($idCarga){
        $cargasDatosPasados = DB::table("carga_datos_pasados","cdp")
        ->join("estado as e", "e.idEstado", "=", "cdp.fkEstado")
        ->where("cdp.idCargaDatosPasados","=",$idCarga)
        ->first();
        
        $datosPasados = DB::table("datos_pasados","dp")
        ->select("dp.*","c.nombre as nombreConcepto", "est.nombre as estado","dp2.*")
        ->join("empleado as e","e.idempleado", "=","dp.fkEmpleado", "left")
        ->join("datospersonales as dp2","dp2.idDatosPersonales", "=","e.fkDatosPersonales", "left")
        ->join("concepto as c","c.idconcepto", "=","dp.fkConcepto", "left")
        ->join("estado as est", "est.idEstado", "=", "dp.fkEstado")
        ->where("dp.fkCargaDatosPasados","=",$idCarga)
        ->get();
        
        

        return view('/datosPasados.verCarga', [
            "cargaDatoPasado" => $cargasDatosPasados,
            "datosPasados" => $datosPasados
        ]);

    }
    public function subir($idCarga){
        $cargaDatos = DB::table("carga_datos_pasados","cdp")
        ->where("cdp.idCargaDatosPasados","=",$idCarga)
        ->where("cdp.fkEstado","=","3")
        ->first();
        if(isset($cargaDatos)){
            $contents = Storage::get($cargaDatos->rutaArchivo);
            
            $reader = Reader::createFromString($contents);
            $reader->setDelimiter(';');
            // Create a customer from each row in the CSV file
            $datosSubidos = 0; 
           
           
            for($i = $cargaDatos->numActual; $i <= $cargaDatos->numRegistros; $i++){
                
                $row = $reader->fetchOne($i);
                $vacios = 0;
                foreach($row as $key =>$valor){
                    
                    if($valor==""){
                        $row[$key]=null;
                        $vacios++;
                    }
                    else{
                        $row[$key] = utf8_encode($row[$key]);
                        if(strpos($row[$key], "/")){
                            
                            $dt = DateTime::createFromFormat("d/m/Y", $row[$key]);
                            if($dt === false){
                                $dt = new DateTime();
                            }
                            $ts = $dt->getTimestamp();
                            $row[$key] = date("Y-m-d", $ts);
                        }
                    }
                }
                if($vacios >= 7){
                    continue;
                }
                

                $existeEmpleado = DB::table("empleado","e")
                ->join("datospersonales as dp","dp.idDatosPersonales", "=", "e.fkDatosPersonales")
                ->where("dp.numeroIdentificacion","=", $row[2])
                ->where("dp.fkTipoIdentificacion","=", $row[1])
                ->first();
                $existeConcepto = DB::table("concepto","c")
                ->where("c.idconcepto","=",$row[0])
                ->first();
                    
                $row[5] = floatval($row[5]);
                if(isset($existeConcepto) && isset($existeEmpleado)){
                    DB::table("datos_pasados")->insert([
                        "fkConcepto" => $row[0],
                        "fkEmpleado" => $existeEmpleado->idempleado,
                        "fecha" => $row[3],
                        "valor" => $row[4],
                        "cantidad" => $row[5],
                        "tipoUnidad" => $row[6],
                        "fkCargaDatosPasados" => $idCarga,
                        "fkEstado" => "3"
                    ]);    
                }
                else if(isset($existeConcepto)){
                    DB::table("datos_pasados")->insert([
                        "fkConcepto" => $row[0],
                        "fecha" => $row[3],
                        "valor" => $row[4],
                        "cantidad" => $row[5],
                        "tipoUnidad" => $row[6],
                        "fkCargaDatosPasados" => $idCarga,
                        "fkEstado" => "12"
                    ]);     
                }
                else if(isset($existeEmpleado)){
                    DB::table("datos_pasados")->insert([
                        "fkEmpleado" => $existeEmpleado->idempleado,
                        "fecha" => $row[3],
                        "valor" => $row[4],
                        "cantidad" => $row[5],
                        "tipoUnidad" => $row[6],
                        "fkCargaDatosPasados" => $idCarga,
                        "fkEstado" => "13"
                    ]);
                }
                else{
                    DB::table("datos_pasados")->insert([
                        "fecha" => $row[3],
                        "valor" => $row[4],
                        "cantidad" => $row[5],
                        "tipoUnidad" => $row[6],
                        "fkCargaDatosPasados" => $idCarga,
                        "fkEstado" => "14"
                    ]);
                }
                $datosSubidos++;
                
                if($datosSubidos == 3){
                    DB::table("carga_datos_pasados")
                    ->where("idCargaDatosPasados","=",$idCarga)
                    ->update(["numActual" => ($i+1)]);

                    $datosPasados = DB::table("datos_pasados","dp")
                    ->select("dp.*","c.nombre as nombreConcepto", "est.nombre as estado","dp2.*")
                    ->join("empleado as e","e.idempleado", "=","dp.fkEmpleado", "left")
                    ->join("datospersonales as dp2","dp2.idDatosPersonales", "=","e.fkDatosPersonales", "left")
                    ->join("concepto as c","c.idconcepto", "=","dp.fkConcepto", "left")
                    ->join("estado as est", "est.idEstado", "=", "dp.fkEstado")
                    ->where("dp.fkCargaDatosPasados","=",$idCarga)
                    ->get();
                    $mensaje = "";

                    foreach($datosPasados as $index => $datoPasado){
                        $mensaje.='<tr>
                            <th></th>
                            <td>'.($index + 1).'</td>
                            <td>'.$datoPasado->numeroIdentificacion.'</td>
                            <td>'.$datoPasado->primerApellido.' '.$datoPasado->segundoApellido.' '.$datoPasado->primerNombre.' '.$datoPasado->segundoNombre.'</td>
                            <td>'.$datoPasado->nombreConcepto.'</td>
                            <td>'.$datoPasado->fecha.'</td>
                            <td>'.$datoPasado->cantidad.'</td>
                            <td>'.$datoPasado->tipoUnidad.'</td>
                            <td>$ '.number_format($datoPasado->valor,0, ",", ".").'</td>
                            <td>'.$datoPasado->estado.'</td>
                        </tr>';
                    }
                    return response()->json([
                        "success" => true,
                        "seguirSubiendo" => true,
                        "numActual" =>  ($i),
                        "mensaje" => $mensaje,
                        "porcentaje" => ceil(($i / $cargaDatos->numRegistros)*100)."%"
                    ]);
                }


                
            }
            
                        
            if($datosSubidos!=0){
                
                $existeEmpleado = DB::table("empleado","e")
                ->join("datospersonales as dp","dp.idDatosPersonales", "=", "e.fkDatosPersonales")
                ->where("dp.numeroIdentificacion","=", $row[2])
                ->where("dp.fkTipoIdentificacion","=", $row[1])
                ->first();
                $existeConcepto = DB::table("concepto","c")
                ->where("c.idconcepto","=",$row[0])
                ->first();
                    
                if(isset($existeConcepto) && isset($existeEmpleado)){
                    DB::table("datos_pasados")->insert([
                        "fkConcepto" => $row[0],
                        "fkEmpleado" => $existeEmpleado->idempleado,
                        "fecha" => $row[3],
                        "valor" => $row[4],
                        "cantidad" => $row[5],
                        "tipoUnidad" => $row[6],
                        "fkCargaDatosPasados" => $idCarga,
                        "fkEstado" => "3"
                    ]);    
                }
                else if(isset($existeConcepto)){
                    DB::table("datos_pasados")->insert([
                        "fkConcepto" => $row[0],
                        "fecha" => $row[3],
                        "valor" => $row[4],
                        "cantidad" => $row[5],
                        "tipoUnidad" => $row[6],
                        "fkCargaDatosPasados" => $idCarga,
                        "fkEstado" => "12"
                    ]);     
                }
                else if(isset($existeEmpleado)){
                    DB::table("datos_pasados")->insert([
                        "fkEmpleado" => $existeEmpleado->idempleado,
                        "fecha" => $row[3],
                        "valor" => $row[4],
                        "cantidad" => $row[5],
                        "tipoUnidad" => $row[6],
                        "fkCargaDatosPasados" => $idCarga,
                        "fkEstado" => "13"
                    ]);
                }
                else{
                    DB::table("datos_pasados")->insert([
                        "fecha" => $row[3],
                        "valor" => $row[4],
                        "cantidad" => $row[5],
                        "tipoUnidad" => $row[6],
                        "fkCargaDatosPasados" => $idCarga,
                        "fkEstado" => "14"
                    ]);
                }
                DB::table("carga_datos_pasados")
                ->where("idCargaDatosPasados","=",$idCarga)
                ->update(["numActual" => ($cargaDatos->numRegistros),"fkEstado" => "15"]);

            }  
            $datosPasados = DB::table("datos_pasados","dp")
            ->select("dp.*","c.nombre as nombreConcepto", "est.nombre as estado","dp2.*")
            ->join("empleado as e","e.idempleado", "=","dp.fkEmpleado", "left")
            ->join("datospersonales as dp2","dp2.idDatosPersonales", "=","e.idempleado", "left")
            ->join("concepto as c","c.idconcepto", "=","dp.fkConcepto", "left")
            ->join("estado as est", "est.idEstado", "=", "dp.fkEstado")
            ->where("dp.fkCargaDatosPasados","=",$idCarga)
            ->get();
            $mensaje = "";

            foreach($datosPasados as $index => $datoPasado){
                $mensaje.='<tr>
                    <th>'.((isset($datoPasado->primerApellido) && isset($datoPasado->nombreConcepto)) ? '<input type="checkbox" name="idDatosPasados[]" value="'.$datoPasado->idDatosPasados.'" />' : '' ).'</th>
                    <td>'.($index + 1).'</td>
                    <td>'.$datoPasado->numeroIdentificacion.'</td>
                    <td>'.$datoPasado->primerApellido.' '.$datoPasado->segundoApellido.' '.$datoPasado->primerNombre.' '.$datoPasado->segundoNombre.'</td>
                    <td>'.$datoPasado->nombreConcepto.'</td>
                    <td>'.$datoPasado->fecha.'</td>
                    <td>'.$datoPasado->cantidad.'</td>
                    <td>'.$datoPasado->tipoUnidad.'</td>
                    <td>$ '.number_format($datoPasado->valor,0, ",", ".").'</td>
                    <td>'.$datoPasado->estado.'</td>
                </tr>';
            }
            
            return response()->json([
                "success" => true,
                "seguirSubiendo" => false,
                "numActual" => $cargaDatos->numRegistros,
                "mensaje" => $mensaje,
                "porcentaje" => "100%"

            ]);
                

        }
    }

    public function cancelarCarga($idCarga){
        DB::table("carga_datos_pasados")
        ->where("idCargaDatosPasados","=",$idCarga)
        ->delete();
        return redirect('/datosPasados');
    }
    public function eliminarRegistros(Request $req){

        
        if(isset($req->idDatosPasados)){
            DB::table("datos_pasados")->whereIn("idDatosPasados",$req->idDatosPasados)->delete();
        }
        
        return redirect('/datosPasados/verCarga/'.$req->idCargaDatosPasados);
    }
    public function aprobarCarga($idCarga){
        $datosPasados = DB::table("datos_pasados","dp")
        ->join("empleado as e", "e.idempleado", "=", "dp.fkEmpleado")
        ->where("dp.fkCargaDatosPasados","=",$idCarga)
        ->where("dp.fkEstado","=","3")
        ->orderBy("dp.fecha")
        ->orderBy("dp.fkEmpleado")
        ->orderBy("e.fkNomina")
        ->orderBy("dp.fkConcepto")
        ->get();
        $mes = 0;
        $anio = 0;
        $empleado = 0;
        $nomina = 0;
        $boucherId = 0;
        $liquidacionId=0;
        $liquidacionIdAnt = 0;
        foreach($datosPasados as $datoPasado){

            

            if($mes != date("m",strtotime($datoPasado->fecha)) || $anio != date("Y",strtotime($datoPasado->fecha)) || $nomina != $datoPasado->fkNomina){
                $mes = date("m",strtotime($datoPasado->fecha));
                $anio = date("Y",strtotime($datoPasado->fecha));
                $nomina = $datoPasado->fkNomina;
                $liquidacionId = DB::table("liquidacionnomina")
                ->insertGetId([
                    "fechaLiquida" => date("Y-m-t",strtotime($datoPasado->fecha)),
                    "fechaInicio" => date("Y-m-01",strtotime($datoPasado->fecha)),
                    "fechaFin" => date("Y-m-t",strtotime($datoPasado->fecha)),
                    "fechaProximaInicio" => date("Y-m-01",strtotime($datoPasado->fecha." +1 month")),
                    "fechaProximaFin" => date("Y-m-01",strtotime($datoPasado->fecha." +1 month")),
                    "fkTipoLiquidacion" => "8",
                    "fkNomina" => $nomina,
                    "fkEstado" => "5",
                    "fkCargaDatosPasados" => $idCarga
                ],"idLiquidacionNomina");

            }

            if($empleado != $datoPasado->fkEmpleado || $liquidacionIdAnt != $liquidacionId){
                $liquidacionIdAnt = $liquidacionId;
                $empleado = $datoPasado->fkEmpleado;
                $periodo = 0;
                $salario = 0;
                $netoPagar = 0;
                foreach($datosPasados as $datoPasado2){
                    if( $empleado == $datoPasado2->fkEmpleado && date("m",strtotime($datoPasado2->fecha)) == $mes 
                    && date("Y",strtotime($datoPasado2->fecha)) == $anio){
                        if ($datoPasado2->fkConcepto == "1" || $datoPasado2->fkConcepto == "2")
                        {
                            $periodo = $periodo + $datoPasado2->cantidad;
                            $salario = $salario + $datoPasado2->valor;
                        }
                        $netoPagar = $netoPagar + $datoPasado2->valor;
                    }
                }
                if($periodo != 0){
                    $salario = ($salario / $periodo)*30;
                }
                else{
                    $salario = 0;
                }
                
                $boucherId = DB::table("boucherpago")->insertGetId([
                    "fkEmpleado" => $datoPasado->fkEmpleado,
                    "fkLiquidacion" => $liquidacionId,
                    "periodoPago" => $periodo,
                    "diasTrabajados" => $periodo,
                    "salarioPeriodoPago" => $salario,
                    "netoPagar" => $netoPagar
                ], "idBoucherPago");
            }

            $pago = 0;
            $descuento = 0;
            if($datoPasado->valor>0){
                $pago = $datoPasado->valor;
            }
            else{
                $descuento = $datoPasado->valor*-1;
            }

            DB::table("item_boucher_pago")->insert([
                "fkBoucherPago" => $boucherId,
                "fkConcepto" => $datoPasado->fkConcepto,
                "pago" => $pago,
                "descuento" => $descuento,
                "cantidad" => $datoPasado->cantidad,
                "tipoUnidad" => $datoPasado->tipoUnidad,
                "valor" => $datoPasado->valor                
            ]);
            DB::table("datos_pasados")
                ->where("idDatosPasados","=",$datoPasado->idDatosPasados)
                ->update(["fkEstado" => "11"]);

        }
        DB::table("carga_datos_pasados")
        ->where("idCargaDatosPasados","=",$idCarga)
        ->update(["fkEstado" => "11"]);

        return redirect('/datosPasados');
    }



    public function indexVac(Request $req){
        $cargasDatosPasados = DB::table("carga_datos_pasados_vac","cdp")
        ->join("estado as e", "e.idEstado", "=", "cdp.fkEstado")
        ->orderBy("cdp.idCargaDatosPasados", "desc")
        ->get();

        return view('/datosPasadosVac.index', ["cargasDatosPasados" => $cargasDatosPasados]);
    }
    public function subirArchivoVac(Request $req){
    
        $csvDatosPasados = $req->file("archivoCSV");
        


        
        $reader = Reader::createFromFileObject($csvDatosPasados->openFile());
        $reader->setDelimiter(';');
        $csvDatosPasados = $csvDatosPasados->store("public/csvFiles");

        
        $idCargaDatosPasados  = DB::table("carga_datos_pasados_vac")->insertGetId([
            "rutaArchivo" => $csvDatosPasados,
            "fkEstado" => "3",
            "numActual" => 0,
            "numRegistros" => sizeof($reader)
        ], "idCargaDatosPasados");

        return redirect('datosPasadosVac/verCarga/'.$idCargaDatosPasados);

    }

    public function verCargaVac($idCarga){
        $cargasDatosPasados = DB::table("carga_datos_pasados_vac","cdp")
        ->join("estado as e", "e.idEstado", "=", "cdp.fkEstado")
        ->where("cdp.idCargaDatosPasados","=",$idCarga)
        ->first();
        
        $datosPasados = DB::table("datos_pasados_vac","dp")
        ->select("dp.*", "est.nombre as estado","dp2.*")
        ->join("empleado as e","e.idempleado", "=","dp.fkEmpleado", "left")
        ->join("datospersonales as dp2","dp2.idDatosPersonales", "=","e.fkDatosPersonales", "left")
        ->join("estado as est", "est.idEstado", "=", "dp.fkEstado")
        ->where("dp.fkCargaDatosPasados","=",$idCarga)
        ->get();
        
        

        return view('/datosPasadosVac.verCarga', [
            "cargaDatoPasado" => $cargasDatosPasados,
            "datosPasados" => $datosPasados
        ]);

    }
    public function subirVac($idCarga){
        $cargaDatos = DB::table("carga_datos_pasados_vac","cdp")
        ->where("cdp.idCargaDatosPasados","=",$idCarga)
        ->where("cdp.fkEstado","=","3")
        ->first();
        if(isset($cargaDatos)){
            $contents = Storage::get($cargaDatos->rutaArchivo);
            
            $reader = Reader::createFromString($contents);
            $reader->setDelimiter(';');
            // Create a customer from each row in the CSV file
            $datosSubidos = 0; 
           
           
            for($i = $cargaDatos->numActual; $i < $cargaDatos->numRegistros; $i++){
                
                $row = $reader->fetchOne($i);
                $vacios = 0;
                foreach($row as $key =>$valor){
                    
                    if($valor==""){
                        $row[$key]=null;
                        $vacios++;
                    }
                    else{
                        $row[$key] = utf8_encode($row[$key]);
                        if(strpos($row[$key], "/")){
                            
                            $dt = DateTime::createFromFormat("d/m/Y", $row[$key]);
                            if($dt === false){
                                $dt = new DateTime();
                            }
                            $ts = $dt->getTimestamp();
                            $row[$key] = date("Y-m-d", $ts);
                        }
                    }
                }
                if($vacios >= 5){
                    continue;
                }
                

                $existeEmpleado = DB::table("empleado","e")
                ->join("datospersonales as dp","dp.idDatosPersonales", "=", "e.fkDatosPersonales")
                ->where("dp.numeroIdentificacion","=", $row[1])
                ->where("dp.fkTipoIdentificacion","=", $row[0])
                ->first();
           
                    
                $row[5] = floatval($row[5]);
                if(isset($existeEmpleado)){
                    DB::table("datos_pasados_vac")->insert([
                        "fkEmpleado" => $existeEmpleado->idempleado,
                        "fecha" => $row[2],
                        "fechaInicial" => $row[3],
                        "fechaFinal" => $row[4],
                        "dias" => $row[5],
                        "fkCargaDatosPasados" => $idCarga,
                        "fkEstado" => "3"
                    ]);
                }
                else{
                    DB::table("datos_pasados_vac")->insert([
                        "fecha" => $row[2],
                        "fechaInicial" => $row[3],
                        "fechaFinal" => $row[4],
                        "dias" => $row[5],
                        "fkCargaDatosPasados" => $idCarga,
                        "fkEstado" => "14"
                    ]);
                }
                $datosSubidos++;
                
                if($datosSubidos == 3){
                    if($cargaDatos->numRegistros == 3){
                        DB::table("carga_datos_pasados_vac")
                        ->where("idCargaDatosPasados","=",$idCarga)
                        ->update(["numActual" => ($cargaDatos->numRegistros),"fkEstado" => "15"]);
                    }
                    else{
                        DB::table("carga_datos_pasados_vac")
                        ->where("idCargaDatosPasados","=",$idCarga)
                        ->update(["numActual" => ($i+1)]);
                    }
                


                    

                    $datosPasados = DB::table("datos_pasados_vac","dp")
                    ->select("dp.*","est.nombre as estado","dp2.*")
                    ->join("empleado as e","e.idempleado", "=","dp.fkEmpleado", "left")
                    ->join("datospersonales as dp2","dp2.idDatosPersonales", "=","e.fkDatosPersonales", "left")
                    ->join("estado as est", "est.idEstado", "=", "dp.fkEstado")
                    ->where("dp.fkCargaDatosPasados","=",$idCarga)
                    ->get();
                    $mensaje = "";

                    foreach($datosPasados as $index => $datoPasado){
                        $mensaje.='<tr>
                            <th></th>
                            <td>'.($index + 1).'</td>
                            <td>'.$datoPasado->numeroIdentificacion.'</td>
                            <td>'.$datoPasado->primerApellido.' '.$datoPasado->segundoApellido.' '.$datoPasado->primerNombre.' '.$datoPasado->segundoNombre.'</td>
                            <td>'.$datoPasado->fecha.'</td>
                            <td>'.$datoPasado->fechaInicial.'</td>
                            <td>'.$datoPasado->fechaFinal.'</td>
                            <td>'.$datoPasado->dias.'</td>
                            <td>'.$datoPasado->estado.'</td>
                        </tr>';
                    }
                    if($cargaDatos->numRegistros == 3){
                        return response()->json([
                            "success" => true,
                            "seguirSubiendo" => false,
                            "numActual" => $cargaDatos->numRegistros,
                            "mensaje" => $mensaje,
                            "porcentaje" => "100%"
            
                        ]);
                    }
                    else{
                        return response()->json([
                            "success" => true,
                            "seguirSubiendo" => true,
                            "numActual" =>  ($i+1),
                            "mensaje" => $mensaje,
                            "porcentaje" => ceil((($i+1) / $cargaDatos->numRegistros)*100)."%"
                        ]);

                    }
                    
                }


                
            }
            
                        
            if($datosSubidos!=0){
                if($datosSubidos>3){
                    $existeEmpleado = DB::table("empleado","e")
                    ->join("datospersonales as dp","dp.idDatosPersonales", "=", "e.fkDatosPersonales")
                    ->where("dp.numeroIdentificacion","=", $row[1])
                    ->where("dp.fkTipoIdentificacion","=", $row[0])
                    ->first();
            
                        
                    if(isset($existeEmpleado)){
                        DB::table("datos_pasados_vac")->insert([
                            "fkEmpleado" => $existeEmpleado->idempleado,
                            "fecha" => $row[2],
                            "fechaInicial" => $row[3],
                            "fechaFinal" => $row[4],
                            "dias" => $row[5],
                            "fkCargaDatosPasados" => $idCarga,
                            "fkEstado" => "11"
                        ]);
                    }
                    else{
                        DB::table("datos_pasados_vac")->insert([
                            "fecha" => $row[2],
                            "fechaInicial" => $row[3],
                            "fechaFinal" => $row[4],
                            "dias" => $row[5],
                            "fkCargaDatosPasados" => $idCarga,
                            "fkEstado" => "14"
                        ]);
                    }
                }
                DB::table("carga_datos_pasados_vac")
                ->where("idCargaDatosPasados","=",$idCarga)
                ->update(["numActual" => ($cargaDatos->numRegistros),"fkEstado" => "15"]);

            }  
            $datosPasados = DB::table("datos_pasados_vac","dp")
            ->select("dp.*","est.nombre as estado","dp2.*")
            ->join("empleado as e","e.idempleado", "=","dp.fkEmpleado", "left")
            ->join("datospersonales as dp2","dp2.idDatosPersonales", "=","e.idempleado", "left")
            ->join("estado as est", "est.idEstado", "=", "dp.fkEstado")
            ->where("dp.fkCargaDatosPasados","=",$idCarga)
            ->get();
            $mensaje = "";

            foreach($datosPasados as $index => $datoPasado){
                $mensaje.='<tr>
                    <th>'.((isset($datoPasado->primerApellido)) ? '<input type="checkbox" name="idDatosPasados[]" value="'.$datoPasado->idDatosPasados.'" />' : '' ).'</th>
                    <td>'.($index + 1).'</td>
                    <td>'.$datoPasado->numeroIdentificacion.'</td>
                    <td>'.$datoPasado->primerApellido.' '.$datoPasado->segundoApellido.' '.$datoPasado->primerNombre.' '.$datoPasado->segundoNombre.'</td>
                    <td>'.$datoPasado->fecha.'</td>
                    <td>'.$datoPasado->fechaInicial.'</td>
                    <td>'.$datoPasado->fechaFinal.'</td>
                    <td>'.$datoPasado->dias.'</td>
                    <td>'.$datoPasado->estado.'</td>
                </tr>';
            }
            
            return response()->json([
                "success" => true,
                "seguirSubiendo" => false,
                "numActual" => $cargaDatos->numRegistros,
                "mensaje" => $mensaje,
                "porcentaje" => "100%"

            ]);
                

        }
    }

    public function cancelarCargaVac($idCarga){
        DB::table("carga_datos_pasados_vac")
        ->where("idCargaDatosPasados","=",$idCarga)
        ->delete();
        return redirect('/datosPasadosVac');
    }
    public function eliminarRegistrosVac(Request $req){

        
        if(isset($req->idDatosPasados)){
            DB::table("datos_pasados_vac")->whereIn("idDatosPasados",$req->idDatosPasados)->delete();
        }
        
        return redirect('/datosPasadosVac/verCarga/'.$req->idCargaDatosPasados);
    }
    public function aprobarCargaVac($idCarga){
        $datosPasados = DB::table("datos_pasados_vac","dp")
        ->join("empleado as e", "e.idempleado", "=", "dp.fkEmpleado")
        ->where("dp.fkCargaDatosPasados","=",$idCarga)
        ->where("dp.fkEstado","=","3")
        ->orderBy("dp.fecha")
        ->orderBy("dp.fkEmpleado")
        ->orderBy("e.fkNomina")
        ->get();

        foreach($datosPasados as $datoPasado){            

            $arrInsertVac = [
                "fechaInicio" => $datoPasado->fechaInicial,
                "fechaFin" => $datoPasado->fechaFinal,
                "diasCompensar" => $datoPasado->dias,
                "pagoAnticipado" => "1"
            ];
            $idVacaciones = DB::table("vacaciones")->insertGetId($arrInsertVac, "idVacaciones");
      


            $arrInsertNovedad =[
                "fkTipoNovedad" => 6,
                "fkNomina" => $datoPasado->fkNomina,
                "fkEmpleado" => $datoPasado->fkEmpleado,
                "fkEstado" => "8",
                "fechaRegistro" => $datoPasado->fecha,
                "fkConcepto" => "29",
                "fkVacaciones" => $idVacaciones,
                "fkCargaDatosPasadosVac" => $idCarga
            ];
            DB::table("novedad")->insert($arrInsertNovedad);
            DB::table("datos_pasados_vac")
                ->where("idDatosPasados","=",$datoPasado->idDatosPasados)
                ->update(["fkEstado" => "11"]);

        }
        DB::table("carga_datos_pasados_vac")
        ->where("idCargaDatosPasados","=",$idCarga)
        ->update(["fkEstado" => "11"]);

        return redirect('/datosPasadosVac');
    }


    public function indexSal(Request $req){
        $cargasDatosPasados = DB::table("carga_datos_pasados_sal","cdp")
        ->join("estado as e", "e.idEstado", "=", "cdp.fkEstado")
        ->orderBy("cdp.idCargaDatosPasados", "desc")
        ->get();

        return view('/datosPasadosSal.index', ["cargasDatosPasados" => $cargasDatosPasados]);
    }
    public function subirArchivoSal(Request $req){
    
        $csvDatosPasados = $req->file("archivoCSV");
        


        
        $reader = Reader::createFromFileObject($csvDatosPasados->openFile());
        $reader->setDelimiter(';');
        $csvDatosPasados = $csvDatosPasados->store("public/csvFiles");

        
        $idCargaDatosPasados  = DB::table("carga_datos_pasados_sal")->insertGetId([
            "rutaArchivo" => $csvDatosPasados,
            "fkEstado" => "3",
            "numActual" => 0,
            "numRegistros" => sizeof($reader)
        ], "idCargaDatosPasados");

        return redirect('datosPasadosSal/verCarga/'.$idCargaDatosPasados);

    }

    public function verCargaSal($idCarga){
        $cargasDatosPasados = DB::table("carga_datos_pasados_sal","cdp")
        ->join("estado as e", "e.idEstado", "=", "cdp.fkEstado")
        ->where("cdp.idCargaDatosPasados","=",$idCarga)
        ->first();
        
        $datosPasados = DB::table("datos_pasados_sal","dp")
        ->select("dp.*","c.nombre as nombreConcepto", "est.nombre as estado","dp2.*")
        ->join("empleado as e","e.idempleado", "=","dp.fkEmpleado", "left")
        ->join("datospersonales as dp2","dp2.idDatosPersonales", "=","e.fkDatosPersonales", "left")
        ->join("concepto as c","c.idconcepto", "=","dp.fkConcepto", "left")
        ->join("estado as est", "est.idEstado", "=", "dp.fkEstado")
        ->where("dp.fkCargaDatosPasados","=",$idCarga)
        ->get();
        
        

        return view('/datosPasadosSal.verCarga', [
            "cargaDatoPasado" => $cargasDatosPasados,
            "datosPasados" => $datosPasados
        ]);

    }
    public function subirSal($idCarga){
        $cargaDatos = DB::table("carga_datos_pasados_sal","cdp")
        ->where("cdp.idCargaDatosPasados","=",$idCarga)
        ->where("cdp.fkEstado","=","3")
        ->first();
        if(isset($cargaDatos)){
            $contents = Storage::get($cargaDatos->rutaArchivo);
            
            $reader = Reader::createFromString($contents);
            $reader->setDelimiter(';');
            // Create a customer from each row in the CSV file
            $datosSubidos = 0; 
           
           
            for($i = $cargaDatos->numActual; $i < $cargaDatos->numRegistros; $i++){
                
                $row = $reader->fetchOne($i);
                $vacios = 0;
                foreach($row as $key =>$valor){
                    
                    if($valor==""){
                        $row[$key]=null;
                        $vacios++;
                    }
                    else{
                        $row[$key] = utf8_encode($row[$key]);
                        if(strpos($row[$key], "/")){
                            
                            $dt = DateTime::createFromFormat("d/m/Y", $row[$key]);
                            if($dt === false){
                                $dt = new DateTime();
                            }
                            $ts = $dt->getTimestamp();
                            $row[$key] = date("Y-m-d", $ts);
                        }
                    }
                }
                if($vacios >= 5){
                    continue;
                }
                $fkConcepto = 0;
                if($row[2]=="PRIMA"){
                    $fkConcepto = 73;
                }
                else if($row[2]=="CESANTIAS"){
                    $fkConcepto = 71;
                }
                else if($row[2]=="INT_CES"){
                    $fkConcepto = 72;
                }

                $existeConcepto = DB::table("concepto","c")
                ->where("c.idconcepto","=",$fkConcepto)
                ->first();

                $existeEmpleado = DB::table("empleado","e")
                ->join("datospersonales as dp","dp.idDatosPersonales", "=", "e.fkDatosPersonales")
                ->where("dp.numeroIdentificacion","=", $row[1])
                ->where("dp.fkTipoIdentificacion","=", $row[0])
                ->first();
           
                    
              
                if(isset($existeConcepto) && isset($existeEmpleado)){
                    DB::table("datos_pasados_sal")->insert([
                        
                        "fkEmpleado" => $existeEmpleado->idempleado,
                        "fkConcepto" => $fkConcepto,
                        "valor" => $row[3],
                        "mes" => $row[4],
                        "anio" => $row[5],
                        "fkCargaDatosPasados" => $idCarga,
                        "fkEstado" => "3"
                    ]);    
                }
                else if(isset($existeConcepto)){
                    DB::table("datos_pasados")->insert([
                        "fkConcepto" => $fkConcepto,
                        "valor" => $row[3],
                        "mes" => $row[4],
                        "anio" => $row[5],
                        "fkCargaDatosPasados" => $idCarga,
                        "fkEstado" => "12"
                    ]);     
                }
                else if(isset($existeEmpleado)){
                    DB::table("datos_pasados_sal")->insert([                        
                        "fkEmpleado" => $existeEmpleado->idempleado,
                        "valor" => $row[3],
                        "mes" => $row[4],
                        "anio" => $row[5],
                        "fkCargaDatosPasados" => $idCarga,
                        "fkEstado" => "3"
                    ]); 
                }
                else{
                    DB::table("datos_pasados_sal")->insert([
                        "valor" => $row[3],
                        "mes" => $row[4],
                        "anio" => $row[5],
                        "fkCargaDatosPasados" => $idCarga,
                        "fkEstado" => "14"
                    ]);
                }

                $datosSubidos++;
                
                if($datosSubidos == 3){
                    if($cargaDatos->numRegistros == 3){
                        DB::table("carga_datos_pasados_sal")
                        ->where("idCargaDatosPasados","=",$idCarga)
                        ->update(["numActual" => ($cargaDatos->numRegistros),"fkEstado" => "15"]);
                    }
                    else{
                        DB::table("carga_datos_pasados_sal")
                        ->where("idCargaDatosPasados","=",$idCarga)
                        ->update(["numActual" => ($i+1)]);
                    }
                


                    

                    $datosPasados = DB::table("datos_pasados_sal","dp")
                    ->select("dp.*","c.nombre as nombreConcepto", "est.nombre as estado","dp2.*")
                    ->join("empleado as e","e.idempleado", "=","dp.fkEmpleado", "left")
                    ->join("datospersonales as dp2","dp2.idDatosPersonales", "=","e.fkDatosPersonales", "left")
                    ->join("concepto as c","c.idconcepto", "=","dp.fkConcepto", "left")
                    ->join("estado as est", "est.idEstado", "=", "dp.fkEstado")
                    ->where("dp.fkCargaDatosPasados","=",$idCarga)
                    ->get();
                    $mensaje = "";

                    foreach($datosPasados as $index => $datoPasado){
                        $mensaje.='<tr>
                            <th></th>
                            <td>'.($index + 1).'</td>
                            <td>'.$datoPasado->numeroIdentificacion.'</td>
                            <td>'.$datoPasado->primerApellido.' '.$datoPasado->segundoApellido.' '.$datoPasado->primerNombre.' '.$datoPasado->segundoNombre.'</td>
                            <td>'.$datoPasado->nombreConcepto.'</td>
                            <td>$'.number_format($datoPasado->valor,0, ",", ".").'</td>
                            <td>'.$datoPasado->mes.'</td>
                            <td>'.$datoPasado->anio.'</td>
                            <td>'.$datoPasado->estado.'</td>
                        </tr>';
                    }
                    if($cargaDatos->numRegistros == 3){
                        return response()->json([
                            "success" => true,
                            "seguirSubiendo" => false,
                            "numActual" => $cargaDatos->numRegistros,
                            "mensaje" => $mensaje,
                            "porcentaje" => "100%"
            
                        ]);
                    }
                    else{
                        return response()->json([
                            "success" => true,
                            "seguirSubiendo" => true,
                            "numActual" =>  ($i+1),
                            "mensaje" => $mensaje,
                            "porcentaje" => ceil((($i+1) / $cargaDatos->numRegistros)*100)."%"
                        ]);

                    }
                    
                }


                
            }
            
                        
            if($datosSubidos!=0){
                
                $existeEmpleado = DB::table("empleado","e")
                ->join("datospersonales as dp","dp.idDatosPersonales", "=", "e.fkDatosPersonales")
                ->where("dp.numeroIdentificacion","=", $row[1])
                ->where("dp.fkTipoIdentificacion","=", $row[0])
                ->first();
           
                    
                if(isset($existeConcepto) && isset($existeEmpleado)){
                    DB::table("datos_pasados_sal")->insert([
                        
                        "fkEmpleado" => $existeEmpleado->idempleado,
                        "fkConcepto" => $fkConcepto,
                        "valor" => $row[3],
                        "mes" => $row[4],
                        "anio" => $row[5],
                        "fkCargaDatosPasados" => $idCarga,
                        "fkEstado" => "3"
                    ]);    
                }
                else if(isset($existeConcepto)){
                    DB::table("datos_pasados")->insert([
                        "fkConcepto" => $fkConcepto,
                        "valor" => $row[3],
                        "mes" => $row[4],
                        "anio" => $row[5],
                        "fkCargaDatosPasados" => $idCarga,
                        "fkEstado" => "12"
                    ]);     
                }
                else if(isset($existeEmpleado)){
                    DB::table("datos_pasados_sal")->insert([                        
                        "fkEmpleado" => $existeEmpleado->idempleado,
                        "valor" => $row[3],
                        "mes" => $row[4],
                        "anio" => $row[5],
                        "fkCargaDatosPasados" => $idCarga,
                        "fkEstado" => "3"
                    ]); 
                }
                else{
                    DB::table("datos_pasados_sal")->insert([
                        "valor" => $row[3],
                        "mes" => $row[4],
                        "anio" => $row[5],
                        "fkCargaDatosPasados" => $idCarga,
                        "fkEstado" => "14"
                    ]);
                }
                DB::table("carga_datos_pasados_sal")
                ->where("idCargaDatosPasados","=",$idCarga)
                ->update(["numActual" => ($cargaDatos->numRegistros),"fkEstado" => "15"]);

            }  
            $datosPasados = DB::table("datos_pasados_sal","dp")
            ->select("dp.*","c.nombre as nombreConcepto", "est.nombre as estado","dp2.*")
            ->join("empleado as e","e.idempleado", "=","dp.fkEmpleado", "left")
            ->join("datospersonales as dp2","dp2.idDatosPersonales", "=","e.fkDatosPersonales", "left")
            ->join("concepto as c","c.idconcepto", "=","dp.fkConcepto", "left")
            ->join("estado as est", "est.idEstado", "=", "dp.fkEstado")
            ->where("dp.fkCargaDatosPasados","=",$idCarga)
            ->get();
            $mensaje = "";

            foreach($datosPasados as $index => $datoPasado){
                $mensaje.='<tr>
                    <th>'.((isset($datoPasado->primerApellido)) ? '<input type="checkbox" name="idDatosPasados[]" value="'.$datoPasado->idDatosPasados.'" />' : '' ).'</th>
                    <td>'.($index + 1).'</td>
                    <td>'.$datoPasado->numeroIdentificacion.'</td>
                    <td>'.$datoPasado->primerApellido.' '.$datoPasado->segundoApellido.' '.$datoPasado->primerNombre.' '.$datoPasado->segundoNombre.'</td>
                    <td>'.$datoPasado->nombreConcepto.'</td>
                    <td>$'.number_format($datoPasado->valor,0, ",", ".").'</td>
                    <td>'.$datoPasado->mes.'</td>
                    <td>'.$datoPasado->anio.'</td>
                    <td>'.$datoPasado->estado.'</td>
                </tr>';
            }
            
            return response()->json([
                "success" => true,
                "seguirSubiendo" => false,
                "numActual" => $cargaDatos->numRegistros,
                "mensaje" => $mensaje,
                "porcentaje" => "100%"

            ]);
                

        }
    }

    public function cancelarCargaSal($idCarga){
        DB::table("carga_datos_pasados_sal")
        ->where("idCargaDatosPasados","=",$idCarga)
        ->delete();
        return redirect('/datosPasadosSal');
    }
    public function eliminarRegistrosSal(Request $req){

        
        if(isset($req->idDatosPasados)){
            DB::table("datos_pasados_sal")->whereIn("idDatosPasados",$req->idDatosPasados)->delete();
        }
        
        return redirect('/datosPasadosSal/verCarga/'.$req->idCargaDatosPasados);
    }
    public function aprobarCargaSal($idCarga){
        $datosPasados = DB::table("datos_pasados_sal","dp")
        ->join("empleado as e", "e.idempleado", "=", "dp.fkEmpleado")
        ->where("dp.fkCargaDatosPasados","=",$idCarga)
        ->where("dp.fkEstado","=","3")
        ->orderBy("dp.fkEmpleado")
        ->orderBy("dp.fkConcepto")
        ->orderBy("e.fkNomina")
        ->get();

        foreach($datosPasados as $datoPasado){            

            DB::table("saldo")->insert([
                "fkConcepto" => $datoPasado->fkConcepto,
                "fkEmpleado" => $datoPasado->fkEmpleado,
                "valor" => $datoPasado->valor,
                "mesAnterior" => $datoPasado->mes,
                "anioAnterior" => $datoPasado->anio,
                "fkCargaDatosPasados" => $idCarga
            ]);

            DB::table("datos_pasados_sal")
                ->where("idDatosPasados","=",$datoPasado->idDatosPasados)
                ->update(["fkEstado" => "11"]);

        }
        DB::table("carga_datos_pasados_sal")
        ->where("idCargaDatosPasados","=",$idCarga)
        ->update(["fkEstado" => "11"]);

        return redirect('/datosPasadosSal');
    }
}