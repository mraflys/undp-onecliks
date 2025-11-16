<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Cache;
use DB;
use Session;

class WorkFlowDoc extends Model
{
  use SoftDeletes;
  protected $table = 'ms_service_workflow_doc';
  protected $primaryKey = 'id_service_workflow_doc';
  protected $hidden = ['date_created', 'date_updated', 'updated_by', 'created_by', 'deleted_by'];
  
  const UPDATED_AT = 'date_updated';
  const DELETED_AT = 'date_deleted';

  public static function list_with_cache() {
    if (Cache::get('workflow_docs') != null) {
      return Cache::get('workflow_docs');
    }else{
      $results = self::all()->get();
      Cache::put('workflow_docs', $results, 1800);
      return $results;
    }
  }

  public function workflow(){
    return $this->belongsTo('App\WorkFlow', 'id_service_workflow');
  }

  public static function get_latest_seq($id_service){
    $row = self::select(DB::raw('count(*) as count_sequence'))->where('id_service_workflow', $id_service)->first();
    return (!is_null($row)) ? ($row->count_sequence + 1 ): 0;
  }
}
