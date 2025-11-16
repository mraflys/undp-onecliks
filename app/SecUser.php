<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use DB;
use Session, Cache;
use Illuminate\Database\Eloquent\SoftDeletes;

class SecUser extends Authenticatable
{
  use SoftDeletes;
  protected $table = 'sec_user';
  protected $primaryKey = 'id_user';

  const UPDATED_AT = 'date_updated';
  const DELETED_AT = 'date_deleted';

  public static function list_with_cache() {
    if (Cache::get('sec_users') != null) {
      return Cache::get('sec_users');
    }else{
      $results = self::orderBy('person_name', 'ASC')->get();
      Cache::put('sec_users', $results, 1800);
      return $results;
    }
  }

  public function agency(){
    return $this->belongsTo('App\AgencyUnit', 'id_agency_unit');
  }

  public function role(){
    return $this->belongsTo('App\SecRole', 'id_role');
  }

  public function country(){
    return $this->belongsTo('App\Country', 'id_country');
  }

  public static function get_by_id_agency_parent($id_agency_unit){
    return self::select("*")
    ->join("ms_agency_unit as ms_agency_unit_child", "ms_agency_unit_child.id_agency_unit",'=', "sec_user.id_agency_unit")
    ->join("ms_agency_unit", "ms_agency_unit_child.id_agency_unit_parent",'=', "ms_agency_unit.id_agency_unit")
    ->where('ms_agency_unit.id_agency_unit', $id_agency_unit)->orderBy("person_name")->get();
  }
  
  public static function get_by_id_agency_parent1($id_agency_unit,$id_user){
    return self::select("*")
    ->where('id_agency_unit', $id_agency_unit)->Orwhere('id_user', $id_user)->orderBy("person_name")->get();
  }
  public static function get_by_id_agency_parent2(){
    return self::select("*")
    ->join("ms_agency_unit as ms_agency_unit_child", "ms_agency_unit_child.id_agency_unit",'=', "sec_user.id_agency_unit")
    ->join("ms_agency_unit", "ms_agency_unit_child.id_agency_unit_parent",'=', "ms_agency_unit.id_agency_unit")->where('sec_user.is_active', 1)
    ->orderBy("person_name")->get();
  }
}
