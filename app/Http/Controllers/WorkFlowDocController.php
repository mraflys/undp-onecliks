<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB, Session, Cache, Validator;
use App\WorkFlow;
use App\WorkFlowDoc;
use App\GeneralHelper;
date_default_timezone_set('Asia/Jakarta');

class WorkFlowDocController extends Controller
{
  protected $table = 'ms_service_workflow_doc';

  public function index(){
    $data['title'] = 'WorkFlowDoc';
    $data['workflow'] = Workflow::find(\Request()->id_service_workflow);
    $data['list'] = WorkFlowDoc::where('id_service_workflow', \Request()->id_service_workflow)->orderBy('sequence')->get();
    $data['breadcrumps'] = ['Master', 'WorkFlowDoc'];
    return view('admin.workflow_doc.list', $data);
  }

  public function create() {
    $data['title'] = 'WorkFlowDoc';
    $data['breadcrumps'] = ['Master', 'New WorkFlowDoc'];
    return view('admin.workflow_doc.form', $data);
  }

  public function store(Request $req) {
    try {
      $validator = $this->check($req);
      if ($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
      
      $input = $this->prepare_data($req);
      $input['date_created'] = Date('Y-m-d H:i:s');
      $input['created_by'] = \Auth::user()->user_name;
      $id = DB::table($this->table)->insertGetId($input);
      Cache::forget('WorkFlowDoc');
      return \Redirect::route('workflow_docs.edit', [$id])->with('message_success', 'Data has been saved successfully!');
    }catch(\Exception $e) {
      GeneralHelper::add_log([
        'type' => 'error',
        'description' => $e->getMessage(), 
        'id_user' => \Auth::user()->id_user]);

      return \Redirect::route('workflow_docs.create')
        ->with('message_error', $e->getMessage());
    }
  }

  public function edit($id) {
    $data['title'] = 'WorkFlowDoc';
    $data['breadcrumps'] = ['Master', 'Edit WorkFlowDoc'];
    $data['detail'] = WorkFlowDoc::find($id);
    return view('admin.workflow_doc.form', $data);
  }

  public function show($id) {
    $data['title'] = 'WorkFlowDoc';
    $data['breadcrumps'] = ['Master', 'WorkFlowDoc'];
    $data['detail'] = WorkFlowDoc::find($id);
    return view('admin.workflow_doc.form', $data);
  }

  public function destroy($id) {
    try{
      $row = WorkFlowDoc::find($id);
      $row->date_deleted = Date('Y-m-d H:i:s');
      $row->deleted_by = \Auth::user()->user_name;
      $row->save();
      Cache::forget('WorkFlowDoc');

      GeneralHelper::add_log(['description' => "DELETE WorkFlowDoc id ".$id, 'id_user' => \Auth::user()->id_user]);
      return response()->json(['message'=>'success']);

    }catch(\Exception $e){
      GeneralHelper::add_log(['type' => 'error', 'description' => $e->getMessage(), 'id_user' => \Auth::user()->id_user]);
      return response()->json(['message'=>'error'], 500);
      
    }
  }

  public function update(Request $req) {
    try {
      $id = $req->workflow_doc;
      $validator = $this->check($req, $id);
      if ($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
     
      $input = $this->prepare_data($req);
      $input['date_updated'] = Date('Y-m-d H:i:s');
      $input['updated_by'] = \Auth::user()->user_name;
      DB::table($this->table)->where('id_service_workflow_doc', $id)->update($input);
      // echo $id;exit;
      Cache::forget('WorkFlowDoc');
      return \Redirect::route('workflow_docs.edit', [$id])->with('message_success', 'Data has been saved successfully!');
    }catch(\Exception $e) {
      GeneralHelper::add_log([
        'type' => 'error',
        'description' => $e->getMessage(), 
        'id_user' => \Auth::user()->id_user]);

      return \Redirect::route('workflow_docs.edit', [$req->workflow_doc])
        ->with('message_error', $e->getMessage());
    }
  }

  public function update_sequence(Request $req){
    $id = $req->id;
    $arrow = $req->arrow;
    $id_parent = $req->id_service_workflow;
    $current_info = WorkFlowDoc::find($id);
    $current_sequence = $current_info->sequence;
    $sign = '=';
    $order_by = 'ASC';

    if ($arrow == 'up') {
      $sign = '<';
      $order_by = 'DESC';
    }
    if ($arrow == 'down') $sign = '>';
      
    $switch = WorkFlowDoc::whereRaw("id_service_workflow = '".$id_parent."' AND sequence $sign ".$current_sequence)->orderBy('sequence', $order_by)->first();

    if (!is_null($switch)) {
      $current_info->sequence = $switch->sequence;
      $current_info->save();

      $switch->sequence = $current_sequence;
      $switch->save();
    }

    return redirect()->back();
  }


  function check($request, $id = null) {
    return Validator::make($request->all(), [
      'document_name' => 'required',
      'description' => 'required',
      'is_mandatory' => 'required',
      'sequence' => 'required',
    ]);
  }

  function prepare_data($req) {
    $input['id_service_workflow'] = $req->id_service_workflow;
    $input['document_name'] = $req->document_name;
    $input['description'] = $req->description;
    $input['is_mandatory'] = $req->is_mandatory;
    $input['sequence'] = $req->sequence;
    
    return $input;
  }
}
