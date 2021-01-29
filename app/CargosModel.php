<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CargosModel extends Model
{
    protected $table = "cargo";
    protected $primaryKey = "idCargo";
    protected $fillable = [
        'nombreCargo'
    ];
    public $timestamps = false;
}
