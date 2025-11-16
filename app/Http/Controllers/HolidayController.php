<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB, Session, Cache, Validator;
use App\Holiday;
use App\Country;
use App\GeneralHelper;
use Datatables;
date_default_timezone_set('Asia/Jakarta');

class HolidayController extends Controller
{
  protected $table = 'ms_calendar_holiday';

  public function index(){
    $data['title'] = 'Holiday Calendar';
    $data['breadcrumps'] = ['Master', 'Holiday Calendar'];
    return view('admin.holiday.list', $data);
  }

  public function create() {
    $data['title'] = 'Holiday Calendar';
    $data['breadcrumps'] = ['Master', 'New Holiday Calendar'];
    $data['countries'] = Country::list_with_cache();
    return view('admin.holiday.form', $data);
  }

  public function store(Request $req) {
    try {
      $validator = $this->check($req);
      if ($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
      
      $input = $this->prepare_data($req);
      $input['date_created'] = Date('Y-m-d H:i:s');
      $input['created_by'] = \Auth::user()->user_name;
      $id = DB::table($this->table)->insertGetId($input);
      Cache::forget('holidays');
      return \Redirect::route('holidays.edit', [$id])->with('message_success', 'Data has been saved successfully!');
    }catch(\Exception $e) {
      GeneralHelper::add_log([
        'type' => 'error',
        'description' => $e->getMessage(), 
        'id_user' => \Auth::user()->id_user]);

      return \Redirect::route('holidays.create')
        ->with('message_error', $e->getMessage());
    }
  }

  public function edit($id) {
    $data['title'] = 'Holiday Calendar';
    $data['breadcrumps'] = ['Master', 'Edit Holiday Calendar'];
    $data['countries'] = Country::list_with_cache();
    $data['detail'] = Holiday::find($id);
    return view('admin.holiday.form', $data);
  }

  public function show($id) {
    $data['title'] = 'Holiday Calendar';
    $data['breadcrumps'] = ['Master', 'Holiday Calendar'];
    $data['countries'] = Country::list_with_cache();
    $data['detail'] = Holiday::find($id);
    return view('admin.holiday.form', $data);
  }

  public function destroy($id) {
    try{
      $row = Holiday::find($id);
      $row->date_deleted = Date('Y-m-d H:i:s');
      $row->deleted_by = \Auth::user()->user_name;
      $row->save();
      Cache::forget('holidays');

      GeneralHelper::add_log(['description' => "DELETE holidays id ".$id, 'id_user' => \Auth::user()->id_user]);
      return response()->json(['message'=>'success']);

    }catch(\Exception $e){
      GeneralHelper::add_log(['type' => 'error', 'description' => $e->getMessage(), 'id_user' => \Auth::user()->id_user]);
      return response()->json(['message'=>'error'], 500);
      
    }
  }

  public function update(Request $req) {
    try {
      $id = $req->holiday;
      $validator = $this->check($req, $id);
      if ($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
     
      $input = $this->prepare_data($req);
      $input['date_updated'] = Date('Y-m-d H:i:s');
      $input['updated_by'] = \Auth::user()->user_name;
      DB::table($this->table)->where('id_calendar_holiday', $id)->update($input);
      Cache::forget('holidays');
      return \Redirect::route('holidays.edit', [$id])->with('message_success', 'Data has been saved successfully!');
    }catch(\Exception $e) {
      GeneralHelper::add_log([
        'type' => 'error',
        'description' => $e->getMessage(), 
        'id_user' => \Auth::user()->id_user]);

      return \Redirect::route('holidays.create')
        ->with('message_error', $e->getMessage());
    }
  }

  function check($request, $id = null) {
    $additional_code_rule = $id == null ? '|unique:ms_calendar_holiday' : '';
    return Validator::make($request->all(), [
      'name' => 'required',
      // 'date_holiday_start' => 'required|date_format:Y-m-d',
      // 'date_holiday_end' => 'required|date_format:Y-m-d ',
    ]);
  }

  function prepare_data($req) {
    $input['holiday_name'] = $req->name;
    $input['description'] = $req->description;
    $input['date_holiday_start'] = $req->date_holiday_start;
    $input['date_holiday_end'] = $req->date_holiday_end;
    $input['id_country'] = $req->country;
    
    return $input;
  }

  # datatables
  public function list(Request $req){
    $list = Holiday::select(DB::raw("ms_calendar_holiday.*, ms_country.country_name"))
    ->join("ms_country", "ms_country.id_country", '=', 'ms_calendar_holiday.id_country')
    ->whereRaw("id_calendar_holiday IS NOT NULL");
    return Datatables::of($list)
    ->addColumn('action', function($list){
      $actions = "<a href='".route('holidays.edit', [$list->id_calendar_holiday])."' class='btn btn-sm btn-clean btn-icon btn-icon-md'><i class='fa fa-edit'></i></a>";
      $actions .= "<a href='#' class='btn btn-sm btn-clean btn-icon btn-icon-md' onclick='deleteRow(".$list->id_calendar_holiday.")'><i class='fa fa-trash'></i></a>&nbsp;";
      return $actions;
    })
    ->make(true);
  }
}
