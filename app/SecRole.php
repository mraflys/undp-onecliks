<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use Session, Cache;

class SecRole extends Model
{
  protected $table = 'sec_role';
  protected $primaryKey = 'id_role';

  const UPDATED_AT = 'date_updated';
  const DELETED_AT = 'date_deleted';

  public static function list_with_cache() {
    if (Cache::get('sec_roles') != null) {
      return Cache::get('sec_roles');
    }else{
      $results = self::orderBy('role_name', 'ASC')->get();
      Cache::put('sec_roles', $results, 1800);
      return $results;
    }
  }
}
