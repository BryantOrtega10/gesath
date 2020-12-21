<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NominaEmpresaModel extends Model
{
    protected $table = "nomina";
    protected $primaryKey = "idNomina";
    protected $fillable = [
        'nombre',
        'tipoPeriodo',
        'periodo',
        'fkEmpresa',
        'diasCesantias',
        'id_uni_nomina'
    ];
    public $timestamps = false;
}
