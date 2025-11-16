<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Cache;
use DB;
use Session;
use App\WorkFlow;

class ServiceList extends Model
{
  use SoftDeletes;
  protected $table = 'ms_service';
  protected $primaryKey = 'id_service';
  protected $hidden = ['date_created', 'date_updated', 'updated_by', 'created_by', 'deleted_by'];
  
  const UPDATED_AT = 'date_updated';
  const DELETED_AT = 'date_deleted';
  const CREATED_AT = 'date_created';

  public static function list_with_cache() {
    if (Cache::get('service_list') != null) {
      return Cache::get('service_list');
    }else{
      $results = self::all()->orderBy('service_name')->get();
      Cache::put('service_list', $results, 1800);
      return $results;
    }
  }

  public static function with_active_price($where){
    return self::select(DB::raw("ms_service.id_service, ms_service.service_name"))
      ->join("ms_service as service_child","service_child.id_service_parent", "=", "ms_service.id_service")
      ->join('ms_service_pricelist', 'ms_service_pricelist.id_service', '=', 'service_child.id_service')
      ->whereRaw("(ms_service_pricelist.date_start_price <= now() AND ms_service_pricelist.date_end_price >= now())")
      ->whereRaw($where)
      ->groupBy('ms_service.service_name', 'ms_service.id_service')
      ->orderBy('ms_service.service_name')
      ->get();
  }

  public static function service_req($where){
    
    $ms_service_workflow = WorkFlow::with('docs')->whereIn('id_service',$where)->get();

    return $ms_service_workflow;
  }

  public static function service_info_req($where){
    
    $ms_service_workflow = WorkFlow::with('infos')->whereIn('id_service',$where)->get();

    return $ms_service_workflow;
  }

  public function agency(){
    return $this->belongsTo('App\AgencyUnit', 'id_agency_unit');
  }

  public function parent(){
    return $this->belongsTo('App\ServiceList', 'id_service_parent');
  }

  public function children(){
    return $this->hasMany('App\ServiceList', 'id_service_parent');
  }

  public function workflows(){
    return $this->hasMany('App\WorkFlow', 'id_service');
  }

  public function price_list(){
    return $this->hasMany('App\PriceList', 'id_service');
  }

  public static function pricelist_mapping($additional_filter = null){
    $where = "ms_service.date_deleted IS NULL";
    if ($additional_filter != null)
      $where .= " AND (".$additional_filter.")";

    return self::select(DB::raw("ms_service.id_service, ms_service.service_name, agency_parent.agency_unit_code as agency_parent_code, ms_agency_unit.agency_unit_name as unit_name, SUM(price) as price"))
    ->join("ms_service as service_child","service_child.id_service_parent", "=", "ms_service.id_service")
    ->join("ms_agency_unit","ms_agency_unit.id_agency_unit", "=", "ms_service.id_agency_unit")
    ->join("ms_agency_unit as agency_parent","ms_agency_unit.id_agency_unit_parent", "=", "agency_parent.id_agency_unit")
    ->join("ms_service_pricelist","ms_service_pricelist.id_service", "=", "service_child.id_service")
    ->whereRaw($where)
    ->groupBy("ms_service.id_service", "ms_service.service_name", "agency_parent_code", "unit_name");
  }
}
