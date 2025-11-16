<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB, Session;

class DrServiceWorkFlow extends Model
{
  use SoftDeletes;
  protected $table = 'dr_service_workflow';
  protected $primaryKey = 'id_transaction_workflow';
  const UPDATED_AT = 'date_updated';
  const DELETED_AT = 'date_deleted';
  const CREATED_AT = 'date_created';

  public function agency(){
  	return $this->belongsTo('App\AgencyUnit', 'id_agency_unit_pic');
  }
	  
}
