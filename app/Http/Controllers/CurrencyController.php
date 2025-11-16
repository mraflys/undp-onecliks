<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB, Session, Cache, Validator;
use App\Currency;
use App\GeneralHelper;
use Datatables;
date_default_timezone_set('Asia/Jakarta');

class CurrencyController extends Controller
{
  protected $table = 'ms_currency';

  public function index(){
    $data['title'] = 'currency';
    $data['breadcrumps'] = ['Master', 'Currency'];
    return view('admin.currency.list', $data);
  }

  public function create() {
    $data['title'] = 'currency';
    $data['breadcrumps'] = ['Master', 'New Currency'];
    return view('admin.currency.form', $data);
  }

  public function store(Request $req) {
    try {
      $validator = $this->check($req);
      if ($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
      
      $input = $this->prepare_data($req);
      $input['date_created'] = Date('Y-m-d H:i:s');
      $input['created_by'] = \Auth::user()->user_name;
      
      $id = DB::table($this->table)->insertGetId($input);
      return \Redirect::route('currencies.edit', [$id])->with('message_success', 'Data has been saved successfully!');

    }catch(\Exception $e) {
      GeneralHelper::add_log([
        'type' => 'error',
        'description' => $e->getMessage(), 
        'id_user' => \Auth::user()->id_user]);

      return \Redirect::route('currencies.create')->with('message_error', $e->getMessage());

    }
  }

  public function edit($id) {
    $data['title'] = 'currency';
    $data['breadcrumps'] = ['Master', 'Edit Currency'];
    $data['detail'] = Currency::find($id);
    return view('admin.currency.form', $data);
  }

  public function show($id) {
    $data['title'] = 'currency';
    $data['breadcrumps'] = ['Master', 'Edit Currency'];
    $data['currencies'] = Currency::list_with_cache();
    $data['detail'] = Currency::find($id);
    return view('admin.currency.form', $data);
  }

  public function destroy($id) {
    try{
      $row = Currency::find($id);
      $row->date_deleted = Date('Y-m-d H:i:s');
      $row->deleted_by = \Auth::user()->user_name;
      $row->save();

      GeneralHelper::add_log(['description' => "DELETE currencies id ".$id, 'id_user' => \Auth::user()->id_user]);
      return response()->json(['message'=>'success']);

    }catch(\Exception $e){
      GeneralHelper::add_log(['type' => 'error', 'description' => $e->getMessage(), 'id_user' => \Auth::user()->id_user]);
      return response()->json(['message'=>'error'], 500);
      
    }
  }

  public function update(Request $req) {
    try {
      $id = $req->currency;
      $validator = $this->check($req, $id);
      if ($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
     
      $input = $this->prepare_data($req);
      $input['date_updated'] = Date('Y-m-d H:i:s');
      $input['updated_by'] = \Auth::user()->user_name;
     
      DB::table($this->table)->where('id_currency', $id)->update($input);
      return \Redirect::route('currencies.edit', [$id])->with('message_success', 'Data has been saved successfully!');
    }catch(\Exception $e) {
      GeneralHelper::add_log([
        'type' => 'error',
        'description' => $e->getMessage(), 
        'id_user' => \Auth::user()->id_user]);

      return \Redirect::route('currencies.edit', [$req->currency])
        ->with('message_error', $e->getMessage());
    }
  }

  function check($request, $id = null) {
    $additional_code_rule = $id == null ? '|unique:ms_currency' : '';
    return Validator::make($request->all(), [
      'currency_code' => 'required|max:25'.$additional_code_rule,
      'name' => 'required',
    ]);
  }

  function prepare_data($req) {
    $input['currency_code'] = $req->currency_code;
    $input['currency_name'] = $req->name;
    $input['description'] = $req->description;
    $input['is_active'] = $req->is_active;
        
    return $input;
  }

  # datatables
  public function list(Request $req){
    $list = Currency::select(DB::raw("ms_currency.*"))
    ->whereRaw("id_currency IS NOT NULL");
    return Datatables::of($list)
    ->editColumn('is_active', function($list){ return $list->is_active == 1 ? 'Active' : 'Inactive' ; })
    ->addColumn('action', function($list){
      $actions = "<a href='".route('currencies.edit', [$list->id_currency])."' class='btn btn-sm btn-clean btn-icon btn-icon-md'><i class='fa fa-edit'></i></a>";
      $actions .= "<a href='#' class='btn btn-sm btn-clean btn-icon btn-icon-md' onclick='deleteRow(".$list->id_currency.")'><i class='fa fa-trash'></i></a>&nbsp;";
      return $actions;
    })
    ->rawColumns(['currency_image_path', 'action'])
    ->make(true);
  }
}
