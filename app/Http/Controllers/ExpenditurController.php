<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB, Session, Cache, Validator;
use App\Expenditur;
use App\AgencyUnit;
use App\GeneralHelper;
use App\TrPEX;
use App\TrPexSetting;
use App\SecUser;
use Datatables;

date_default_timezone_set('Asia/Jakarta');

class ExpenditurController extends Controller
{
  protected $table = 'ms_exp_type';

  public function __construct(){
    $this->middleware(function ($request, $next){
      if (session('user_id') != null) {
        return $next($request);
      }else{
        return redirect()->route('login');
      }
    });
  }

  public function index(){
    $data['title'] = 'Expenditure Type';
    $data['breadcrumps'] = ['Master', 'Expenditure Type'];
    $data['list'] = Expenditur::orderBy('exp_type_code')->get(); 
    $data['users'] = SecUser::get();
    return view('admin.expenditur.list', $data);
  }

  public function create() {
    $data['title'] = 'Project';
    $data['breadcrumps'] = ['Master', 'New Project'];
    $data['agencies'] = AgencyUnit::whereRaw('id_agency_unit_parent IS NULL')->orderBy('agency_unit_name')->get();
    return view('admin.project.form', $data);
  }

  public function store(Request $req) {
    try {
      
      Expenditur::create([
        'exp_type_code' => $req->code,
        'exp_type_name' => $req->name,
        'description' => $req->description,
      ]);
      return \Redirect::route('expenditur.index')->with('message_success', 'Expenditure Type has been saved successfully!');
    }catch(\Exception $e) {
      GeneralHelper::add_log([
        'type' => 'error',
        'description' => $e->getMessage(), 
        'id_user' => \Auth::user()->id_user]);

      return \Redirect::route('expenditur.index')
        ->with('message_error', $e->getMessage());
    }
  }

  public function expenditur_detail(Request $req) {
    $Expenditur = Expenditur::where('id_exptype',$req->id)->first();
    
    return $Expenditur;
  }

  public function ajax_list(Request $req) {
    $viewBy = $req->viewBy;
    if(!is_null($viewBy)){
      $Expenditur = Expenditur::orderBy('exp_type_code')->get(); 
    }else{
      $Expenditur = Expenditur::orderBy('exp_type_code')->get(); 
    }
    return Datatables::of($Expenditur)
    ->addColumn("action", function($list){ 
      $actions = '<a href="javascript:void(0);" class="btn btn-secondary btn-clean btn-icon btn-icon-md" data-toggle="modal"
      data-target="#listModal" data-backdrop="static" data-keyboard="false" onclick="expenditur_detail(this.id)" id="'.$list->id_exptype.'"><i class="fa fa-pen"></i></a>';
      $actions .= "<a href='#' class='btn btn-sm btn-clean btn-icon btn-icon-md' onclick='deleteRow(".$list->id_exptype.")'><i class='fa fa-trash'></i></a>&nbsp;";
      return $actions; 
    })
    ->make(true);
  }

  public function edit($id) {
    $data['title'] = 'Project';
    $data['breadcrumps'] = ['Master', 'New Project'];
    $data['agencies'] = AgencyUnit::whereRaw('id_agency_unit_parent IS NULL')->orderBy('agency_unit_name')->get();
    $data['detail'] = Project::find($id);
    return view('admin.project.form', $data);
  }

  public function show($id) {
    $data['title'] = 'Project';
    $data['breadcrumps'] = ['Master', 'New Project'];
    $data['detail'] = Project::find($id);
    return view('admin.project.form', $data);
  }

  public function destroy($id) {
    try{
      $row = Expenditur::find($id);
      $row->date_deleted = Date('Y-m-d H:i:s');
      $row->deleted_by = \Auth::user()->user_name;
      $row->save();
      Cache::forget('expenditur');
      GeneralHelper::add_log(['description' => "DELETE ms_exp_type id ".$id, 'id_user' => \Auth::user()->id_user]);
      return \Redirect::route('expenditur.index')->with('message_success', 'Data has been deleted successfully!');

    }catch(\Exception $e){
      GeneralHelper::add_log(['type' => 'error', 'description' => $e->getMessage(), 'id_user' => \Auth::user()->id_user]);
      return response()->json(['message'=>'error'], 500);
      
    }
  }

  public function update_new(Request $req) {
    try {
      $id = $req->id_exptype;
      $validator = $this->check($req, $id);
      if ($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
     
      $input = $this->prepare_data($req);
      $input['date_update'] = Date('Y-m-d H:i:s');
      $input['updated_by'] = \Auth::user()->user_name;
      DB::table($this->table)->where('id_exptype', $id)->update($input);
      Cache::forget('expenditur');
      GeneralHelper::add_log(['description' => "Update Expenditur type ".$id, 'id_user' => \Auth::user()->id_user]);

      return \Redirect::route('expenditur.index')->with('message_success', 'Data has been saved successfully!');
    }catch(\Exception $e) {
      GeneralHelper::add_log([
        'type' => 'error',
        'description' => $e->getMessage(), 
        'id_user' => \Auth::user()->id_user]);

      return \Redirect::route('expenditur.index')
        ->with('message_error', $e->getMessage());
    }
  }

  function check($request, $id = null) {
    return Validator::make($request->all(), [
      'code_edit' => 'required',
      'name_edit' => 'required',
      'id_exptype' => 'required',
    ]);
  }

  function prepare_data($req) {
    $input['exp_type_code'] = $req->code_edit;
    $input['exp_type_name'] = $req->name_edit;
    $input['description'] = $req->description_edit;
    return $input;
  }
  function get_inputname() {
    $pexParent = TrPEX::get();
    // dd($pexParent);
    $namerow=null;
    $returnValue=null;
    foreach ($pexParent as $pex) {
      // $pexChild = TrPEX::where('name','like', '%'.$pex->Name.'%')->get();
      $returnValue.='<tr>';

      $returnValue.='<td>'.$pex->Name.'</td>';
      $returnValue.='<td><input type="checkbox" class="checkboxnameproject_id"
      name="usernameproject_id[]" id="' . $pex->TIDNO . '"
      value="' . $pex->TIDNO . '"></td>';
      $returnValue.='<td>'.$pex->Project.'</td>';
      $returnValue.='<td>'.$pex->PositionDescr.'</td>';
      $returnValue.='<td>'.$pex->CalendarGroup.'</td>';
      $returnValue.='<td>'.$pex->OperatingUnit.'</td>';
      $returnValue.='</tr>';
      // if(count($pexChild) != 0){
      //   $a = 0;
      //   foreach($pexChild as $child){
          
      //     $a++;
      //   }
        
      // }
      
      $namerow = $pex->Name;
    }
    return \Response::json(['content' => $returnValue], 200);
  }
  function setting_store(Request $req) {
    try {
      $user = SecUser::where('id_user',$req->id_user)->first();
      $checkbox_table = explode(",", $req->checkbox_table);
      foreach($checkbox_table as $project){
        $TrPEX = TrPEX::where('TIDNO',$project)->first(); 
        if($TrPEX){
          $TrPexSetting = TrPexSetting::where('id_user',$user->id_user)->where('TIDNO',$project)->first();
          if(!$TrPexSetting){
            TrPexSetting::create([
              'id_user' => $user->id_user,
              'TIDNO' => $project,
              'is_active' => true
            ]);
          }
        }
      }
      GeneralHelper::add_log(['description' => $user->person_name." has been given the authority to view the projects that have been listed", 'id_user' => \Auth::user()->id_user]);
      return \Redirect::route('projects.index')->with('message_success', $user->person_name." has been given the authority to view the projects that have been listed");
    }catch(\Exception $e) {
      GeneralHelper::add_log([
        'type' => 'error',
        'description' => $e->getMessage(), 
        'id_user' => \Auth::user()->id_user]);

      return \Redirect::route('projects.index')
        ->with('message_error', $e->getMessage());
    }
  }

  function setting_store_input(Request $req) {
    try {
      $user = SecUser::where('id_user',$req->id_user)->first();
      $checkbox_table = explode(",", $req->checkbox_table);
      
      foreach($checkbox_table as $project){
        $project = str_replace(" ","",$project);
        $TrPEXs = TrPEX::where('Project', 'LIKE','%'.$project.'%')->get(); 
        // dd($TrPEXs);
        if(count($TrPEXs) != 0){
          foreach($TrPEXs as $TrPEX){
            $TrPexSetting = TrPexSetting::where('id_user',$user->id_user)->where('TIDNO',$TrPEX->TIDNO)->first();
            if(!$TrPexSetting){
              TrPexSetting::create([
                'id_user' => $user->id_user,
                'TIDNO' => $TrPEX->TIDNO,
                'is_active' => true
              ]);
            }
          }
        }
      }
      GeneralHelper::add_log(['description' => $user->person_name." has been given the authority to view the projects that have been listed", 'id_user' => \Auth::user()->id_user]);
      return \Redirect::route('projects.index')->with('message_success', $user->person_name." has been given the authority to view the projects that have been listed");
    }catch(\Exception $e) {
      GeneralHelper::add_log([
        'type' => 'error',
        'description' => $e->getMessage(), 
        'id_user' => \Auth::user()->id_user]);

      return \Redirect::route('projects.index')
        ->with('message_error', $e->getMessage());
    }
  }

  function excel(Request $req) {
    return \Excel::download(new \App\Exports\projectExcel(), 'My Project.xlsx');
  }
}
