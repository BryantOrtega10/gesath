<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\CargosModel;
use App\Http\Requests\CargosRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use League\Csv\Reader;

class CargosController extends Controller
{
    public function index() {
        $cargos = CargosModel::all();
        return view('/cargos/cargos', [
            'cargos' => $cargos
        ]);
    }

    public function subirPlanoIndex(){
        
        return view('/cargos/subirPlanocargos');
    }

    public function subirArchivo(Request $req){
        $csvDatosPasados = $req->file("archivoCSV");
        $reader = Reader::createFromFileObject($csvDatosPasados->openFile());
        $reader->setOutputBOM(Reader::BOM_UTF8);
        $reader->setDelimiter(';');
        $csvDatosPasados = $csvDatosPasados->store("public/csvFiles");
        foreach ($reader as $row){
            foreach($row as $key =>$col){
                $row[$key] = mb_convert_encoding($col,"UTF-8");
            }
            if($row[0] != ""){
                $cargo = new CargosModel();
                $cargo->nombreCargo = $row[0];
                $save = $cargo->save();
            }
            
        }
        return redirect('/cargos');
    }
    

    public function getFormAdd() {
        return view('/cargos/addCargo');
    }

    public function create(CargosRequest $request) {
        $cargo = new CargosModel();
        $cargo->nombreCargo = $request->nombreCargo;
        $save = $cargo->save();
        if ($save) {
            $success = true;
            $mensaje = "Nomina de empresa agregada correctamente";
        } else {
            $success = true;
            $mensaje = "Error al agregar nomina de empresa";
        }
        return response()->json(["success" => $success, "mensaje" => $mensaje]);
    }

    public function edit($id) {
        try {
            $cargo = CargosModel::findOrFail($id);
            return view('/cargos/editCargo', [
                'cargos' => $cargo
            ]);
		}
		catch (ModelNotFoundException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe un nomina de empresa con este ID"]);
		}
    }

    public function detail($id) {
        try {
            $cargo = CargosModel::findOrFail($id);
            return view('/cargos/detailCargo', [
                'cargos' => $cargo
            ]);
		}
		catch (ModelNotFoundException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe una nomina de empresa con este ID"]);
		}
    }

    public function update(Request $request, $id) {
        try {
            $cargo = CargosModel::findOrFail($id);
            $cargo->nombreCargo = $request->nombreCargo;
            $save = $cargo->save();
            if ($save) {
                $success = true;
                $mensaje = "Nomina de empresa actualizada correctamente";
            } else {
                $success = true;
                $mensaje = "Error al actualizar nomina de empresa";
            }
            return response()->json(["success" => $success, "mensaje" => $mensaje]);
            }
		catch (ModelNotFoundException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe una nomina de empresa con este ID"]);
		}
    }

    public function delete($id) {
        try{
            $cargo = CargosModel::findOrFail($id);
            if($cargo->delete()){
                $success = true;
                $mensaje = "Nomina de empresa eliminada con exito";
            } else {
                $success = false;
                $mensaje = "Error al eliminar nomina de empresa";
            }
			return response()->json(["success" => $success, "mensaje" => $mensaje]);
		} catch (ModelNotFoundException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe una nomina de empresa con este ID"]);
        }
    }
}
