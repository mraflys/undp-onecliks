<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB, Session;

class DrServiceInfo extends Model
{
  use SoftDeletes;
  protected $table = 'dr_service_info';
  protected $primaryKey = 'id_draft_info';
  const UPDATED_AT = 'date_updated';
  const DELETED_AT = 'date_deleted';
  const CREATED_AT = 'date_created';

  public function workflowInfo(){
    return $this->belongsTo(DrServiceInfo::class, 'id_workflow', 'id_service_workflow_doc');
  }
}
