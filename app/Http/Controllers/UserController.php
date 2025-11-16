<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB, Session, Cache, Validator;
use App\SecRole;
use App\ServiceList;
use App\Country;
use App\SecUser;
use App\GeneralHelper;
use Datatables;
date_default_timezone_set('Asia/Jakarta');

class UserController extends Controller
{
  protected $table = 'sec_user';

  public function index(){
    $data['title'] = 'User';
    $data['breadcrumps'] = ['Master', 'User'];
    return view('admin.user.list', $data);
  }

  public function create() {
    $data['title'] = 'User';
    $data['breadcrumps'] = ['Master', 'New User'];
    $data['countries'] = Country::list_with_cache();
    $data['roles'] = SecRole::list_with_cache();
    return view('admin.user.form', $data);
  }

  public function store(Request $req) {
    try {
      $validator = $this->check($req);
      if ($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
      
      $input = $this->prepare_data($req);
      $input['date_created'] = Date('Y-m-d H:i:s');
      $input['created_by'] = \Auth::user()->user_name;
      if (!empty($req->password))
        $input['user_password'] = sha1($req->password);

      $id = DB::table($this->table)->insertGetId($input);
      Cache::forget('sec_users');
      return \Redirect::route('users.edit', [$id])->with('message_success', 'Data has been saved successfully!');
    }catch(\Exception $e) {
      GeneralHelper::add_log([
        'type' => 'error',
        'description' => $e->getMessage(), 
        'id_user' => \Auth::user()->id_user]);

      return \Redirect::route('userss.create')
        ->with('message_error', $e->getMessage());
    }
  }

  public function edit($id) {
    $data['title'] = 'User';
    $data['breadcrumps'] = ['Master', 'Edit User'];
    $data['countries'] = Country::list_with_cache();
    $data['roles'] = SecRole::list_with_cache();
    $data['detail'] = SecUser::find($id);
    return view('admin.user.form', $data);
  }

  public function show($id) {
    $data['title'] = 'User';
    $data['breadcrumps'] = ['Master', 'Edit User'];
    $data['countries'] = Country::list_with_cache();
    $data['roles'] = SecRole::list_with_cache();
    $data['detail'] = SecUser::find($id);
    return view('admin.user.form', $data);
  }

  public function destroy($id) {
    try{
      $row = SecUser::find($id);
      $row->date_deleted = Date('Y-m-d H:i:s');
      $row->deleted_by = \Auth::user()->user_name;
      $row->save();
      Cache::forget('sec_users');

      GeneralHelper::add_log(['description' => "DELETE user id ".$id, 'id_user' => \Auth::user()->id_user]);
      return response()->json(['message'=>'success']);

    }catch(\Exception $e){
      GeneralHelper::add_log(['type' => 'error', 'description' => $e->getMessage(), 'id_user' => \Auth::user()->id_user]);
      return response()->json(['message'=>'error'], 500);
    }
  }

  public function update(Request $req) {
    try {
      $id = $req->user;
      $validator = $this->check($req, $id);
      if ($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
     
      $input = $this->prepare_data($req);
      $input['date_updated'] = Date('Y-m-d H:i:s');
      $input['updated_by'] = \Auth::user()->user_name;
      if (!empty($req->password))
        $input['user_password'] = sha1($req->password);
      DB::table($this->table)->where('id_user', $id)->update($input);
      Cache::forget('sec_users');
      return \Redirect::route('users.edit', [$id])->with('message_success', 'Data has been saved successfully!');
    }catch(\Exception $e) {
      GeneralHelper::add_log([
        'type' => 'error',
        'description' => $e->getMessage(), 
        'id_user' => \Auth::user()->id_user]);

      return \Redirect::route('users.create')
        ->with('message_error', $e->getMessage());
    }
  }

  function check($request, $id = null) {
    $additional_code_rule = $id == null ? '|unique:sec_user' : '';
    $additional_code_rule = '';
    return Validator::make($request->all(), [
      'user_name' => 'required|max:50'.$additional_code_rule,
      // 'email' => 'required|max:25'.$additional_code_rule,
      'first_name' => 'required',
      'last_name' => 'required',
    ]);
  }

  function prepare_data($req) {
    $input['user_name'] = $req->user_name;
    $input['email'] = $req->user_name;
    $input['first_name'] = $req->first_name;
    $input['last_name'] = $req->last_name;
    $input['person_name'] = $req->first_name.' '.$req->last_name;
    $input['id_role'] = $req->id_role;
    $input['id_agency_unit'] = $req->id_agency_unit;
    $input['id_country'] = $req->id_country;
    $input['is_internal_user'] = $req->is_internal_user;
    $input['is_using_LDAP'] = $req->is_using_LDAP;
    $input['external_fee_percentage'] = $req->external_fee_percentage;
    $input['is_active'] = $req->is_active;
    
    return $input;
  }

 
  public function show_as_json($id_user){
    $row = SecUser::find($id_user);
    return response()->json(['data' => $row]);
  }

  # datatables
  public function list(Request $req){
    $list = SecUser::select(DB::raw("sec_user.*, ms_agency_unit.agency_unit_name, role_name, country_name"))
    ->join("ms_agency_unit", "ms_agency_unit.id_agency_unit", '=', 'sec_user.id_agency_unit', 'LEFT')
    ->join("ms_country", "ms_country.id_country", '=', 'sec_user.id_country', 'LEFT')
    ->join("sec_role", "sec_role.id_role", '=', 'sec_user.id_role', 'LEFT')
    ->whereRaw("sec_user.id_user IS NOT NULL");
    
    if (isset($req->with_deleted) && $req->with_deleted == 'true') {
     $list->withTrashed()->whereRaw("sec_user.date_deleted IS NOT NULL"); 
    }
    
    return Datatables::of($list)
    ->editColumn('is_active', function($list){ return $list->is_active == 1 ? 'Active' : 'Inactive' ; })
    ->addColumn('action', function($list){
      if (!$list->trashed()) {
        $actions = "<a href='".route('users.edit', [$list->id_user])."' class='btn btn-sm btn-clean btn-icon btn-icon-md'><i class='fa fa-edit'></i></a>";
        $actions .= "<a href='#' class='btn btn-sm btn-clean btn-icon btn-icon-md' onclick='deleteRow(".$list->id_user.")'><i class='fa fa-trash'></i></a>&nbsp;";
      }else{
        $actions = "<a href='".route('soft_deleted_users.revive', ['id'=>$list->id_user])."' class='btn btn-sm btn-clean btn-icon btn-icon-md' 
        title='Revive'><i class='fa fa-undo'></i></a>";
      }
      return $actions;
    })
    ->make(true);
  }
}
