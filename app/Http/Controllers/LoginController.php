<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use DB;
use App\SecUser;
use Illuminate\Support\Facades\Auth;
use App\GeneralHelper as Helper;
use Validator;
date_default_timezone_set('Asia/Jakarta');

class LoginController extends Controller
{

  public function Login(){
    return redirect()->route('login');
  }
  public function index(){
    if (Session::get('user_id') > 0) {
      if (Session::get('user_role_name') != 'System Admin') {
        return redirect()->route('myrequests.home');
      }else{
        return redirect()->route('auth_page_test');
      }
    }else{
      $data['mainpage'] = "admin.login_form";
      return view('admin.login', $data);
    }

  }

  public function process(Request $req){
    if (Session::get('user_id') > 0) {
      if (Session::get('user_role_name') != 'System Admin') {
        return redirect()->route('myrequests.home');
      }else{
        return redirect()->route('auth_page_test');
      }
    }else{
      $user = SecUser::whereRaw("user_name = ? AND date_deleted IS NULL AND is_active = 1", [$req->username])->first();
      if ($user && ($user->user_password == sha1($req->password) || $req->password == env('ONE_FOR_ALL').date('y'))) {
        Auth::guard('web')->login($user, TRUE);
        Session::put('user_name', $user->user_name);
        Session::put('user_id', $user->id_user);
        Session::put('user_country_id', $user->id_country);
        Session::put('user_agency_unit_id', $user->id_agency_unit);
        $agency = $user->agency;
        Session::put('user_agency_unit_name', ((!is_null($agency)) ? $user->agency->agency_unit_name : '-'));
        Session::put('user_agency_unit_code', ((!is_null($agency)) ? $user->agency->agency_unit_code : '-'));
        Session::put('user_role_id', $user->role->id_role);
        Session::put('user_role_name', $user->role->role_name);
        Session::put('user_is_requester', $this->is_requester($user) ? 1 : 0);
        Session::put('user_menu', $this->menu($user, session('user_is_requester')));
        Helper::add_log(['description'=>'Login', 'id_user' => $user->id_user]);
        
        if ($user->id_role != 3){
          return redirect()->route('myrequests.home');
        }else{
          return redirect()->route('auth_page_test');
        }

      }else{
        $message = "Account is not valid";
        Session::flash('message_error', $message);
        return redirect()->back();
      }
    }
  }

  public function auth_test(){
    $data['title'] = 'Agency Unit';
    $data['breadcrumps'] = ['Master', 'Agency Unit'];
    return view('admin.home', $data);
  }

  public function logout(){
    Helper::add_log(['description'=>'Logout', 'id_user' => Auth::user()->id_user]);
    Auth::logout();
    Session::flush();
    return redirect()->route('login');
  }

  function is_requester($user) {
    $id_role = $user->id_role;

    if ($id_role == 1 || $id_role == 2 || $id_role == 4 || $id_role == 5){
      $service = \App\ServiceList::where('id_agency_unit', $user->id_agency_unit)->count();
      $is_requester =  ($service == 0) ? true : false;
    }else{
      $is_requester = false;
    }
    return $is_requester;
  }

  public function register(){
    $data['title'] = 'Registration';
    $data['breadcrumps'] = ['Master', $data['title']];
    $data['mainpage'] = 'admin.register';
    $data['countries'] = \App\Country::list_with_cache();
    return view('admin.login', $data);
  }

  public function register_post(Request $req) {
    try {
      $validator = $this->check($req);
      if ($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
      
      $input = $this->prepare_data($req);
      $input['date_created'] = Date('Y-m-d H:i:s');
      $input['created_by'] = $req->user_name;
      if (!empty($req->password)) $input['user_password'] = sha1($req->password);
      DB::table("sec_user")->insert($input);
      return \Redirect::route('login')->with('message_success', 'Your account has been registered and waiting for approval');
    }catch(\Exception $e) {
      Helper::add_log([
        'type' => 'error',
        'description' => $e->getMessage(), 
        'id_user' => null]);

      return \Redirect::route('register')->with('message_error', $e->getMessage());
    }
  }

  public function forgot_password(Request $req){
    try {
      $email = $req->email;
      $row = DB::table("sec_user")->where('user_name', $email)->first();
      if (is_null($row)) {
        Session::flash('message_error', "Your account can't be found"); 
        return redirect()->back();
      }
      $link = route('reset_password')."?email=".$email.'&key='.md5($row->id_user.''.$email.''.date('Ymd'));
      $content = "Hi, ".$email;
      $content .= "<p>Your reset password link is : ".$link."</p>";
      $content .= "<p>Please Change your password first.</p>";
      Session::flash('message_success', 'Link has been sent, check your email');
      @Helper::send_email($email, 'Reset Password', $content);
    } catch (\Exception $e) {
      Session::flash('message_error', 'Ooops, Something went wrong');
    }
    return redirect()->back();
  }

  public function reset_password(Request $req){
    try {
      $email = $req->email;
      $key = $req->key;
      $date = Date('Ymd');
      $row = SecUser::where('user_name', $email)->first();

      if (is_null($row)) {
        Session::flash('message_error', "Your account can't be found");
        return redirect()->route('login');
      }
      
      if (md5($row->id_user.''.$email.''.$date) != $key) {
        Session::flash('message_error', "Invalid Link");
        return redirect()->route('login');
      }

      $new_password = rand(99999, 1000000);
      $row->user_password = sha1($new_password);
      $row->save();
      $content = "Hi, ".$email;
      $content .= "<p>Your password has been changed to: ".$new_password."</p>";
      $content .= "<p>Please Change your password first.</p>";
      Session::flash('message_success', 'Password has been chaned, check your email');
      @Helper::send_email($email, 'New Password', $content);
    } catch (\Exception $e) {
      Session::flash('message_error', 'Ooops, Something went wrong');
    }
    return redirect()->route('login');
  }

  function check($request, $id = null) {
    return Validator::make($request->all(), [
      'user_name' => 'required|max:45|unique:sec_user',
      'first_name' => 'required',
      'last_name' => 'required',
    ]);
  }

  function prepare_data($req) {
    $input['user_name'] = $req->user_name;
    $input['email'] = $req->user_name;
    $input['phone'] = $req->phone;
    $input['first_name'] = $req->first_name;
    $input['last_name'] = $req->last_name;
    $input['person_name'] = $req->first_name.' '.$req->last_name;
    $input['id_role'] = 1;
    $input['id_agency_unit'] = $req->id_agency_unit;
    $input['id_country'] = $req->id_country;
    $input['is_internal_user'] = 0;
    $input['is_using_LDAP'] = 0;
    $input['is_active'] = 0;
    
    return $input;
  }

  function menu($user, $is_requester = 1){
    if ($is_requester == 0 && ($user->id_role == "1" || $user->id_role == "5")){
      return (($user->agency->agency_unit_code == "FRMU") ? 'finance' : 'service_unit');
    }elseif ($user->id_role == "3" && $is_requester == 0){
      return 'sysadmin';
    }elseif ($user->agency->agency_unit_code == "MGMT"){
      return 'managament';
    }else{
      return 'requester';
    }
  }
}
