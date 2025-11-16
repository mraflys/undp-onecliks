<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB, Session, Cache, Validator;
use App\GeneralHelper;
date_default_timezone_set('Asia/Jakarta');

class AppConfigController extends Controller
{
  protected $table = 'app_configs';

  public function index(){
    $data['title'] = 'App Config';
    $data['breadcrumps'] = ['Master', 'App Config'];
    $data['detail'] = DB::table($this->table)->where('id', 1)->first();
    return view('admin.app_config.form', $data);
  }

  public function update(Request $req) {
    try {
      $id = 1;
      $validator = $this->check($req, $id);
      if ($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
     
      $input = $this->prepare_data($req);
      $input['updated_by'] = \Auth::user()->id_user;

      $logo = $req->logo;
      if (!is_null($logo)){
        $logo_name = time().'.'.$logo->getClientOriginalExtension();
        $input['logo'] = 'assets/images/logo/'.$logo_name;
        $logo->move(public_path('assets/images/logo'), $logo_name);
      }
      
      $banner = $req->banner;
      if (!is_null($banner)){
        $banner_name = time().'.'.$banner->getClientOriginalExtension();
        $input['banner'] = 'assets/images/banner/'.$banner_name;
        $banner->move(public_path('assets/images/banner'), $banner_name);
      }

      DB::table($this->table)->where('id', 1)->update($input);
      Cache::forget('app_configs');
      
      return \Redirect::route('app_configs.index', [$id])->with('message_success', 'Data has been saved successfully!');
    }catch(\Exception $e) {
      GeneralHelper::add_log([
        'type' => 'error',
        'description' => $e->getMessage(), 
        'id_user' => \Auth::user()->id_user]);

      return \Redirect::route('app_configs.index', [1])
        ->with('message_error', $e->getMessage());
    }
  }

  function check($request, $id = null) {
    $additional_code_rule = $id == null ? '|unique:app_configs' : '';
    return Validator::make($request->all(), [
      'short_description' => 'required|max:255'.$additional_code_rule,
      'name' => 'required',
    ]);
  }

  function prepare_data($req) {
    $input['name'] = $req->name;
    $input['description'] = $req->description;
    $input['short_description'] = $req->short_description;
        
    return $input;
  }
}
