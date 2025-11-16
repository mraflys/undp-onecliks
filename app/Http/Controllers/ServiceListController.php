<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB, Session, Cache, Validator;
use App\AgencyUnit;
use App\ServiceList;
use App\Country;
use App\SecUser;
use App\GeneralHelper;
use App\PriceList;
use App\WorkFlow;

use Datatables;
date_default_timezone_set('Asia/Jakarta');

class ServiceListController extends Controller
{
  protected $table = 'ms_service';

  public function index(){
    $data['title'] = 'Service List';
    $data['breadcrumps'] = ['Master', 'Service List'];
    return view('admin.service_list.list', $data);
  }

  public function create() {
    $data['title'] = 'Service List';
    $data['breadcrumps'] = ['Master', 'New Service List'];
    $data['countries'] = Country::list_with_cache();
    $data['agencies'] = AgencyUnit::by_country_id(Session::get('user_id_country'));
    return view('admin.service_list.form', $data);
  }

  public function store(Request $req) {
    try {
      $validator = $this->check($req);
      if ($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
      
      $input = $this->prepare_data($req);
      $input['date_created'] = Date('Y-m-d H:i:s');
      $input['created_by'] = \Auth::user()->user_name;

      $id = DB::table($this->table)->insertGetId($input);
      Cache::forget('service_list');
      return \Redirect::route('service_list.edit', [$id])->with('message_success', 'Data has been saved successfully!');
    }catch(\Exception $e) {
      GeneralHelper::add_log([
        'type' => 'error',
        'description' => $e->getMessage(), 
        'id_user' => \Auth::user()->id_user]);

      return \Redirect::route('service_list.create')
        ->with('message_error', $e->getMessage());
    }
  }

  public function edit($id) {
    $data['title'] = 'Service List';
    $data['breadcrumps'] = ['Master', 'New Service List'];
    $data['countries'] = Country::list_with_cache();
    $data['agencies'] = AgencyUnit::list_with_cache();
    $data['detail'] = ServiceList::find($id);
    return view('admin.service_list.form', $data);
  }

  public function show($id) {
    $data['title'] = 'Service List';
    $data['breadcrumps'] = ['Master', 'New Service List'];
    $data['countries'] = Country::list_with_cache();
    $data['agencies'] = AgencyUnit::list_with_cache();
    $data['detail'] = ServiceList::find($id);
    return view('admin.service_list.form', $data);
  }

  public function destroy($id) {
    try{
      $row = ServiceList::find($id);
      $row->date_deleted = Date('Y-m-d H:i:s');
      $row->deleted_by = \Auth::user()->user_name;
      $row->save();
      Cache::forget('service_list');

      GeneralHelper::add_log(['description' => "DELETE service_list id ".$id, 'id_user' => \Auth::user()->id_user]);
      return response()->back()->with('message', 'success')->setStatusCode(200);

      // return response()->json(['message'=>'success']);

    }catch(\Exception $e){
      GeneralHelper::add_log(['type' => 'error', 'description' => $e->getMessage(), 'id_user' => \Auth::user()->id_user]);
      return response()->back()->with('message', 'error')->setStatusCode(500);

      // return response()->json(['message'=>'error'], 500);
    }
  }

  public function update(Request $req) {
    try {
      $id = $req->service_list;
      $validator = $this->check($req, $id);
      if ($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
     
      $input = $this->prepare_data($req);
      $input['date_updated'] = Date('Y-m-d H:i:s');
      $input['updated_by'] = \Auth::user()->user_name;
      DB::table($this->table)->where('id_service', $id)->update($input);
      Cache::forget('service_list');
      return \Redirect::route('service_list.edit', [$id])->with('message_success', 'Data has been saved successfully!');
    }catch(\Exception $e) {
      GeneralHelper::add_log([
        'type' => 'error',
        'description' => $e->getMessage(), 
        'id_user' => \Auth::user()->id_user]);

      return \Redirect::route('service_list.create')
        ->with('message_error', $e->getMessage());
    }
  }

  function check($request, $id = null) {
    // $additional_code_rule = $id == null ? '|unique:ms_service' : '';
    $additional_code_rule = '';
    return Validator::make($request->all(), [
      // 'service_code' => 'required|max:25'.$additional_code_rule,
      'name' => 'required',
    ]);
  }

  function prepare_data($req) {
    $input['service_code'] = $req->service_code;
    $input['service_name'] = $req->name;
    $input['description'] = $req->description;
    $input['id_agency_unit'] = $req->id_agency_unit;
    $input['is_required_contract'] = $req->is_required_contract == 1 ? true : false;
    $input['is_active'] = $req->is_active;
    
    return $input;
  }

  # worflows
  public function show_workflow($id_service) {
    $detail = ServiceList::find($id_service);
    if (!is_null($detail)) {
      $data['title'] = 'Service List';
      $data['breadcrumps'] = ['Master', 'Service List Detail'];
      $data['detail'] = $detail;
      $data['children'] = $detail->children;
      return view('admin.service_list.children', $data);
    }else{
      return \Redirect::route('service_list.index')->with('message_error', 'Data not found');
    }
  }

  #Manage Child

  public function add_child(Request $req) {
    try{
      $id_service = $req->id_service_parent;
      $parent = ServiceList::find($id_service);
      $new_child = $parent->replicate();
      $new_child->id_service_parent = $parent->id_service;
      $new_child->service_name = $req->service_name;
      $new_child->date_created = Date('Y-m-d H:i:s');
      $new_child->created_by = SecUser::find($req->_sess)->user_name;
      $new_child->save();

      return response()->json(['message' => 'success']);
    }catch(\Exception $e) {
      GeneralHelper::add_log([
        'type' => 'error',
        'description' => $e->getMessage(), 
        'id_user' => $req->_sess]);

      return response()->json(['message' => $e->getMessage()], 500);
    }
  }

  public function update_child(Request $req) {
    try{
      $service = ServiceList::find($req->id_service);
      $service->service_name = $req->service_name;
      $service->date_updated = Date('Y-m-d H:i:s');
      $service->updated_by = SecUser::find($req->_sess)->user_name;
      $service->save();

      return response()->json(['message' => 'success']);
    }catch(\Exception $e) {
      GeneralHelper::add_log([
        'type' => 'error',
        'description' => $e->getMessage(), 
        'id_user' => $req->_sess]);

      return response()->json(['message' => $e->getMessage()], 500);
    }
  }


  public function delete_child($id_service) {
    try{
      $service = ServiceList::destroy($id_service);
    }catch(\Exception $e) {
      GeneralHelper::add_log([
        'type' => 'error',
        'description' => $e->getMessage(), 
        'id_user' => Session::get('user_id')]);
    }

    return redirect()->back();
  }

  # datatables
  public function list(Request $req){
    $where = "ms_service.id_service_parent IS NULL";

    if (!empty($req->id_country)) {
      $where .= " AND ms_agency_unit.id_country = ".$req->id_country;
    }
    if (!empty($req->id_agency_unit)) {
      $where .= " AND ms_service.id_agency_unit = ".$req->id_agency_unit;
    }
    if (!empty($req->status) || $req->status == '0') {
      $where .= " AND ms_service.is_active = ".$req->status;
    }

    $list = ServiceList::select(DB::raw("ms_service.*, ms_agency_unit.agency_unit_name"))
    ->join("ms_agency_unit", "ms_agency_unit.id_agency_unit", '=', 'ms_service.id_agency_unit')
    ->whereRaw($where);
    return Datatables::of($list)
    ->editColumn('is_active', function($list){ return $list->is_active == 1 ? 'Active' : 'Inactive' ; })
    ->addColumn('action', function($list){
      $actions = "<a href='".route('service_list.edit', [$list->id_service])."' class='btn btn-sm btn-clean btn-icon btn-icon-md'><i class='fa fa-edit'></i></a>";
      $actions .= "<a href='#' class='btn btn-sm btn-clean btn-icon btn-icon-md' onclick='deleteRow(".$list->id_service.")'><i class='fa fa-trash'></i></a>&nbsp;";
      $actions .= " <a href='".route('service_list.workflow', [$list->id_service])."' class='btn btn-sm btn-clean btn-icon btn-icon-md' title='Define Workflow'><i class='fa fa-list'></i></a>&nbsp;";

      return $actions;
    })
    ->make(true);
  }

  public function search_by(){
    $where = 'ms_service.id_service IS NOT NULL';
    if (isset($_GET['parent_only'])) $where .= " AND (ms_service.id_service_parent IS NULL OR ms_service.id_service_parent = '')"; 
    if (isset($_GET['id_parent'])) $where .= ' AND ms_service.id_agency_unit = '.$_GET['id_parent']; 
    if (isset($_GET['with_price'])){
      $result = ServiceList::with_active_price($where);
    }else {
      $result = ServiceList::select('id_service', 'service_name')->whereRaw($where)->orderBy('service_name')->get();
    }
    return response()->json(['total' => count($result),'data' => $result]);
  }

  public function show_as_json(Request $req){
    $service = ServiceList::find($req->id_service);
    $data = $service;
    $service_prices = [];
    $group_workflow = [];

    if (!is_null($service)) {
      $active_price = PriceList::active_by_service_id_parent($service->id_service)->first();
      $data['active_price'] = $active_price;
      $workflows = WorkFlow::by_service_parent_id_with_price($service->id_service);
      $data['workflows'] = $workflows;
      $data['required_docs'] = null;
      $data['required_infos'] = null;
      
      if (!is_null($data['workflows'])) {
        foreach ($workflows as $workflow) {
          
          $agency = $workflow->agency;
          if (isset($group_workflow[$workflow->id_service])){
            $group_workflow_item = $group_workflow[$workflow->id_service];
          }else{
            $group_workflow_item['id'] = $workflow->id_service;
            $group_workflow_item['name'] = $workflow->service_name;
            $group_workflow_item['price'] = $workflow->price;
          }
          $group_workflow_item['sub_services'][] = [
            'id_service_workflow' => $workflow->id_service_workflow,
            'name' => $workflow->workflow_name,
            'sequence' => $workflow->sequence,
            'workflow_day' => $workflow->workflow_day,
            'agency' => !is_null($agency) ? $agency->agency_unit_name : 'Requester',
            'price' => !isset($group_workflow_item['sub_services']) || count($group_workflow_item['sub_services']) == 0 ? $workflow->price : ''
          ];

          $service_prices[$workflow->id_service] = $workflow->price;
          $group_workflow[$workflow->id_service] = $group_workflow_item;

          unset($group_workflow_item);
        }
        $seq_1_workflow = WorkFlow::by_service_parent_id_and_first_sequence($service->id_service)->first();
        if (!is_null($seq_1_workflow)) {
          $data['required_docs'] = $seq_1_workflow->docs()->orderBy('sequence')->get();
          $data['required_infos'] = $seq_1_workflow->infos()->orderBy('sequence')->get();
        }
      }

      if (!is_null($active_price)) $active_price->currency;
      $data['total_price'] = (count($service_prices) > 0) ? round(array_sum($service_prices), 2) : 0;
      
      $data['group_workflows'] = array_values($group_workflow);
    }

    return response()->json(['data' => $data]);
  }

  public function workflows_as_json(Request $req){
    $service = ServiceList::find($req->id_service);
    $data = $service;

    if (!is_null($service)) {
      $data['workflows'] = $service->workflows()->orderBy('sequence')->get();
    }
    
    return response()->json(['data' => $data]);
  }
}
