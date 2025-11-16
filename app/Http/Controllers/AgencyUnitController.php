<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB, Session, Cache, Validator;
use App\AgencyUnit;
use App\Country;
use App\GeneralHelper;
use Datatables;
date_default_timezone_set('Asia/Jakarta');

class AgencyUnitController extends Controller
{
  protected $table = 'ms_agency_unit';

  // public function __construct(){
  //   $this->middleware(function ($request, $next){
  //     if (session('user_id') != null) {
  //       return $next($request);
  //     }else{
  //       return redirect()->route('login');
  //     }
  //   });
  // }

  public function index(){
    $data['title'] = 'Unit';
    $data['breadcrumps'] = ['Master', 'Unit'];
    return view('admin.agency_unit', $data);
  }

  public function create() {
    $data['title'] = 'Unit';
    $data['breadcrumps'] = ['Master', 'New Unit'];
    $data['countries'] = Country::list_with_cache();
    return view('admin.agency_unit_form', $data);
  }

  public function store(Request $req) {
    try {
      $validator = $this->check($req);
      if ($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
      
      $input = $this->prepare_data($req);
      $input['date_created'] = Date('Y-m-d H:i:s');
      $input['created_by'] = \Auth::user()->user_name;
      $id = DB::table($this->table)->insertGetId($input);
      Cache::forget('agency_units');
      GeneralHelper::add_log(['description' => "Add AgencyUnit ".$id, 'id_user' => \Auth::user()->id_user]);

      return \Redirect::route('agency_units.edit', [$id])->with('message_success', 'Data has been saved successfully!');
    }catch(\Exception $e) {
      GeneralHelper::add_log([
        'type' => 'error',
        'description' => $e->getMessage(), 
        'id_user' => \Auth::user()->id_user]);

      return \Redirect::route('agency_units.create')
        ->with('message_error', $e->getMessage());
    }
  }

  public function edit($id) {
    $data['title'] = 'Unit';
    $data['breadcrumps'] = ['Master', 'New Unit'];
    $data['countries'] = Country::list_with_cache();
    $data['detail'] = AgencyUnit::find($id);
    return view('admin.agency_unit_form', $data);
  }

  public function show($id) {
    $data['title'] = 'Unit';
    $data['breadcrumps'] = ['Master', 'New Unit'];
    $data['countries'] = Country::list_with_cache();
    $data['detail'] = AgencyUnit::find($id);
    return view('admin.agency_unit_form', $data);
  }

  public function destroy($id) {
    try{
      $row = AgencyUnit::find($id);
      $row->date_deleted = Date('Y-m-d H:i:s');
      $row->deleted_by = \Auth::user()->user_name;
      $row->save();
      Cache::forget('agency_units');

      GeneralHelper::add_log(['description' => "DELETE agency_units id ".$id, 'id_user' => \Auth::user()->id_user]);
      return response()->json(['message'=>'success']);

    }catch(\Exception $e){
      GeneralHelper::add_log(['type' => 'error', 'description' => $e->getMessage(), 'id_user' => \Auth::user()->id_user]);
      return response()->json(['message'=>'error'], 500);
      
    }
  }

  public function update(Request $req) {
    try {
      $id = $req->agency_unit;
      $validator = $this->check($req, $id);
      if ($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
     
      $input = $this->prepare_data($req);
      $input['date_updated'] = Date('Y-m-d H:i:s');
      $input['updated_by'] = \Auth::user()->user_name;
      DB::table($this->table)->where('id_agency_unit', $id)->update($input);
      Cache::forget('agency_units');
      GeneralHelper::add_log(['description' => "Update AgencyUnit ".$id, 'id_user' => \Auth::user()->id_user]);

      return \Redirect::route('agency_units.edit', [$id])->with('message_success', 'Data has been saved successfully!');
    }catch(\Exception $e) {
      GeneralHelper::add_log([
        'type' => 'error',
        'description' => $e->getMessage(), 
        'id_user' => \Auth::user()->id_user]);

      return \Redirect::route('agency_units.create')
        ->with('message_error', $e->getMessage());
    }
  }

  function check($request, $id = null) {
    $additional_code_rule = $id == null ? '|unique:ms_agency_unit' : '';
    return Validator::make($request->all(), [
      'agency_unit_code' => 'required|max:25'.$additional_code_rule,
      'name' => 'required',
    ]);
  }

  function prepare_data($req) {
    $input['agency_unit_code'] = $req->agency_unit_code;
    $input['agency_unit_name'] = $req->name;
    $input['finance_email'] = $req->finance_email;
    $input['description'] = $req->description;
    $input['id_country'] = $req->country;
    $input['is_active'] = $req->is_active;
    $input['is_service_unit'] = $req->is_service_agency;
    
    return $input;
  }

  # datatables
  public function list(Request $req){
    $where = "id_agency_unit_parent IS NULL";
    if (!empty($req->id_country)) {
      $where .= " AND ms_agency_unit.id_country = ".$req->id_country;
    }
    if (!empty($req->id_agency_unit)) {
      $where .= " AND id_agency_unit_parent = ".$req->id_agency_unit;
    }
    if (!empty($req->status) || $req->status == '0') {
      $where .= " AND ms_agency_unit.is_active = ".$req->status;
    }
    $list = AgencyUnit::select(DB::raw("ms_agency_unit.*, ms_country.country_name"))
    ->join("ms_country", "ms_country.id_country", '=', 'ms_agency_unit.id_country')
    ->whereRaw($where);
    return Datatables::of($list)
    ->editColumn('is_active', function($list){ return $list->is_active == 1 ? 'Active' : 'Inactive' ; })
    ->addColumn('action', function($list){
      $actions = "<a href='".route('agency_units.edit', [$list->id_agency_unit])."' class='btn btn-sm btn-clean btn-icon btn-icon-md'><i class='fa fa-edit'></i></a>";
      $actions .= "<a href='#' class='btn btn-sm btn-clean btn-icon btn-icon-md' onclick='deleteRow(".$list->id_agency_unit.")'><i class='fa fa-trash'></i></a>&nbsp;";
      return $actions;
    })
    ->make(true);
  }

  #api
  public function search_by(){
    $where = 'id_agency_unit IS NOT NULL';
    if (!isset($_GET['all'])) {
      $where .= " AND (id_agency_unit_parent IS NULL OR id_agency_unit_parent = '' )"; 
    }else{
      $where .= ' AND id_agency_unit_parent IS NOT NULL'; 
    }

    if (isset($_GET['with_service'])) $where .= " AND is_service_unit = 1";

    if (isset($_GET['id_country'])) {
      $where .= ' AND id_country = '.$_GET['id_country']; 
    }
    if (isset($_GET['id_parent'])) {
      $where .= ' AND id_agency_unit_parent = '.$_GET['id_parent']; 
    }
    if (isset($_GET['service_only'])) {
      $where = 'id_agency_unit_parent = '.$_GET['id_parent'].' AND is_service_unit IS NOT NULL'; 
    }
    $result = AgencyUnit::select('id_agency_unit', 'agency_unit_name', 'agency_unit_code')->whereRaw($where)->orderBy('agency_unit_name')->get();
    return response()->json(['data' => $result]);
  }
}
