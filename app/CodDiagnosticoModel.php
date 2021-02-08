<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CodDiagnosticoModel extends Model
{
    protected $table = "cod_diagnostico";
    protected $primaryKey = "idCodDiagnostico";
    public $incrementing = false;
    protected $fillable = [
        'idCodDiagnostico',
        'nombre',
    ];
    public $timestamps = false;
}
