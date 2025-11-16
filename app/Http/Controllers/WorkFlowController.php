<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB, Session, Cache, Validator;
use App\WorkFlow;
use App\SecUser;
use App\AgencyUnit;
use App\ServiceList;
use App\GeneralHelper;
use Datatables;
date_default_timezone_set('Asia/Jakarta');

class WorkFlowController extends Controller
{
  protected $table = 'ms_service_workflow';

  public function show($id){
    $detail = WorkFlow::find($id);
    $data = array();
    $status = 404;
    if (!is_null($detail)) {
      $data['name'] = $detail->workflow_name;
      $data['code'] = $detail->workflow_code;
      $data['service'] = $detail->service->service_name;
      $data['service_id'] = $detail->id_service;
      if ($detail->agency)
        $data['agency'] = $detail->agency->agency_unit_name;
      $data['agency_id'] = $detail->id_agency_unit;
      $data['primary_user_id'] = $detail->id_user_pic_primary;
      $data['alternate_user_id'] = $detail->id_user_pic_alternate;
      $data['id_workflow'] = $detail->id_workflow;
      $data['sequence'] = $detail->sequence;
      $data['is_start_billing'] = $detail->is_start_billing;
      $data['is_start_contract'] = $detail->is_start_contract;
      $status = 200;
    }
    return response()->json(['data' => $data], $status);
  }

  public function store(Request $req){
    try {
      $validator = $this->check($req);
      if ($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
      
      $input = $this->prepare_data($req);
      $input['date_created'] = Date('Y-m-d H:i:s');
      $input['created_by'] = \Auth::user()->user_name;
      $id = DB::table($this->table)->insertGetId($input);
      Cache::forget('workflow_list');
      return \Redirect::route('workflows.edit', [$id])->with('message_success', 'Data has been saved successfully!');
    }catch(\Exception $e) {
      GeneralHelper::add_log([
        'type' => 'error',
        'description' => $e->getMessage(), 
        'id_user' => \Auth::user()->id_user]);

      return \Redirect::route('workflow_list.create')
        ->with('message_error', $e->getMessage());
    }
  }

   public function destroy($id) {
    try{
      $row = ServiceList::find($id);
      $row->date_deleted = Date('Y-m-d H:i:s');
      $row->deleted_by = \Auth::user()->user_name;
      $row->save();
      Cache::forget('workflow_list');

      GeneralHelper::add_log(['description' => "DELETE workflows id ".$id, 'id_user' => \Auth::user()->id_user]);
      return response()->json(['message'=>'success']);

    }catch(\Exception $e){
      GeneralHelper::add_log(['type' => 'error', 'description' => $e->getMessage(), 'id_user' => \Auth::user()->id_user]);
      return response()->json(['message'=>'error'], 500);
      
    }
  }

  public function create() {
    $data['title'] = 'WorkFlow';
    $data['breadcrumps'] = ['Master', 'New WorkFlow'];
    $data['agencies'] = AgencyUnit::list_with_cache();
    $data['users'] = SecUser::list_with_cache();
    $data['service'] = ServiceList::find(\Request()->id_service);
    return view('admin.workflow.form', $data);
  }

  public function edit($id) {
    $data['title'] = 'WorkFlow';
    $data['breadcrumps'] = ['Master', 'New WorkFlow'];
    $data['agencies'] = AgencyUnit::list_with_cache();
    $data['users'] = SecUser::get();
    $data['detail'] = WorkFlow::find($id);
    $data['service'] = $data['detail']->service;
    return view('admin.workflow.form', $data);
  }

  public function update(Request $req) {
    try {
      $id = $req->workflow;
      $validator = $this->check($req, $id);
      if ($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
     
      $input = $this->prepare_data($req);
      $input['date_updated'] = Date('Y-m-d H:i:s');
      $input['updated_by'] = \Auth::user()->user_name;
      DB::table($this->table)->where('id_service_workflow', $id)->update($input);
      Cache::forget('workflow_list');
      return \Redirect::route('workflows.edit', [$id])->with('message_success', 'Data has been saved successfully!');
    }catch(\Exception $e) {
      GeneralHelper::add_log([
        'type' => 'error',
        'description' => $e->getMessage(), 
        'id_user' => \Auth::user()->id_user]);

      return \Redirect::route('workflow_list.create')
        ->with('message_error', $e->getMessage());
    }
  }

  public function update_sequence(Request $req){
    $id = $req->id;
    $arrow = $req->arrow;
    $current_info = WorkFlow::find($id);
    $id_parent = $current_info->id_service;
    $current_sequence = $current_info->sequence;
    $sign = '=';
    $order_by = 'ASC';


    if ($arrow == 'up') {
      $sign = '<';
      $order_by = 'DESC';
    }
    if ($arrow == 'down') $sign = '>';
    $switch = WorkFlow::whereRaw("id_service = '".$id_parent."' AND sequence $sign ".$current_sequence)->orderBy('sequence', $order_by)->first();

    // echo "id_service = '".$id_parent."' AND sequence $sign ".$current_sequence;exit;
    // print_r($switch);exit;

    if (!is_null($switch)) {
      $current_info->sequence = $switch->sequence;
      $current_info->save();

      $switch->sequence = $current_sequence;
      $switch->save();
    }

    return redirect()->back();
  }

  function check($request, $id = null) {
    // $additional_code_rule = $id == null ? '|unique:ms_service' : '';
    $additional_code_rule = '';
    return Validator::make($request->all(), [
      // 'code' => 'max:25'.$additional_code_rule,
      'name' => 'required',
    ]);
  }

  function prepare_data($req) {
    $input['workflow_code'] = $req->workflow_code;
    $input['workflow_name'] = $req->name;
    $input['workflow_day'] = $req->workflow_day;
    $input['id_service'] = $req->id_service;
    $input['is_required_doc'] = $req->is_required_doc;
    $input['is_required_info'] = $req->is_required_info;
    $input['id_agency_unit'] = $req->id_agency_unit;
    $input['sequence'] = $req->sequence;
    $input['is_start_billing'] = $req->is_start_billing;
    $input['is_start_contract'] = $req->is_start_contract;
    $input['id_user_pic_primary'] = $req->id_user_pic_primary;
    $input['id_user_pic_alternate'] = $req->id_user_pic_alternate;
    return $input;
  }

  public function delete_child($id_service) {
    try{
      $service = WorkFlow::destroy($id_service);
    }catch(\Exception $e) {
      GeneralHelper::add_log([
        'type' => 'error',
        'description' => $e->getMessage(), 
        'id_user' => Session::get('user_id')]);
    }

    return redirect()->back();
  }


}