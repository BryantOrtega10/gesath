<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CentroCostoEmpresaModel extends Model
{
    protected $table = "centrocosto";
    protected $primaryKey = "idcentroCosto";
    protected $fillable = [
        'nombre',
        'fkEmpresa',
        'id_uni_centro'
    ];
    public $timestamps = false;
}
