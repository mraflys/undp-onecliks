<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB, Session, Cache, Validator;
use App\Coa;
use App\Country;
use App\GeneralHelper;
use Datatables;
date_default_timezone_set('Asia/Jakarta');

class CoaController extends Controller
{
  protected $table = 'ms_coa';

  public function index(){
    $data['title'] = 'COA';
    $data['breadcrumps'] = ['Master', 'COA'];
    return view('admin.coa.list', $data);
  }

  public function create() {
    $data['title'] = 'COA';
    $data['breadcrumps'] = ['Master', 'New COA'];
    return view('admin.coa.form', $data);
  }

  public function store(Request $req) {
    try {
      $validator = $this->check($req);
      if ($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
      
      $input = $this->prepare_data($req);
      $input['date_created'] = Date('Y-m-d H:i:s');
      $input['created_by'] = \Auth::user()->user_name;
      $id = DB::table($this->table)->insertGetId($input);
      Cache::forget('coas');
      return \Redirect::route('coas.edit', [$id])->with('message_success', 'Data has been saved successfully!');
    }catch(\Exception $e) {
      GeneralHelper::add_log([
        'type' => 'error',
        'description' => $e->getMessage(), 
        'id_user' => \Auth::user()->id_user]);

      return \Redirect::route('coas.create')
        ->with('message_error', $e->getMessage());
    }
  }

  public function edit($id) {
    $data['title'] = 'COA';
    $data['breadcrumps'] = ['Master', 'Edit COA'];
    $data['detail'] = Coa::find($id);
    return view('admin.coa.form', $data);
  }

  public function show($id) {
    $data['title'] = 'COA';
    $data['breadcrumps'] = ['Master', 'COA'];
    $data['detail'] = Coa::find($id);
    return view('admin.coa.form', $data);
  }

  public function destroy($id) {
    try{
      $row = Coa::find($id);
      $row->date_deleted = Date('Y-m-d H:i:s');
      $row->deleted_by = \Auth::user()->user_name;
      $row->save();
      Cache::forget('coas');

      GeneralHelper::add_log(['description' => "DELETE coas id ".$id, 'id_user' => \Auth::user()->id_user]);
      return response()->json(['message'=>'success']);

    }catch(\Exception $e){
      GeneralHelper::add_log(['type' => 'error', 'description' => $e->getMessage(), 'id_user' => \Auth::user()->id_user]);
      return response()->json(['message'=>'error'], 500);
      
    }
  }

  public function update(Request $req) {
    try {
      $id = $req->coa;
      $validator = $this->check($req, $id);
      if ($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
     
      $input = $this->prepare_data($req);
      $input['date_updated'] = Date('Y-m-d H:i:s');
      $input['updated_by'] = \Auth::user()->user_name;
      DB::table($this->table)->where('id_master_coa', $id)->update($input);
      Cache::forget('coas');
      return \Redirect::route('coas.edit', [$id])->with('message_success', 'Data has been saved successfully!');
    }catch(\Exception $e) {
      GeneralHelper::add_log([
        'type' => 'error',
        'description' => $e->getMessage(), 
        'id_user' => \Auth::user()->id_user]);

      return \Redirect::route('coas.create')
        ->with('message_error', $e->getMessage());
    }
  }

  function check($request, $id = null) {
    return Validator::make($request->all(), [
      // 'opu' => 'required',
      'fund' => 'required',
      'imp_agent' => 'required',
      'donor' => 'required',
      'pcbu' => 'required',
      'project' => 'required',
      'activities' => 'required',
    ]);
  }

  function prepare_data($req) {
    $input['opu'] = $req->opu;
    $input['fund'] = $req->fund;
    $input['imp_agent'] = $req->imp_agent;
    $input['donor'] = $req->donor;
    $input['pcbu'] = $req->pcbu;
    $input['project'] = $req->project;
    $input['activities'] = $req->activities;
    
    return $input;
  }

  # datatables
  public function list(Request $req){
    $list = Coa::select(DB::raw("ms_coa.*"))->whereRaw("id_master_coa IS NOT NULL");
    return Datatables::of($list)
    ->addColumn('action', function($list){
      $actions = "<a href='".route('coas.edit', [$list->id_master_coa])."' class='btn btn-sm btn-clean btn-icon btn-icon-md'><i class='fa fa-edit'></i></a>";
      $actions .= "<a href='#' class='btn btn-sm btn-clean btn-icon btn-icon-md' onclick='deleteRow(".$list->id_master_coa.")'><i class='fa fa-trash'></i></a>&nbsp;";
      return $actions;
    })
    ->make(true);
  }
}
