<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\UserRequest;
use App\User;

class UsuarioController extends Controller
{
    public function index() {
        $usuarios = User::select(
            'users.*',
            'rol.nombre'
        )
        ->join('rol', 'users.fkRol', 'rol.idrol')
        ->get();
        return view('/usuarios/usuarios', [
            'usuarios' => $usuarios
        ]);
    }

    public function getFormAdd() {
        return view('/usuarios/addUsuario');
    }

    public function create(UserRequest $request) {
        $usuario = new User();
        $usuario->username = $request->username;
        $usuario->email = $request->email;
        $usuario->password = $request->password;
        $usuario->fkRol = $request->fkRol;
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

    public function actPass(Request $request, $id) {
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
