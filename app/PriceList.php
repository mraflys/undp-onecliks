<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Cache;
use DB;
use Session;

class PriceList extends Model
{
  use SoftDeletes;
  protected $table = 'ms_service_pricelist';
  protected $primaryKey = 'id_service_pricelist';
  protected $hidden = ['date_created', 'date_updated', 'updated_by', 'created_by', 'deleted_by'];
  
  const UPDATED_AT = 'date_updated';
  const DELETED_AT = 'date_deleted';

  public static function list_with_cache() {
    if (Cache::get('pricelist') != null) {
      return Cache::get('pricelist');
    }else{
      $results = self::all()->get();
      Cache::put('pricelist', $results, 1800);
      return $results;
    }
  }

  public function service(){
    return $this->belongsTo('App\ServiceList', 'id_service');
  }

  public function currency(){
    return $this->belongsTo('App\Currency', 'id_currency');
  }

  public static function get_latest_seq($id_service){
    $row = self::select(DB::raw('count(sequence) as count_sequence'))->where('id_service', $id_service)->first();
    return (!is_null($row)) ? $row->count_sequence : 0;
  }

  public static function active_by_service_id_parent($id_service){
    return self::with_service()->whereRaw("(date_start_price <= now() AND date_end_price >= now()) 
      AND id_service_parent = ".$id_service);
  }

  public static function with_service(){
    return self::join("ms_service", "ms_service.id_service", "=", "ms_service_pricelist.id_service");
  }

}
