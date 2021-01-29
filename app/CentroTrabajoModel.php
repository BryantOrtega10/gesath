<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CentroTrabajoModel extends Model
{
    protected $table = "centrotrabajo";
    protected $primaryKey = "idCentroTrabajo";
    protected $fillable = [
        'codigo',
        'nombre',
        'fkNivelArl',
        'fkEmpresa'
    ];
    public $timestamps = false;
}
