<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB, Session;

class TrServiceWorkFlow extends Model
{
  use SoftDeletes;
  protected $table = 'tr_service_workflow';
  protected $primaryKey = 'id_transaction_workflow';
  const UPDATED_AT = 'date_updated';
  const DELETED_AT = 'date_deleted';
  const CREATED_AT = 'date_created';

  public function tr_service(){
    return $this->belongsTo('App\TrService', 'id_transaction');
  }

  public function agency(){
  	return $this->belongsTo('App\AgencyUnit', 'id_agency_unit_pic');
  }

  public function primary_pic(){
  	return $this->belongsTo('App\SecUser', 'id_user_pic_primary');
  }

  public function alternate_pic(){
  	return $this->belongsTo('App\SecUser', 'id_user_pic_alternate');
  } 

  public function finisher(){
  	return SecUser::where("user_name", $this->completed_by)->first();
  }

  public function infos(){
  	return $this->hasMany("App\TrServiceWorkFlowInfo", 'id_transaction_workflow');
  }

  public function docs(){

  	return $this->hasMany("App\TrServiceWorkFlowDoc", 'id_transaction_workflow','id_transaction_workflow');
  }
}
