<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB, Session;

class TrServiceWorkFlowDoc extends Model
{
  use SoftDeletes;
  protected $table = 'tr_service_workflow_doc';
  protected $primaryKey = 'id_transaction_workflow_doc';
  const UPDATED_AT = 'date_updated';
  const DELETED_AT = 'date_deleted';
  const CREATED_AT = 'date_created';
}
