<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TrPexSetting extends Model
{
    public $timestamps = false;
    protected $table = 'tr_pex_setting';
    protected $fillable = ['id_user','TIDNO','is_active'];
}
