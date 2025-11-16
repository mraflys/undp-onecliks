<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Cache, DB, Session;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expenditur extends Model
{
  use SoftDeletes;
  protected $table = 'ms_exp_type';
  protected $primaryKey = 'id_exptype';
  const CREATED_AT = 'date_created';
  const UPDATED_AT = 'date_update';
  const DELETED_AT = 'date_deleted';
  protected $fillable = [
    'exp_type_code',
    'exp_type_name',
    'description',
  ];
}
