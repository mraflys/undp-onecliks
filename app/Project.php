<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Cache, DB, Session;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
  use SoftDeletes;
  protected $table = 'ms_project';
  protected $primaryKey = 'id_project';
  const UPDATED_AT = 'date_updated';
  const DELETED_AT = 'date_deleted';

  public static function list_with_cache() {
    if (Cache::get('projects') != null) {
      return Cache::get('projects');
    }else{
      $results = self::orderBy('project_code')->get();
      Cache::put('projects', $results, 1800);
      return $results;
    }
  }

  public function agency(){
    return $this->belongsTo('App\AgencyUnit', 'id_agency_unit');
  }
}
