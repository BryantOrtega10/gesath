<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\CalendarioModel;
use App\Http\Requests\CalendarioRequest;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CalendarioController extends Controller
{
    public function index() {
        $calendario = CalendarioModel::all();
        $usu = UsuarioController::dataAdminLogueado();
        return view('/calendario.calendario', [
            "calendarios" => $calendario,
            'dataUsu' => $usu
        ]);
    }

    public function getFormAdd() {
        return view('/calendario/addCalendario');
    }
    
    public function insertarDatosTabla($fechaA, $fechaI, $fechaF) {
        $fechaAsignada = explode(',', $fechaA);
        $fechaInicioSemana = explode(',', $fechaI);
        $fechaFinSemana = explode(',', $fechaF);

        $retorno = false;
        foreach($fechaAsignada as $key => $f) {
            $calendario = new CalendarioModel();
            $calendario->fecha = $f;
            $calendario->fechaInicioSemana = $fechaInicioSemana[$key];
            $calendario->fechaFinSemana = $fechaFinSemana[$key];
            $save = $calendario->save();
            if ($save) {
                $retorno = true;
            }
        }
        return $retorno;
    }
    
    public function create(CalendarioRequest $request) {
        $insertar = $this->insertarDatosTabla($request->fecha, $request->fechaInicioSemana, $request->fechaFinSemana);

        if ($insertar) {
            $success = true;
            $mensaje = "Calendario agregado correctamente";
        } else {
            $success = true;
            $mensaje = "Error al agregar calendario";
        }

        return response()->json(["success" => $success, "mensaje" => $mensaje]);
    }

    public function edit() {
        try {
            $calendario = CalendarioModel::select([
                'fecha',
                'fechaInicioSemana',
                'fechaFinSemana'
            ])->get();
            
            return view('/calendario.editCalendario', [
                'calendario' => $calendario
            ]);
		}
		catch (ModelNotFoundException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe un calendario con este ID"]);
		}
    }

     public function detail() {
        try {
            $calendario = CalendarioModel::select([
                'fecha',
                'fechaInicioSemana',
                'fechaFinSemana'
            ])->get();

            return view('/calendario.detailCalendario', [
                'calendario' => $calendario
            ]);
		}
		catch (ModelNotFoundException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe un calendario con este ID"]);
		}
    }
    public function update(Request $request) {
        try {
            CalendarioModel::truncate();
            if ($request->fecha) {
                $insertar = $this->insertarDatosTabla($request->fecha, $request->fechaInicioSemana, $request->fechaFinSemana);
                if ($insertar) {
                    $success = true;
                    $mensaje = "Calendario actualizado correctamente";
                } else {
                    $success = false;
                    $mensaje = "Error al actualizar calendario";
                }
            } else {
                $success = false;
                $mensaje = "No puedes actualizar el calendario sin haber seleccionado al menos un día";
            }
    
        }
		catch (ModelNotFoundException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe un calendario con este ID"]);
        }
        return response()->json(["success" => $success, "mensaje" => $mensaje]);
    }

    public function delete($id) {
        try{
			$calendario = CalendarioModel::findOrFail($id);
			if($calendario->delete()){
				$success = true;
				$mensaje = "Calendario eliminado con exito";
			} else {
				$success = false;
				$mensaje = "Error al eliminar calendario";
			}
			return response()->json(["success" => $success, "mensaje" => $mensaje]);
		} catch (ModelNotFoundException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe un calendario con este ID"]);
		}
    }
}
