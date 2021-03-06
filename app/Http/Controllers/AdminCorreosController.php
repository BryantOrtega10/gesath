<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Swift_Mailer;
use Swift_SmtpTransport;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Mail\ComprobantesPagoMail;
use App\Mail\MensajeGeneralMail;
use App\SMTPConfigModel;

class AdminCorreosController extends Controller
{
    public $arrayCampos = [
        "nombreCompleto" => "__nombreCompleto__",
        "primerNombre" => "__primerNombre__",
        "segundoNombre" => "__segundoNombre__",
        "primerApellido" => "__primerApellido__",
        "segundoApellido" => "__segundoApellido__",
        "numeroIdentificacion" => "__numeroIdentificacion__",
        "tipoidentificacion" => "__tipoIdentificacion__",
        "nombreEmpresa" => "__empresa__",            
        "periodoNomina" => "__periodoNomina__"
    ];
    public function indexEnviarCorreosxLiquidacion($idLiquidacionNomina){
        $enviosxLiquidacion = DB::table("envio_correo_liquidacion")
        ->join("estado as est", "est.idEstado", "=","envio_correo_liquidacion.fkEstado")
        ->where("fkLiquidacion","=",$idLiquidacionNomina)->get();

        $usu = UsuarioController::dataAdminLogueado();
        return view('/envioCorreosLiquidacion.index',[
            "enviosxLiquidacion" => $enviosxLiquidacion,
            "idLiquidacionNomina" => $idLiquidacionNomina,
            "dataUsu" => $usu
        ]);
    }

    public function crearEnvioCorreo(Request $req){

        $bouchers = DB::table('boucherpago',"bp")
        ->where("bp.fkLiquidacion","=",$req->idLiquidacionNomina)
        ->count();


        $idEnvioCorreoLiq = DB::table("envio_correo_liquidacion")->insertGetId([
            "fkLiquidacion" => $req->idLiquidacionNomina,
            "numRegistros" => $bouchers,
            "numActual" => "0"
        ], "idEnvioCorreoLiq");

        return response()->json([
            "success" => true,
            "idEnvioCorreoLiq" => $idEnvioCorreoLiq
        ]);



    }

    public function verEnvioCorreo($idEnvioCorreoLiq){

        $envioxLiquidacion = DB::table("envio_correo_liquidacion")->where("idEnvioCorreoLiq","=",$idEnvioCorreoLiq)->first();
        $empleados = DB::table("empleado","e")
        ->select("dp.*","ti.nombre as tipoidentificacion", "estado.nombre as estado", "iecl.mensaje")
        ->join("datospersonales as dp","dp.idDatosPersonales", "=", "e.fkDatosPersonales")
        ->join("tipoidentificacion as ti","ti.idtipoIdentificacion", "=", "dp.fkTipoIdentificacion")
        ->join("boucherpago as bp", "bp.fkEmpleado","=","e.idempleado")
        ->leftJoin('item_envio_correo_liquidacion as iecl', function($join) use ($idEnvioCorreoLiq){
            $join->on('iecl.fkBoucherPago', '=', 'bp.idBoucherPago')
                ->where('iecl.fkEnvioCorreoLiq', '=', $idEnvioCorreoLiq);
        })
        ->leftJoin("estado","estado.idestado","=","iecl.fkEstado")
        ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
        ->join("envio_correo_liquidacion as ecl","ecl.fkLiquidacion","=","ln.idLiquidacionNomina")
        ->where("ecl.idEnvioCorreoLiq","=",$idEnvioCorreoLiq)->get();

        $usu = UsuarioController::dataAdminLogueado();
        return view('/envioCorreosLiquidacion.verEnvioCorreo', [
            "empleados" => $empleados,
            "envioxLiquidacion" => $envioxLiquidacion,
            "dataUsu" => $usu
        ]);
    }
    public function enviarProximosRegistroReporte($idEnvioCorreoRep){
        $numeroRegistrosAEnviar = 3;
        $envioxReporte = DB::table("envio_correo_reporte")->where("id_envio_correo_reporte","=",$idEnvioCorreoRep)->first();
        $empleadosxReporte = DB::table("item_envio_correo_reporte", "iec")
        ->join("empleado as e","e.idempleado", "=","iec.fkEmpleado")
        ->join("datospersonales as dp","dp.idDatosPersonales", "=", "e.fkDatosPersonales")
        ->where("iec.fkEnvioCoreoReporte", "=",$idEnvioCorreoRep)
        ->offset($envioxReporte->numActual)
        ->limit($numeroRegistrosAEnviar)
        ->get();
        $numActual = $envioxReporte->numActual;

        
        if(sizeof($empleadosxReporte) > 0){
            foreach($empleadosxReporte as $empleadoReporte){
                if($envioxReporte->fkMensaje=="4"){
                    if(isset($empleadoReporte->fkBoucherPago)){
                        $jsonMensaje = $this->enviarCorreoBoucher($empleadoReporte->fkBoucherPago);
                        if($jsonMensaje->original["success"]){
                            DB::table("item_envio_correo_reporte")
                            ->where("id_item_envio_correo_reporte","=",$empleadoReporte->id_item_envio_correo_reporte)
                            ->update([
                                "fkEstado" => "48"
                            ]);
                        }
                        else{
                            DB::table("item_envio_correo_reporte")
                            ->where("id_item_envio_correo_reporte","=",$empleadoReporte->id_item_envio_correo_reporte)
                            ->update([
                                "fkEstado" => "36",
                                "mensaje" => $jsonMensaje->original["error"]
                            ]);
                        }
                        $numActual++;                
                    }
                }
                else{
                    $jsonMensaje = $this->enviarCorreoGeneral($empleadoReporte->fkEmpleado, $envioxReporte->fkMensaje);
                    if($jsonMensaje->original["success"]){
                        DB::table("item_envio_correo_reporte")
                        ->where("id_item_envio_correo_reporte","=",$empleadoReporte->id_item_envio_correo_reporte)
                        ->update([
                            "fkEstado" => "48"
                        ]);
                    }
                    else{
                        DB::table("item_envio_correo_reporte")
                        ->where("id_item_envio_correo_reporte","=",$empleadoReporte->id_item_envio_correo_reporte)
                        ->update([
                            "fkEstado" => "36",
                            "mensaje" => $jsonMensaje->original["error"]
                        ]);
                    }
                    $numActual++;      
                    
                }
            }
            DB::table("envio_correo_reporte")
            ->where("id_envio_correo_reporte","=",$idEnvioCorreoRep)
            ->update([
                "numActual" => $numActual
            ]);
            $mensaje = "";
            $empleados = DB::table("item_envio_correo_reporte", "iec")
            ->select("dp.*","ti.nombre as tipoidentificacion", "est.nombre as estado", "iec.mensaje")
            ->join("empleado as e","e.idempleado", "=","iec.fkEmpleado")
            ->join("datospersonales as dp","dp.idDatosPersonales", "=", "e.fkDatosPersonales")
            ->join("tipoidentificacion as ti","ti.idtipoIdentificacion", "=", "dp.fkTipoIdentificacion")
            ->join("estado as est", "est.idEstado", "=","iec.fkEstado")
            ->where("iec.fkEnvioCoreoReporte", "=",$idEnvioCorreoRep)
            ->get();
            foreach ($empleados as $empleado){
                $mensaje.='<tr>
                    <th scope="row">'.$empleado->tipoidentificacion.' - '. $empleado->numeroIdentificacion .'</th>
                    <td>'. $empleado->primerApellido .' '. $empleado->segundoApellido .' '. $empleado->primerNombre .' '. $empleado->segundoNombre .'</td>
                    <td>'. $empleado->estado .' '. $empleado->mensaje .'</td>
                </tr>';
            }
            return response()->json([
                "success" => true,
                "seguirSubiendo" => true,
                "numActual" =>  ($numActual),
                "mensaje" => $mensaje,
                "porcentaje" => ceil(($numActual / $envioxReporte->numRegistros)*100)."%"
            ]);

          
        }
        else{
            $mensaje = "";
            $empleados = DB::table("item_envio_correo_reporte", "iec")
            ->select("dp.*","ti.nombre as tipoidentificacion", "est.nombre as estado", "iec.mensaje")
            ->join("empleado as e","e.idempleado", "=","iec.fkEmpleado")
            ->join("datospersonales as dp","dp.idDatosPersonales", "=", "e.fkDatosPersonales")
            ->join("tipoidentificacion as ti","ti.idtipoIdentificacion", "=", "dp.fkTipoIdentificacion")
            ->join("estado as est", "est.idEstado", "=","iec.fkEstado")
            ->where("iec.fkEnvioCoreoReporte", "=",$idEnvioCorreoRep)
            ->get();
            foreach ($empleados as $empleado){
                $mensaje.='<tr>
                    <th scope="row">'.$empleado->tipoidentificacion.' - '. $empleado->numeroIdentificacion .'</th>
                    <td>'. $empleado->primerApellido .' '. $empleado->segundoApellido .' '. $empleado->primerNombre .' '. $empleado->segundoNombre .'</td>
                    <td>'. $empleado->estado .' '. $empleado->mensaje .'</td>
                </tr>';
            }
            DB::table("envio_correo_reporte")
            ->where("id_envio_correo_reporte","=",$idEnvioCorreoRep)
            ->update([
                "numActual" => $envioxReporte->numRegistros,
                "fkEstado" => "5"
            ]);
            return response()->json([
                "success" => true,
                "seguirSubiento" => false,
                "mensaje" => $mensaje,
                "numActual" => $envioxReporte->numRegistros,
                "numRegistros" => $envioxReporte->numRegistros
            ]);
        }





    }

    public function enviarProximosRegistro($idEnvioCorreoLiq){
        $numeroRegistrosAEnviar = 3;

        $envioxLiquidacion = DB::table("envio_correo_liquidacion")->where("idEnvioCorreoLiq","=",$idEnvioCorreoLiq)->first();
        $bouchersXLiquidacion  = DB::table("boucherpago", "bp")
        ->where("bp.fkLiquidacion", "=",$envioxLiquidacion->fkLiquidacion)
        ->offset($envioxLiquidacion->numActual)
        ->limit($numeroRegistrosAEnviar)
        ->get();
        $numActual = $envioxLiquidacion->numActual;
        if(sizeof($bouchersXLiquidacion) > 0){
            foreach($bouchersXLiquidacion as $boucher){
                $jsonMensaje = $this->enviarCorreoBoucher($boucher->idBoucherPago);


                if($jsonMensaje->original["success"]){
                    DB::table("item_envio_correo_liquidacion")->insert([
                        "fkBoucherPago" => $boucher->idBoucherPago,
                        "fkEnvioCorreoLiq" => $idEnvioCorreoLiq,
                        "fkEstado" => "48"
                    ]);
                }
                else{
                    DB::table("item_envio_correo_liquidacion")->insert([
                        "fkBoucherPago" => $boucher->idBoucherPago,
                        "fkEnvioCorreoLiq" => $idEnvioCorreoLiq,
                        "fkEstado" => "36",
                        "mensaje" => $jsonMensaje->original["error"]
                    ]);
                }
                $numActual++;                
            }
            DB::table("envio_correo_liquidacion")
            ->where("idEnvioCorreoLiq","=",$idEnvioCorreoLiq)
            ->update([
                "numActual" => $numActual
            ]);

            $empleados = DB::table("empleado","e")
            ->select("dp.*","ti.nombre as tipoidentificacion", "estado.nombre as estado", "iecl.mensaje")
            ->join("datospersonales as dp","dp.idDatosPersonales", "=", "e.fkDatosPersonales")
            ->join("tipoidentificacion as ti","ti.idtipoIdentificacion", "=", "dp.fkTipoIdentificacion")
            ->join("boucherpago as bp", "bp.fkEmpleado","=","e.idempleado")
            ->leftJoin('item_envio_correo_liquidacion as iecl', function($join) use ($idEnvioCorreoLiq){
                $join->on('iecl.fkBoucherPago', '=', 'bp.idBoucherPago')
                    ->where('iecl.fkEnvioCorreoLiq', '=', $idEnvioCorreoLiq);
            })
            ->leftJoin("estado","estado.idestado","=","iecl.fkEstado")
            ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
            ->join("envio_correo_liquidacion as ecl","ecl.fkLiquidacion","=","ln.idLiquidacionNomina")
            ->where("ecl.idEnvioCorreoLiq","=",$idEnvioCorreoLiq)->get();
            $mensaje = "";
            foreach ($empleados as $empleado){
                $mensaje.='<tr>
                    <th scope="row">'.$empleado->tipoidentificacion.' - '. $empleado->numeroIdentificacion .'</th>
                    <td>'. $empleado->primerApellido .' '. $empleado->segundoApellido .' '. $empleado->primerNombre .' '. $empleado->segundoNombre .'</td>
                    <td>'. $empleado->estado .' '. $empleado->mensaje .'</td>
                </tr>';
            }

            return response()->json([
                "success" => true,
                "seguirSubiendo" => true,
                "numActual" =>  ($numActual),
                "mensaje" => $mensaje,
                "porcentaje" => ceil(($numActual / $envioxLiquidacion->numRegistros)*100)."%"
            ]);
        }
        else{
            $empleados = DB::table("empleado","e")
            ->select("dp.*","ti.nombre as tipoidentificacion", "estado.nombre as estado", "iecl.mensaje")
            ->join("datospersonales as dp","dp.idDatosPersonales", "=", "e.fkDatosPersonales")
            ->join("tipoidentificacion as ti","ti.idtipoIdentificacion", "=", "dp.fkTipoIdentificacion")
            ->join("boucherpago as bp", "bp.fkEmpleado","=","e.idempleado")
            ->leftJoin('item_envio_correo_liquidacion as iecl', function($join) use ($idEnvioCorreoLiq){
                $join->on('iecl.fkBoucherPago', '=', 'bp.idBoucherPago')
                    ->where('iecl.fkEnvioCorreoLiq', '=', $idEnvioCorreoLiq);
            })
            ->leftJoin("estado","estado.idestado","=","iecl.fkEstado")
            ->join("liquidacionnomina as ln","ln.idLiquidacionNomina","=","bp.fkLiquidacion")
            ->join("envio_correo_liquidacion as ecl","ecl.fkLiquidacion","=","ln.idLiquidacionNomina")
            ->where("ecl.idEnvioCorreoLiq","=",$idEnvioCorreoLiq)->get();
            $mensaje = "";
            foreach ($empleados as $empleado){
                $mensaje.='<tr>
                    <th scope="row">'.$empleado->tipoidentificacion.' - '. $empleado->numeroIdentificacion .'</th>
                    <td>'. $empleado->primerApellido .' '. $empleado->segundoApellido .' '. $empleado->primerNombre .' '. $empleado->segundoNombre .'</td>
                    <td>'. $empleado->estado .' '. $empleado->mensaje .'</td>
                </tr>';
            }
            DB::table("envio_correo_liquidacion")
            ->where("idEnvioCorreoLiq","=",$idEnvioCorreoLiq)
            ->update([
                "numActual" => $envioxLiquidacion->numRegistros,
                "fkEstado" => "5"
            ]);
            return response()->json([
                "success" => true,
                "seguirSubiento" => false,
                "mensaje" => $mensaje,
                "numActual" => $envioxLiquidacion->numRegistros,
                "numRegistros" => $envioxLiquidacion->numRegistros
            ]);
        }
    }

    public function enviarCorreoBoucher($idBoucherPago) {


        $smtpDef = SMTPConfigModel::find(1);

        $arrSMTPDefault = array(
            'host' => $smtpDef->smtp_host,
            'user' => $smtpDef->smtp_username,
            'pass' => $smtpDef->smtp_password,
            'encrypt' => $smtpDef->smtp_encrypt,
            'port' => $smtpDef->smtp_port,
            'sender_mail' => $smtpDef->smtp_mail_envia,
            'sender_name' => $smtpDef->smtp_nombre_envia
        );




        $empleado = DB::table("empleado", "e")
        ->selectRaw('dp.*,e.*,ti.nombre as tipoidentificacion, emp.razonSocial as nombreEmpresa,
                    CONCAT_WS(" ",dp.primerApellido, dp.segundoApellido, dp.primerNombre, dp.segundoNombre) as nombreCompleto')
        ->join("datospersonales as dp","dp.idDatosPersonales", "=", "e.fkDatosPersonales","left")
        ->join("tipoidentificacion as ti","ti.idtipoIdentificacion", "=", "dp.fkTipoIdentificacion","left")
        ->join("nomina as n","n.idNomina", "=","e.fkNomina","left")
        ->join("empresa as emp","emp.idempresa", "=","e.fkEmpresa","left")
        ->join("boucherpago as bp","bp.fkEmpleado", "=","e.idempleado")
        ->where("bp.idBoucherPago","=",$idBoucherPago)
        ->first();
        
        $empresayLiquidacion = DB::table("empresa", "e")
        ->select("e.*", "ln.*", "n.nombre as nom_nombre", "bp.*")
        ->join("nomina as n","n.fkEmpresa", "e.idempresa")
        ->join("liquidacionnomina as ln","ln.fkNomina", "n.idNomina")
        ->join("boucherpago as bp","bp.fkLiquidacion", "ln.idLiquidacionNomina")
        ->where("bp.idBoucherPago","=",$idBoucherPago)
        ->first();

        $smtConfig = DB::table("smtp_config","s")
        ->join("empresa as e","e.fkSmtpConf", "=","s.id_smpt")
        ->where("e.idempresa","=",$empresayLiquidacion->idempresa)->first();

        $novedadesRetiro = DB::table("novedad","n")
            ->select("r.fecha", "r.fechaReal","mr.nombre as motivoRet")
            ->join("retiro AS r", "r.idRetiro","=","n.fkRetiro")
            ->join("motivo_retiro as mr","mr.idMotivoRetiro","=","r.fkMotivoRetiro")
            ->where("n.fkEmpleado", "=", $empleado->idempleado)
            ->whereIn("n.fkEstado",["7", "8"])
            ->whereNotNull("n.fkRetiro")
            ->whereBetween("n.fechaRegistro",[$empresayLiquidacion->fechaInicio, $empresayLiquidacion->fechaFin])->first();
        
        $mensaje = DB::table("mensaje")->where("tipo","=", "4")
        ->where("fkEmpresa", "=",$empresayLiquidacion->idempresa)
        ->first();
        if(!isset($mensaje)){
            $mensaje = DB::table("mensaje")->where("idMensaje","=", "4")->first();
        }


        if(($empresayLiquidacion->fkTipoLiquidacion == "2" || $empresayLiquidacion->fkTipoLiquidacion == "3" || $empresayLiquidacion->fkTipoLiquidacion == "7") && isset($novedadesRetiro)){
            
            $mensaje = DB::table("mensaje")->where("tipo","=", "5")
            ->where("fkEmpresa", "=",$empresayLiquidacion->idempresa)
            ->first();
            if(!isset($mensaje)){
                $mensaje = DB::table("mensaje")->where("idMensaje","=", "5")->first();
            }
            
        }  

        $transport = new Swift_SmtpTransport($arrSMTPDefault['host'], $arrSMTPDefault['port'], $arrSMTPDefault['encrypt']);
        $transport->setUsername($arrSMTPDefault['user']);
        $transport->setPassword($arrSMTPDefault['pass']);
        $customSwiftMailer = new Swift_Mailer($transport);

        $sender_mail = $arrSMTPDefault['sender_mail'];
        $sender_name = $arrSMTPDefault['sender_name'];

        if(isset($smtConfig)){
            
            $transport = new Swift_SmtpTransport($smtConfig->smtp_host, $smtConfig->smtp_port, $smtConfig->smtp_encrypt);
            $transport->setUsername($smtConfig->smtp_username);
            $transport->setPassword($smtConfig->smtp_password);
            $customSwiftMailer = new Swift_Mailer($transport);

            $sender_mail = $smtConfig->smtp_mail_envia;
            $sender_name = $smtConfig->smtp_nombre_envia;
        }
        
        Mail::setSwiftMailer($customSwiftMailer);



        $arrDatos =  (array) $empleado;
       
        $arrDatos["periodoNomina"] = $empresayLiquidacion->fechaLiquida;
        
        $mensaje->html = $this->reemplazarCampos($mensaje->html, $arrDatos);
        $mensaje->asunto = $this->reemplazarCampos($mensaje->asunto, $arrDatos);
        

        $reportes = new ReportesNominaController();

        $pdf = $reportes->boucherCorreo($idBoucherPago);

        try {
            if(!isset($empleado->correo)){
                return response()->json([
                    "success" => false,
                    "error" => "Correo vacio"
                ]);
            }
            else{
                Mail::to($empleado->correo)->send(new ComprobantesPagoMail($mensaje->asunto, $mensaje->html, $sender_mail, $sender_name, $pdf));
                return response()->json([
                    "success" => true
                ]);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "error" => $e->getMessage()
            ]);
        }
    }
    private function reemplazarCampos($mensaje, $datos){
   
        foreach($this->arrayCampos as $id => $campo){
            if(isset($datos[$id])){
                $mensaje = str_replace($campo, $datos[$id], $mensaje);
            }
            else{
                $mensaje = str_replace($campo, "", $mensaje);
            }
        }
        return $mensaje;
        
    }
    
    public function enviarCorreoGeneral($idEmpleado, $tipoMensaje) {

        $smtpDef = SMTPConfigModel::find(1);
        $arrSMTPDefault = array(
            'host' => $smtpDef->smtp_host,
            'user' => $smtpDef->smtp_username,
            'pass' => $smtpDef->smtp_password,
            'encrypt' => $smtpDef->smtp_encrypt,
            'port' => $smtpDef->smtp_port,
            'sender_mail' => $smtpDef->smtp_mail_envia,
            'sender_name' => $smtpDef->smtp_nombre_envia
        );

        $empleado = DB::table("empleado", "e")
        ->selectRaw('dp.*,e.*,ti.nombre as tipoidentificacion, emp.razonSocial as nombreEmpresa,
                    CONCAT_WS(" ",dp.primerApellido, dp.segundoApellido, dp.primerNombre, dp.segundoNombre) as nombreCompleto')
        ->join("datospersonales as dp","dp.idDatosPersonales", "=", "e.fkDatosPersonales","left")
        ->join("tipoidentificacion as ti","ti.idtipoIdentificacion", "=", "dp.fkTipoIdentificacion","left")
        ->join("nomina as n","n.idNomina", "=","e.fkNomina","left")
        ->join("empresa as emp","emp.idempresa", "=","e.fkEmpresa","left")
        ->where("e.idempleado","=",$idEmpleado)
        ->first();
        

        $smtConfig = DB::table("smtp_config","s")
        ->join("empresa as e","e.fkSmtpConf", "=","s.id_smpt")
        ->where("e.idempresa","=",$empleado->fkEmpresa)->first();       
        
        $mensaje = DB::table("mensaje")->where("tipo","=", $tipoMensaje)
        ->where("fkEmpresa", "=",$empleado->fkEmpresa)
        ->first();
        if(!isset($mensaje)){
            $mensaje = DB::table("mensaje")->where("idMensaje","=", $tipoMensaje)->first();
        }

        $transport = new Swift_SmtpTransport($arrSMTPDefault['host'], $arrSMTPDefault['port'], $arrSMTPDefault['encrypt']);
        $transport->setUsername($arrSMTPDefault['user']);
        $transport->setPassword($arrSMTPDefault['pass']);
        $customSwiftMailer = new Swift_Mailer($transport);

        $sender_mail = $arrSMTPDefault['sender_mail'];
        $sender_name = $arrSMTPDefault['sender_name'];

        if(isset($smtConfig)){
            
            $transport = new Swift_SmtpTransport($smtConfig->smtp_host, $smtConfig->smtp_port, $smtConfig->smtp_encrypt);
            $transport->setUsername($smtConfig->smtp_username);
            $transport->setPassword($smtConfig->smtp_password);
            $customSwiftMailer = new Swift_Mailer($transport);

            $sender_mail = $smtConfig->smtp_mail_envia;
            $sender_name = $smtConfig->smtp_nombre_envia;
        }
        
        Mail::setSwiftMailer($customSwiftMailer);



        $arrDatos =  (array) $empleado;
       
        
        $mensaje->html = $this->reemplazarCampos($mensaje->html, $arrDatos);
        $mensaje->asunto = $this->reemplazarCampos($mensaje->asunto, $arrDatos);
        try {
            if(!isset($empleado->correo)){
                return response()->json([
                    "success" => false,
                    "error" => "Correo vacio"
                ]);
            }
            else{
                Mail::to($empleado->correo)->send(new MensajeGeneralMail($mensaje->asunto, $mensaje->html, $sender_mail, $sender_name));
                return response()->json([
                    "success" => true
                ]);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "error" => $e->getMessage()
            ]);
        }
    }


}
