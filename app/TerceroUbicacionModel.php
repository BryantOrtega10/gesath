<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TerceroUbicacionModel extends Model
{
    protected $table = 'tercero_ubicacion';
	protected $primaryKey = 'id_ter_ubi';
	protected $fillable = ['id_ter', 'id_ubi'];
	public $timestamps = false;
}
