<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Concepto extends Model
{
    protected $table = 'concepto';
	protected $primaryKey = 'idconcepto';
	public $timestamps = false;
}
