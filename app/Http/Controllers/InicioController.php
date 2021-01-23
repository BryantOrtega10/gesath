<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\RecuperarPassMail;
use App\Http\Requests\ActPassRequest;
use App\User;
use Config;

class InicioController extends Controller
{
    public function index(Request $request){
        if ($request->session()->has('usuario')) {
            header('location: /seleccionarEmpresa');
        }
        else{
            return view('/inicio.inicio');
        }
    }

    public function noPermitido() {
        return view('/noPermitido');
    }

    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    protected function username() {
        return 'email';
    }

    public function login(LoginRequest $request)
    {
        $dataUsu = User::where('email', $request->email)->first();
        if ($dataUsu) {
            // $emple = DB::table('empleado')->select('fkEstado')->where('idempleado', $dataUsu->fkEmpleado)->first();
            // if ($emple->fkEstado == 1) {
                $estadoUsu = $dataUsu->estado;
                if ($estadoUsu == 1) {
                    $credentials = $request->only($this->username(), 'password');
                    $authSuccess = Auth::attempt($credentials);
            
                    if($authSuccess) {
                        $request->session()->regenerate();
                        return response(['success' => true, 'rol' => $dataUsu->fkRol], 200);
                    }
            
                    return response()->json(['success' => false, 'mensaje' => 'Error, usuario o contraseña incorrectos']);
                } else {
                    return response()->json(['success' => false, 'mensaje' => 'Error, el usuario no ha sido activado']);
                }   
            // } else {
            //     return response()->json(['success' => false, 'mensaje' => 'Error, el usuario no ha sido activado o está en creación']);
            // }
        } else {
            return response()->json(['success' => false, 'mensaje' => 'Error, usuario o contraseña incorrectos']);
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->flush();
        $request->session()->regenerate();

        return redirect('/');
    }

    /* RECUPERACION DE CONTRASEÑA */

    public function vistaRecuperarMail() {
        return view('/auth/passwords.email');
    }

    public function vistaActPass($token) {
        return view('/auth/passwords.reset', [
            'token' => $token
        ]);
    }

    public function validarUsuario(Request $request) {
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['success' => false, 'mensaje' => 'Error, este usuario o correo electrónico no está registrado en el sistema']);
        }

        $dataUsu = User::select(
            'users.email',
            'empresa.razonSocial'
        )
        ->join('empleado', 'empleado.idempleado', '=', 'users.fkEmpleado')
        ->join('empresa', 'empresa.idempresa', '=', 'empleado.fkEmpresa')
        ->where('users.email', $request->email)->first();

        DB::table('password_resets')->insert([
            'email' => $request->email,
            'token' => $this->generateRandomString(60),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $tokenData = DB::table('password_resets')->where('email', $request->email)->first();

        if ($this->sendResetEmail($request->email, $tokenData->token, $dataUsu->razonSocial)) {
            return response()->json(['success' => true, 'mensaje' => 'Ha sido enviado un enlace de recuperación al correo electrónico. Debes revisar la bandeja de entrada']);
        } else {
            return response()->json(['success' => false, 'mensaje' => 'Error de envío de correo electrónico']);
        }
    }

    private function sendResetEmail($email, $token, $nomEmpre) {
        try {
            Mail::to($email)->send(new RecuperarPassMail($email, $token, $nomEmpre));
            return true;
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            return false;
        }
    }

    public function resetPassword(ActPassRequest $request) {
        $password = $request->password;

        $tokenData = DB::table('password_resets')->where('token', $request->token)->first();

        if (!$tokenData) return response()->json(['success' => false, 'mensaje' => 'Error, no está disponible la recuperación de contraseña. Repita el proceso']);

        $user = User::where('email', $tokenData->email)->first();

        if (!$user) return response()->json(['success' => false, 'mensaje' => 'Error, correo electrónico no encontrado']);
        
        $user->password = $password;
        $update = $user->update();

        if ($update) {
            DB::table('password_resets')->where('email', $user->email)->delete();
            return response()->json(['success' => true, 'mensaje' => 'Contraseña actualizada correctamente']);
        }
    }

    function generateRandomString($length) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
   
}
