<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\SMTPConfigRequest;
use App\SMTPConfigModel;
use App\EmpresaModel;
use Illuminate\Http\Request;

class SMTPConfigController extends Controller
{
    public function index($id) {
        $arrSMTPDefault = array(
            'host' => 'smtp-mail.outlook.com',
            'user' => 'noreply@gesath.com',
            'pass' => 'NI900020391',
            'encrypt' => 'TLS',
            'port' => '587',
            'sender_mail' => 'noreply@gesath.com',
            'sender_name' => 'PRUEBAS GESATH'
        );
        $smtp = SMTPConfigModel::select([
            'smtp_config.*',
            'empresa.fkSmtpConf'
        ])
        ->join('empresa', 'smtp_config.id_smpt', 'empresa.fkSmtpConf')
        ->where('empresa.idempresa', $id)
        ->first();
        // dd($smtp);
        $usu = UsuarioController::dataAdminLogueado();
        return view('/empresas/smtpConf.smtpConfig', [
            "smtp" => $smtp,
            "smtpDefault" => $arrSMTPDefault,
            'dataUsu' => $usu,
            "idEmpresa" => $id
        ]);
    }

    public function create(SMTPConfigRequest $request) {
        $success = '';
        $mensaje = '';
        if ($request->confPropia == 'true') {
            $guardarSMTP = $this->agregarSMTPaBD(
                $request->smtp_host,
                $request->smtp_port,
                $request->smtp_username,
                $request->smtp_password,
                $request->smtp_encrypt,
                $request->smtp_mail_envia,
                $request->smtp_nombre_envia
            );
            if ($guardarSMTP) {
                $actConfEmpresa = $this->actConfSmtpEmpresa(
                    $request->idEmpresa,
                    $guardarSMTP->id_smpt
                );
                if ($actConfEmpresa) {
                    $success = true;
                    $mensaje = "Configuración actualizada correctamente";
                } else {
                    $success = false;
                    $mensaje = "Error al actualizar configuración";
                }
            } else {
                $success = false;
                $mensaje = "Error al actualizar configuración";
            }
        } else {
            $actConfEmpresa = $this->actConfSmtpEmpresa(
                $request->idEmpresa,
                11
            );
            if ($actConfEmpresa) {
                $success = true;
                $mensaje = "Configuración actualizada correctamente";
            } else {
                $success = false;
                $mensaje = "Error al actualizar configuración";
            }
        }

        return response()->json(["success" => $success, "mensaje" => $mensaje]);
    }

    public function actConfSmtpEmpresa($id, $idSmtp) {
        $retorno = false;
        $empresaModel = EmpresaModel::findOrFail($id);
        $empresaModel->fkSmtpConf = $idSmtp;
        $actualizar = $empresaModel->save();
        if ($actualizar) {
            $retorno = true;
        }

        return $retorno;
    }

    public function agregarSMTPaBD($host, $puerto,  $usuario, $pass, $encrypt, $mailEnvia, $nomEnvia) {
        $retorno = false;
        $smtp = new SMTPConfigModel();
        $smtp->smtp_host = $host;
        $smtp->smtp_port = $puerto;
        $smtp->smtp_username = $usuario;
        $smtp->smtp_password = $pass;
        $smtp->smtp_encrypt = $encrypt;
        $smtp->smtp_mail_envia = $mailEnvia;
        $smtp->smtp_nombre_envia = $nomEnvia;
        $insertar = $smtp->save();
        if ($insertar) {
            return $smtp;
        }

        return $retorno;
    }
}