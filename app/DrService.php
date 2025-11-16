<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB, Session;
use App\Country;

class DrService extends Model
{
  use SoftDeletes;
  protected $table = 'dr_service';
  protected $primaryKey = 'id_draft';
  const UPDATED_AT = 'date_updated';
  const DELETED_AT = 'date_deleted';
  const CREATED_AT = 'date_created';

  public function currency(){
    return $this->belongsTo('App\Currency', 'id_currency');
  }

  public function agency(){
    return $this->belongsTo('App\AgencyUnit', 'id_agency_unit_service');
  }

  public function DrServiceDoc(){
    return $this->hasMany(DrServiceDoc::class, 'id_draft', 'id_draft');
  }

  public function required_docs(){
    return DrServiceDoc::select(DB::raw("dr_service_doc.*, ms_service_workflow.workflow_name, ms_service_workflow_doc.document_name, ms_service_workflow_doc.id_service_workflow_doc"))
    ->join('dr_service', 'dr_service.id_draft', '=', 'dr_service_doc.id_draft')
    ->join('ms_service_workflow', 'ms_service_workflow.id_service_workflow', '=', 'dr_service_doc.id_workflow')
    ->where('dr_service.id_draft', $this->id_transaction)->orderBy('id_draft_doc', 'ASC')->get();
  }

  public function required_docs_new($id_transaction){
    return DrServiceDoc::with('workflowDoc')
    ->where('id_draft', $id_transaction)->orderBy('id_draft_doc', 'ASC')->get();
  }

  public function required_coa_docs($id_transaction){
    return DB::table('dr_service_coa_other')
    ->where('id_transaction', $id_transaction)->orderBy('id_transaction', 'ASC')->get();
  }

  public function required_infos(){
    return null;
  }

  public function service_workflows(){
    return DrServiceWorkFlow::select("dr_service_workflow.*")
    ->join("dr_service as dr_service_child", "dr_service_child.id_transaction", "=", "dr_service_workflow.id_transaction")
    ->join("dr_service", "dr_service.id_draft", "=", "dr_service_child.id_transaction_parent")
    ->where('dr_service.id_draft', $this->id_transaction)->orderBy('sequence')->get();
  }

  public function payments(){
    if ($this->payment_method == 'atlas') {
      return DB::table("dr_service_coa_atlas")->where('id_transaction', $this->id_draft)->orderBy('percentage')->get();
    }elseif ($this->payment_method == 'non_atlas') {
      return DB::table("dr_service_coa_other")->where('id_transaction', $this->id_draft)->orderBy('percentage')->get();
    }else {
      return 'Bank Transfer';
    }
  }
}