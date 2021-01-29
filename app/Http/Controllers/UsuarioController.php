<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\UserRequest;
use App\Http\Requests\CrearUsuarioAdminRequest;
use App\Http\Requests\SoloPassRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\User;

class UsuarioController extends Controller
{
    public function index() {
        $usu = $this->dataAdminLogueado();
        $usuarios = User::select(
            'users.*',
            'rol.nombre'
        )
        ->join('rol', 'users.fkRol', 'rol.idrol')
        ->get();
        return view('/usuarios/usuarios', [
            'usuarios' => $usuarios,
            'dataUsu' => $usu
        ]);
    }

    public static function dataAdminLogueado() {
        $usuario = Auth::user();
        $dataUsu = DB::table('users')->select(
            'users.username',
            'users.email',
            'datospersonales.primerNombre',
            'datospersonales.primerApellido',
            'datospersonales.foto',
            'empleado.idempleado'
        )->join('empleado', 'users.fkEmpleado', 'empleado.idempleado')
        ->join('empresa', 'empresa.idempresa', 'empleado.fkEmpresa')
        ->join('datospersonales', 'datospersonales.idDatosPersonales', 'empleado.fkDatosPersonales')
        ->where('users.id', $usuario->id)
        ->first();
        return $dataUsu;
    }

    public function getFormAdd() {
        $empresas = DB::table('empresa')
        ->select(
            'idempresa',
            'razonSocial'
        )
        ->get();
        return view('/usuarios/addUsuario', [
            'empresas' => $empresas
        ]);
    }

    public function create(CrearUsuarioAdminRequest $request) {
        // Creamos primero registro de datos personales
        $image = $request->file('foto');
        $name = "imagen_" . time() . '.' . $image->getClientOriginalExtension();
        $destinationPath = storage_path('app/public/imgEmpleados');
        $image->move($destinationPath, $name);

        $idDatosP = DB::table('datospersonales')->insertGetId([
            'foto' => $name,
            'primerNombre' => $request->primerNombre,
            'primerApellido' => $request->primerApellido
        ]);

        // Creamos registro de empleado

        $idRecEmp = DB::table('empleado')->insertGetId([
            'fkDatosPersonales' => $idDatosP,
            'fkEmpresa' => $request->fkEmpresa,
            'fechaIngreso' => date('Y-m-d')
        ]);

        // Creamos registro en la tabla usuarios

        $usuario = new User();
        $usuario->username = $request->username;
        $usuario->email = $request->email;
        $usuario->password = $request->password;
        $usuario->fkRol = $request->fkRol;
        $usuario->fkEmpleado = $idRecEmp;
        $usuario->estado = 1;
        $usuario->created_at = date("Y-m-d H:i:s");
        $usuario->updated_at = date("Y-m-d H:i:s");
        $save = $usuario->save();
        if ($save) {
            $success = true;
            $mensaje = "Usuario agregado correctamente";
        } else {
            $success = true;
            $mensaje = "Error al agregar usuario";
        }
        return response()->json(["success" => $success, "mensaje" => $mensaje]);
    }

    public function edit($id) {
        try {
            $usuario = User::findOrFail($id);
            return view('/usuarios/editUsuario', [
                'usuario' => $usuario
            ]);
		}
		catch (ModelNotFoundException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe un usuario con este ID"]);
		}
    }

    public function detail($id) {
        try {
            $usuario = User::findOrFail($id);
            return view('/usuarios/detailUsuario', [
                'usuario' => $usuario
            ]);
		}
		catch (ModelNotFoundException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe una usuario con este ID"]);
		}
    }

    public function update(Request $request, $id) {
        try {
            $usuario = User::findOrFail($id);
            $usuario->username = $request->username;
            $usuario->email = $request->username;
            $usuario->fkRol = $request->fkRol;
            $usuario->updated_at = date("Y-m-d H:i:s");
            $save = $usuario->save();
            if ($save) {
                $success = true;
                $mensaje = "Usuario actualizado correctamente";
            } else {
                $success = true;
                $mensaje = "Error al actualizar usuario";
            }
            return response()->json(["success" => $success, "mensaje" => $mensaje]);
            }
		catch (ModelNotFoundException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe una usuario con este ID"]);
		}
    }

    public function hab_deshab_usu($id, $estado){
        if ($estado == 0) {
            $estadoAct = 1;
            $mensaje = "Usuario habilitado correctamente";
        } else {
            $estadoAct = 0;
            $mensaje = "Usuario deshabilitado correctamente";
        }
        try {
            $usuario = User::findOrFail($id);
            $usuario->estado = $estadoAct;
            $save = $usuario->save();
            if ($save) {
                $success = true;
            } else {
                $success = false;
            }
            return response()->json(["success" => $success, "mensaje" => $mensaje]);
            }
		catch (ModelNotFoundException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe una usuario con este ID"]);
		}
    }

    public function delete($id) {
        try{
            $usuario = User::findOrFail($id);
            if($usuario->delete()){
                $success = true;
                $mensaje = "Usuario eliminado con exito";
            } else {
                $success = false;
                $mensaje = "Error al eliminar usuario";
            }
			return response()->json(["success" => $success, "mensaje" => $mensaje]);
		} catch (ModelNotFoundException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe una usuario con este ID"]);
        }
    }

    public function vistaActPass($id) {
        try {
            $usuario = User::findOrFail($id);
            return view('/usuarios/cambiarPass', [
                'usuario' => $usuario
            ]);
		}
		catch (ModelNotFoundException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe una usuario con este ID"]);
		}
    }

    public function actPass(SoloPassRequest $request, $id) {
        try {
            $usuario = User::findOrFail($id);
            $usuario->password = $request->password;
            $save = $usuario->save();
            if ($save) {
                $success = true;
                $mensaje = "Contraseña modificada correctamente";
            } else {
                $success = false;
                $mensaje = "Error al modificar contraseña";
            }
            return response()->json(["success" => $success, "mensaje" => $mensaje]);
            }
		catch (ModelNotFoundException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe una usuario con este ID"]);
		}
    }
}
