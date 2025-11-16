<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Cache, DB, Session;
use Illuminate\Database\Eloquent\SoftDeletes;

class Currency extends Model
{
  use SoftDeletes;
  protected $table = 'ms_currency';
  protected $primaryKey = 'id_currency';
  const UPDATED_AT = 'date_updated';
  const DELETED_AT = 'date_deleted';
  protected $hidden = ['date_created', 'date_updated', 'updated_by', 'created_by', 'deleted_by'];

  public static function list_with_cache() {
    if (Cache::get('currencies') != null) {
      return Cache::get('currencies');
    }else{
      $results = self::orderBy('currency_name')->get();
      Cache::put('currencies', $results, 1800);
      return $results;
    }
  }
}
