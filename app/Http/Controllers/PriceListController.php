<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB, Session, Cache, Validator;
use App\ServiceList;
use App\PriceList;
use App\Currency;
use App\GeneralHelper;
use Datatables;
date_default_timezone_set('Asia/Jakarta');

class PriceListController extends Controller
{
  protected $table = 'ms_service_pricelist';

  public function index(){
    $data['service'] = ServiceList::find(\Request()->id_service);
    $data['title'] = 'Pricelist';
    $data['breadcrumps'] = ['Master', 'Pricelist'];
    return view('admin.pricelist.list', $data);
  }

  public function create() {
    $data['service'] = ServiceList::find(\Request()->id_service);
    $data['title'] = 'Pricelist';
    $data['breadcrumps'] = ['Master', 'New Pricelist'];
    $data['currencies'] = Currency::list_with_cache();
    return view('admin.pricelist.form', $data);
  }

  public function store(Request $req) {
    try {
      $validator = $this->check($req);
      if ($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
      
      $input = $this->prepare_data($req);
      $input['date_created'] = Date('Y-m-d H:i:s');
      $input['created_by'] = \Auth::user()->user_name;
      $id = DB::table($this->table)->insertGetId($input);
      Cache::forget('pricelist');
      return \Redirect::route('pricelist.edit', [$id])->with('message_success', 'Data has been saved successfully!');
    }catch(\Exception $e) {
      GeneralHelper::add_log([
        'type' => 'error',
        'description' => $e->getMessage(), 
        'id_user' => \Auth::user()->id_user]);

      return \Redirect::route('pricelist.create')
        ->with('message_error', $e->getMessage());
    }
  }

  public function edit($id) {
    $data['service'] = ServiceList::find(\Request()->id_service);
    $data['title'] = 'Pricelist';
    $data['breadcrumps'] = ['Master', 'Edit Pricelist'];
    $data['detail'] = Pricelist::find($id);
    $data['currencies'] = Currency::list_with_cache();
    return view('admin.pricelist.form', $data);
  }

  public function show($id) {
    $data['title'] = 'Pricelist';
    $data['breadcrumps'] = ['Master', 'Pricelist'];
    $data['detail'] = Pricelist::find($id);
    return view('admin.pricelist.form', $data);
  }

  public function destroy($id) {
    try{
      $row = Pricelist::find($id);
      $row->date_deleted = Date('Y-m-d H:i:s');
      $row->deleted_by = \Auth::user()->user_name;
      $row->save();
      Cache::forget('pricelist');

      GeneralHelper::add_log(['description' => "DELETE pricelist id ".$id, 'id_user' => \Auth::user()->id_user]);
      return response()->json(['message'=>'success']);

    }catch(\Exception $e){
      GeneralHelper::add_log(['type' => 'error', 'description' => $e->getMessage(), 'id_user' => \Auth::user()->id_user]);
      return response()->json(['message'=>'error'], 500);
      
    }
  }

  public function update(Request $req) {
    try {
      $id = $req->pricelist;
      $validator = $this->check($req, $id);
      if ($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
     
      $input = $this->prepare_data($req);
      $input['date_updated'] = Date('Y-m-d H:i:s');
      $input['updated_by'] = \Auth::user()->user_name;
      DB::table($this->table)->where('id_service_pricelist', $id)->update($input);
      Cache::forget('pricelist');
      return \Redirect::route('pricelist.edit', [$id])->with('message_success', 'Data has been saved successfully!');
    }catch(\Exception $e) {
      GeneralHelper::add_log([
        'type' => 'error',
        'description' => $e->getMessage(), 
        'id_user' => \Auth::user()->id_user]);

      return \Redirect::route('pricelist.create')
        ->with('message_error', $e->getMessage());
    }
  }

  function check($request, $id = null) {
    return Validator::make($request->all(), [
      // 'opu' => 'required',
      'id_service' => 'required',
      'id_currency' => 'required',
      'price' => 'required',
      
    ]);
  }

  function prepare_data($req) {
    $input['id_service'] = $req->id_service;
    $input['id_currency'] = $req->id_currency;
    $input['date_start_price'] = $req->date_start_price;
    $input['date_end_price'] = $req->date_end_price;
    $input['price'] = $req->price;
    
    return $input;
  }

  # datatables
  public function list(Request $req){
    $id_service = $req->id_service;

    $list = Pricelist::select(DB::raw("ms_service_pricelist.*, currency_name"))
    ->join('ms_currency', 'ms_currency.id_currency', '=', 'ms_service_pricelist.id_currency')
    ->whereRaw("id_service_pricelist IS NOT NULL AND id_service = ".$id_service);
    return Datatables::of($list)
    ->addColumn('action', function($list){
      $actions = "<a href='".route('pricelist.edit', [$list->id_service_pricelist])."' class='btn btn-sm btn-clean btn-icon btn-icon-md'><i class='fa fa-edit'></i></a>";
      $actions .= "<a href='#' class='btn btn-sm btn-clean btn-icon btn-icon-md' onclick='deleteRow(".$list->id_service_pricelist.")'><i class='fa fa-trash'></i></a>&nbsp;";
      return $actions;
    })
    ->make(true);
  }
}
