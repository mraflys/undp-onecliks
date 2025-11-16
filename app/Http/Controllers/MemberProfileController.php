<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB, Session, Cache, Validator;
use App\SecRole;
use App\ServiceList;
use App\Country;
use App\SecUser;
use App\GeneralHelper;
date_default_timezone_set('Asia/Jakarta');

class MemberProfileController extends Controller
{
  protected $table = 'sec_user';
  
  public function __construct(){
    $this->middleware(function ($request, $next){
      if (session('user_id') != null) {
        return $next($request);
      }else{
        return redirect()->route('login');
      }
    });
  }
  
  public function show() {
    $data['title'] = 'User';
    $data['breadcrumps'] = ['Member Area', 'My Profile'];
    $data['detail'] = SecUser::find(Session::get('user_id'));
    return view('member.profile.form', $data);
  }

  public function update(Request $req) {
    try {
      $id = Session::get('user_id');
      $validator = $this->check($req, $id);
      if ($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
     
      $input = $this->prepare_data($req);
      $input['date_updated'] = Date('Y-m-d H:i:s');
      $input['updated_by'] = \Auth::user()->user_name;

      if (!empty($req->password)) {
      	$input['user_password'] = sha1($req->password);
      }
      
      DB::table($this->table)->where('id_user', $id)->update($input);
      return \Redirect::route('myprofile.show')->with('message_success', 'Data has been saved successfully!');
    }catch(\Exception $e) {
      GeneralHelper::add_log([ 'type' => 'error', 'description' => $e->getMessage(), 'id_user' => \Auth::user()->id_user]);
      return \Redirect::route('myprofile.show')->with('message_error', $e->getMessage());
    }
  }

 	function check($request, $id = null) {
    $additional_code_rule = $id == null ? '|unique:sec_user' : '';
    $additional_code_rule = '';
    return Validator::make($request->all(), [
      'first_name' => 'required',
      'last_name' => 'required',
      'id_agency_unit' => 'required',
    ]);
  }

  function prepare_data($req) {
    $input['first_name'] = $req->first_name;
    $input['last_name'] = $req->last_name;
    $input['person_name'] = $req->first_name.' '.$req->last_name;
    $input['id_agency_unit'] = $req->id_agency_unit;
    // $input['is_using_LDAP'] = $req->is_using_LDAP;
    
    return $input;
  }
}
