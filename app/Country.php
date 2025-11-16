<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Cache, DB, Session;
use Illuminate\Database\Eloquent\SoftDeletes;

class Country extends Model
{
  use SoftDeletes;
  protected $table = 'ms_country';
  protected $primaryKey = 'id_country';
  const UPDATED_AT = 'date_updated';
  const DELETED_AT = 'date_deleted';

  public static function list_with_cache() {
    if (Cache::get('countries') != null) {
      return Cache::get('countries');
    }else{
      $results = self::orderBy('country_name')->get();
      Cache::put('countries', $results, 1800);
      return $results;
    }
  }

  public static function get_last_sequence_by_code($code){
    $code = strtolower($code);
    $res = self::select(DB::raw("sequence"))->whereRaw("LOWER(country_code) = '".$code."'")->first();
    DB::update("UPDATE ms_country SET sequence = sequence + 1 WHERE LOWER(country_code) = '".$code."'");
    return ($res->sequence + 1);
  }
}
