<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Cache;
use DB;
use Session;

class AgencyUnit extends Model
{
  use SoftDeletes;
  protected $table = 'ms_agency_unit';
  protected $primaryKey = 'id_agency_unit';
  protected $hidden = ['date_created', 'date_updated', 'updated_by', 'created_by', 'deleted_by'];
  
  const UPDATED_AT = 'date_updated';
  const DELETED_AT = 'date_deleted';

  public static function list_with_cache() {
    if (Cache::get('agency_units') != null) {
      return Cache::get('agency_units');
    }else{
      $results = self::whereRaw("id_agency_unit_parent = '' OR id_agency_unit_parent IS NULL")
      ->orderBy('agency_unit_name')->get();
      Cache::put('agency_units', $results, 1800);
      return $results;
    }
  }

  public static function list_with_cache_of_service() {
    if (Cache::get('service_units') != null) {
      return Cache::get('service_units');
    }else{
      $results = self::whereRaw('id_agency_unit_parent IS NOT NULL AND is_service_unit = true')
      ->orderBy('agency_unit_name')->get();
      Cache::put('service_units', $results, 1800);
      return $results;
    }
  }

  public static function by_country_id($country_id = 1){
    $country_id = $country_id == null ? 1 : $country_id;
    return self::where('id_country', $country_id)->orderBy('agency_unit_name')->get();
  }

  public function country(){
    return $this->belongsTo('App\Country', 'id_country');
  }

  public function parent(){
    return $this->belongsTo('App\AgencyUnit', 'id_agency_unit_parent');
  }
}
