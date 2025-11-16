<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\WorkFlowDoc;
use DB, Session;

class DrServiceDoc extends Model
{
  use SoftDeletes;
  protected $table = 'dr_service_doc';
  protected $primaryKey = 'id_draft_doc';
  public $timestamps = false;
  const DELETED_AT = 'date_deleted';
  const CREATED_AT = 'date_created';

  public function workflowDoc(){
    return $this->belongsTo(WorkFlowDoc::class, 'id_workflow', 'id_service_workflow_doc');
  }
}
