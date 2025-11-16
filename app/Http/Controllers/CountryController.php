<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB, Session, Cache, Validator;
use App\Country;
use App\GeneralHelper;
use Datatables;
date_default_timezone_set('Asia/Jakarta');

class CountryController extends Controller
{
  protected $table = 'ms_country';

  public function index(){
    $data['title'] = 'Country';
    $data['breadcrumps'] = ['Master', 'Country'];
    return view('admin.country.list', $data);
  }

  public function create() {
    $data['title'] = 'Country';
    $data['breadcrumps'] = ['Master', 'New Country'];
    return view('admin.country.form', $data);
  }

  public function store(Request $req) {
    try {
      $validator = $this->check($req);
      if ($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
      
      $input = $this->prepare_data($req);
      $input['date_created'] = Date('Y-m-d H:i:s');
      $input['created_by'] = \Auth::user()->user_name;
      $image_name = time().'.'.$req->file->getClientOriginalExtension();
      $input['country_image_path'] = 'assets/images/flag/'.$image_name;
      $req->file->move(public_path('assets/images/flag'), $image_name);

      $id = DB::table($this->table)->insertGetId($input);
      Cache::forget('countries');
      return \Redirect::route('countries.edit', [$id])->with('message_success', 'Data has been saved successfully!');

    }catch(\Exception $e) {
      GeneralHelper::add_log([
        'type' => 'error',
        'description' => $e->getMessage(), 
        'id_user' => \Auth::user()->id_user]);

      return \Redirect::route('countries.create')->with('message_error', $e->getMessage());

    }
  }

  public function edit($id) {
    $data['title'] = 'Country';
    $data['breadcrumps'] = ['Master', 'New Country'];
    $data['detail'] = Country::find($id);
    return view('admin.country.form', $data);
  }

  public function show($id) {
    $data['title'] = 'Country';
    $data['breadcrumps'] = ['Master', 'New Country'];
    $data['countries'] = Country::list_with_cache();
    $data['detail'] = Country::find($id);
    return view('admin.country.form', $data);
  }

  public function destroy($id) {
    try{
      $row = Country::find($id);
      $row->date_deleted = Date('Y-m-d H:i:s');
      $row->deleted_by = \Auth::user()->user_name;
      $row->save();
      Cache::forget('countries');

      GeneralHelper::add_log(['description' => "DELETE countries id ".$id, 'id_user' => \Auth::user()->id_user]);
      return response()->json(['message'=>'success']);

    }catch(\Exception $e){
      GeneralHelper::add_log(['type' => 'error', 'description' => $e->getMessage(), 'id_user' => \Auth::user()->id_user]);
      return response()->json(['message'=>'error'], 500);
      
    }
  }

  public function update(Request $req) {
    try {
      $id = $req->country;
      $validator = $this->check($req, $id);
      if ($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
     
      $input = $this->prepare_data($req);
      $input['date_updated'] = Date('Y-m-d H:i:s');
      $input['updated_by'] = \Auth::user()->user_name;
      $image_name = time().'.'.$req->file->getClientOriginalExtension();
      $input['country_image_path'] =   'assets/images/flag/'.$image_name;
      $req->file->move(public_path('assets/images/flag'), $image_name);

      DB::table($this->table)->where('id_country', $id)->update($input);
      Cache::forget('countries');
      
      return \Redirect::route('countries.edit', [$id])->with('message_success', 'Data has been saved successfully!');
    }catch(\Exception $e) {
      GeneralHelper::add_log([
        'type' => 'error',
        'description' => $e->getMessage(), 
        'id_user' => \Auth::user()->id_user]);

      return \Redirect::route('countries.edit', [$req->country])
        ->with('message_error', $e->getMessage());
    }
  }

  function check($request, $id = null) {
    $additional_code_rule = $id == null ? '|unique:ms_country' : '';
    return Validator::make($request->all(), [
      'country_code' => 'required|max:25'.$additional_code_rule,
      'name' => 'required',
      'file' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
    ]);
  }

  function prepare_data($req) {
    $input['country_code'] = $req->country_code;
    $input['country_name'] = $req->name;
    $input['description'] = $req->description;
    $input['is_active'] = $req->is_active;
        
    return $input;
  }

  # datatables
  public function list(Request $req){
    $list = Country::select(DB::raw("ms_country.*"))
    ->whereRaw("id_country IS NOT NULL");
    return Datatables::of($list)
    ->editColumn('country_image_path', function($list){ 
      return "<img src='".\URL::to('/').'/'.$list->country_image_path."' width='50px'>";
    })
    ->editColumn('is_active', function($list){ return $list->is_active == 1 ? 'Active' : 'Inactive' ; })
    ->addColumn('action', function($list){
      $actions = "<a href='".route('countries.edit', [$list->id_country])."' class='btn btn-sm btn-clean btn-icon btn-icon-md'><i class='fa fa-edit'></i></a>";
      $actions .= "<a href='#' class='btn btn-sm btn-clean btn-icon btn-icon-md' onclick='deleteRow(".$list->id_country.")'><i class='fa fa-trash'></i></a>&nbsp;";
      return $actions;
    })
    ->rawColumns(['country_image_path', 'action'])
    ->make(true);
  }
}
