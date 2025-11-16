<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Cache, DB, Session;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coa extends Model
{
  use SoftDeletes;
  protected $table = 'ms_coa';
  protected $primaryKey = 'id_master_coa';
  const UPDATED_AT = 'date_updated';
  const DELETED_AT = 'date_deleted';

  public static function list_with_cache() {
    if (Cache::get('coas') != null) {
      return Cache::get('coas');
    }else{
      $results = self::orderBy('coa_code')->get();
      Cache::put('coas', $results, 1800);
      return $results;
    }
  }
}
