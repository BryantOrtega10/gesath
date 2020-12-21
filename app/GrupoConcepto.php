<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GrupoConcepto extends Model
{
    protected $table = 'grupoconcepto';
	protected $primaryKey = 'idgrupoConcepto';
	protected $fillable = ['nombre'];
	public $timestamps = false;
}
