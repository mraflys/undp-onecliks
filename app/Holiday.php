<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Cache, DB, Session;
use Illuminate\Database\Eloquent\SoftDeletes;

class Holiday extends Model
{
  use SoftDeletes;
  protected $table = 'ms_calendar_holiday';
  protected $primaryKey = 'id_calendar_holiday';
  const UPDATED_AT = 'date_updated';
  const DELETED_AT = 'date_deleted';

  public static function list_with_cache() {
    if (Cache::get('holidays') != null) {
      return Cache::get('holidays');
    }else{
      $results = self::orderBy('calendar_holiday_name')->get();
      Cache::put('holidays', $results, 1800);
      return $results;
    }
  }
}
