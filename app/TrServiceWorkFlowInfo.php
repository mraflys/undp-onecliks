<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrServiceWorkFlowInfo extends Model
{
    //
    use SoftDeletes;
  
    protected $table = 'tr_service_workflow_info';
    protected $primaryKey = 'id_transaction_workflow_info';
  	const UPDATED_AT = 'date_updated';
  	const DELETED_AT = 'date_deleted';
  	const CREATED_AT = 'date_created';
    protected $fillable = ['id_transaction_workflow', 'id_service_workflow_info'];
}
