<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB, Session, Cache, Validator;
use App\Project;
use App\AgencyUnit;
use App\GeneralHelper;
use App\TrPEX;
use App\TrPexSetting;
use App\SecUser;
use Datatables;

date_default_timezone_set('Asia/Jakarta');

class ProjectController extends Controller
{
  protected $table = 'ms_project';

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
    $data['title'] = 'Payroll Expenditure';
    $data['breadcrumps'] = ['Master', 'Payroll Expenditure'];
    $data['list'] = TrPEX::whereNotNull('Name')->orderBy('TIDNO')->get(); 
    $data['users'] = SecUser::get();
    $data['distinctTrPEX'] = TrPEX::whereNotNull('Name')->select('Project')->distinct()->get();
    return view('admin.project.list', $data);
  }

  public function create() {
    $data['title'] = 'Project';
    $data['breadcrumps'] = ['Master', 'New Project'];
    $data['agencies'] = AgencyUnit::whereRaw('id_agency_unit_parent IS NULL')->orderBy('agency_unit_name')->get();
    return view('admin.project.form', $data);
  }

  // public function store(Request $req) {
  //   try {
  //     $validator = $this->check($req);
  //     if ($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
      
  //     $input = $this->prepare_data($req);
  //     $input['date_created'] = Date('Y-m-d H:i:s');
  //     $input['created_by'] = \Auth::user()->user_name;
  //     $id = DB::table($this->table)->insertGetId($input);
  //     Cache::forget('projects');
  //     GeneralHelper::add_log(['description' => "Add Project ".$id, 'id_user' => \Auth::user()->id_user]);

  //     return \Redirect::route('projects.edit', [$id])->with('message_success', 'Data has been saved successfully!');
  //   }catch(\Exception $e) {
  //     GeneralHelper::add_log([
  //       'type' => 'error',
  //       'description' => $e->getMessage(), 
  //       'id_user' => \Auth::user()->id_user]);

  //     return \Redirect::route('projects.create')
  //       ->with('message_error', $e->getMessage());
  //   }
  // }

  public function store(Request $req) {
    try {
      if ($req->hasFile('import')) {
        $TrPEX = TrPEX::whereDate('Date','>=',$req->date1)->whereDate('Date','<=',$req->date2)->get();
        $TrPEXId = $TrPEX->pluck('TIDNO')->toArray();
        $TrPEXDel = TrPEX::whereIn('TIDNO', $TrPEXId)->delete();
        \Excel::import(new \App\Imports\PexExcel($req->date1,$req->date2), $req->file('import'));
        GeneralHelper::add_log(['description' => "Project has been saved successfully!", 'id_user' => \Auth::user()->id_user]);
        return \Redirect::route('projects.index')->with('message_success', 'Project has been saved successfully!');
      }
    }catch(\Exception $e) {
      GeneralHelper::add_log([
        'type' => 'error',
        'description' => $e->getMessage(), 
        'id_user' => \Auth::user()->id_user]);

      return \Redirect::route('projects.index')
        ->with('message_error', $e->getMessage());
    }
  }

  public function person_list(Request $req) {
    $TrPEX = TrPEX::where('TIDNO',$req->id)->first();
    $list_TrPEX = TrPEX::where('CalendarGroup',$TrPEX->CalendarGroup)->where('Project',$TrPEX->Project)->where('ErnDedCd',$TrPEX->ErnDedCd)->where('ProjAct',$TrPEX->ProjAct)->get();
    $person_list = ['name' => [], 'position' => []];
    
    foreach($list_TrPEX as $list){
      if($list->Name){
        array_push($person_list['name'], $list->Name);
        array_push($person_list['position'], $list->PositionDescr);
      }
    }
    return $person_list;
  }

  public function ajax_list(Request $req) {
    $viewBy = $req->viewBy;
    if($req->roleUser == 3){
      if(!is_null($viewBy)){
        $TrPEX = TrPEX::whereNotNull('Name')->where('Project',$viewBy)->orderBy('TIDNO')->get(); 
      }else{
        $TrPEX = TrPEX::whereNotNull('Name')->orderBy('TIDNO')->get(); 
      }
      return Datatables::of($TrPEX)
      ->addColumn("action", function($list){ 
        return '<a href="javascript:void(0);" class="btn btn-secondary" data-toggle="modal"
        data-target="#listModal" data-backdrop="static" data-keyboard="false" onclick="person_list(this.id)" id="'.$list->TIDNO.'"><i class="fa fa-eye"></i> List</a>'; 
      })
      ->make(true);
    }else{
      $TrPexSetting = TrPexSetting::where('id_user',\Auth::user()->id_user)->get();
      $TrPexSettingId = $TrPexSetting->pluck('TIDNO')->toArray();
      if(!is_null($viewBy)){
        $TrPEX = TrPEX::whereIn('TIDNO',$TrPexSettingId)->where('Project',$viewBy)->orderBy('TIDNO')->get();
      }else{
        $TrPEX = TrPEX::whereIn('TIDNO',$TrPexSettingId)->orderBy('TIDNO')->get(); 
      }
      return Datatables::of($TrPEX)
      ->addColumn("action", function($list){ 
        return '<a href="javascript:void(0);" class="btn btn-secondary" data-toggle="modal"
        data-target="#listModal" data-backdrop="static" data-keyboard="false" onclick="person_list(this.id)" id="'.$list->TIDNO.'"><i class="fa fa-eye"></i> List</a>';
      })
      ->make(true);
    }
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
      $row = Project::find($id);
      $row->date_deleted = Date('Y-m-d H:i:s');
      $row->deleted_by = \Auth::user()->user_name;
      $row->save();
      Cache::forget('projects');
      GeneralHelper::add_log(['description' => "DELETE projects id ".$id, 'id_user' => \Auth::user()->id_user]);
      return response()->json(['message'=>'success']);

    }catch(\Exception $e){
      GeneralHelper::add_log(['type' => 'error', 'description' => $e->getMessage(), 'id_user' => \Auth::user()->id_user]);
      return response()->json(['message'=>'error'], 500);
      
    }
  }

  public function update(Request $req) {
    try {
      $id = $req->project;
      $validator = $this->check($req, $id);
      if ($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();
     
      $input = $this->prepare_data($req);
      $input['date_updated'] = Date('Y-m-d H:i:s');
      $input['updated_by'] = \Auth::user()->user_name;
      DB::table($this->table)->where('id_project', $id)->update($input);
      Cache::forget('projects');
      GeneralHelper::add_log(['description' => "Update Project ".$id, 'id_user' => \Auth::user()->id_user]);

      return \Redirect::route('projects.edit', [$id])->with('message_success', 'Data has been saved successfully!');
    }catch(\Exception $e) {
      GeneralHelper::add_log([
        'type' => 'error',
        'description' => $e->getMessage(), 
        'id_user' => \Auth::user()->id_user]);

      return \Redirect::route('projects.create')
        ->with('message_error', $e->getMessage());
    }
  }

  function check($request, $id = null) {
    return Validator::make($request->all(), [
      'name' => 'required|max:100',
      'id_agency_unit' => 'required',
    ]);
  }

  function prepare_data($req) {
    $input['project_name'] = $req->name;
    $input['id_agency_unit'] = $req->id_agency_unit;
    $input['id_agency_unit_parent'] = $req->id_agency_unit_parent;
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
