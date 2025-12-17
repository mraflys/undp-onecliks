<?php
namespace App\Http\Controllers;

use App\GeneralHelper;
use App\WorkFlow;
use App\WorkFlowInfo;
use Cache;
use DB;
use Illuminate\Http\Request;
use Validator;

date_default_timezone_set('Asia/Jakarta');

class WorkFlowInfoController extends Controller
{
    protected $table = 'ms_service_workflow_info';

    public function index()
    {
        $data['title']       = 'WorkFlowInfo';
        $data['workflow']    = Workflow::find(\Request()->id_service_workflow);
        $data['list']        = WorkFlowInfo::where('id_service_workflow', \Request()->id_service_workflow)->orderBy('sequence')->get();
        $data['breadcrumps'] = ['Master', 'WorkFlowInfo'];
        return view('admin.workflow_info.list', $data);
    }

    public function create()
    {
        $data['title']       = 'WorkFlowInfo';
        $data['breadcrumps'] = ['Master', 'New WorkFlowInfo'];
        return view('admin.workflow_info.form', $data);
    }

    public function store(Request $req)
    {
        try {
            $validator = $this->check($req);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $input                 = $this->prepare_data($req);
            $input['date_created'] = Date('Y-m-d H:i:s');
            $input['created_by']   = \Auth::user()->user_name;
            $id                    = DB::table($this->table)->insertGetId($input);
            Cache::forget('WorkFlowInfo');
            return \Redirect::route('workflow_infos.edit', [$id])->with('message_success', 'Data has been saved successfully!');
        } catch (\Exception $e) {
            GeneralHelper::add_log([
                'type'        => 'error',
                'description' => $e->getMessage(),
                'id_user'     => \Auth::user()->id_user]);

            return \Redirect::route('workflow_infos.create')
                ->with('message_error', $e->getMessage());
        }
    }

    public function edit($id)
    {
        $data['title']       = 'WorkFlowInfo';
        $data['breadcrumps'] = ['Master', 'Edit WorkFlowInfo'];
        $data['detail']      = WorkFlowInfo::find($id);
        return view('admin.workflow_info.form', $data);
    }

    public function show($id)
    {
        $data['title']       = 'WorkFlowInfo';
        $data['breadcrumps'] = ['Master', 'WorkFlowInfo'];
        $data['detail']      = WorkFlowInfo::find($id);
        return view('admin.workflow_info.form', $data);
    }

    public function destroy($id)
    {
        try {
            $row               = WorkFlowInfo::find($id);
            $row->date_deleted = Date('Y-m-d H:i:s');
            $row->deleted_by   = \Auth::user()->user_name;
            $row->save();
            Cache::forget('WorkFlowInfo');

            GeneralHelper::add_log(['description' => "DELETE WorkFlowInfo id " . $id, 'id_user' => \Auth::user()->id_user]);
            return response()->json(['message' => 'success']);

        } catch (\Exception $e) {
            GeneralHelper::add_log(['type' => 'error', 'description' => $e->getMessage(), 'id_user' => \Auth::user()->id_user]);
            return response()->json(['message' => 'error'], 500);

        }
    }

    public function update(Request $req)
    {
        try {
            $id        = $req->workflow_info;
            $validator = $this->check($req, $id);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            $input                 = $this->prepare_data($req);
            $input['date_updated'] = Date('Y-m-d H:i:s');
            $input['updated_by']   = \Auth::user()->user_name;
            DB::table($this->table)->where('id_service_workflow_info', $id)->update($input);
            Cache::forget('WorkFlowInfo');
            return \Redirect::route('workflow_infos.edit', [$id])->with('message_success', 'Data has been saved successfully!');
        } catch (\Exception $e) {
            GeneralHelper::add_log([
                'type'        => 'error',
                'description' => $e->getMessage(),
                'id_user'     => \Auth::user()->id_user]);

            return \Redirect::route('workflow_infos.create')
                ->with('message_error', $e->getMessage());
        }
    }

    public function update_sequence(Request $req)
    {
        $id               = $req->id;
        $arrow            = $req->arrow;
        $id_parent        = $req->id_service_workflow;
        $current_info     = WorkFlowInfo::find($id);
        $current_sequence = $current_info->sequence;
        $sign             = '=';
        $order_by         = 'ASC';

        if ($arrow == 'up') {
            $sign     = '<';
            $order_by = 'DESC';
        }
        if ($arrow == 'down') {
            $sign = '>';
        }

        $switch = WorkFlowInfo::whereRaw("id_service_workflow = '" . $id_parent . "' AND sequence $sign " . $current_sequence)->orderBy('sequence', $order_by)->first();

        if (! is_null($switch)) {
            $current_info->sequence = $switch->sequence;
            $current_info->save();

            $switch->sequence = $current_sequence;
            $switch->save();
        }

        return redirect()->back();
    }

    public function check($request, $id = null)
    {
        return Validator::make($request->all(), [
            'info_title'   => 'required',
            'description'  => 'required',
            'is_mandatory' => 'required',
        ]);
    }

    public function prepare_data($req)
    {
        $input['id_service_workflow'] = $req->id_service_workflow;
        $input['info_title']          = $req->info_title;
        $input['description']         = $req->description;
        $input['is_mandatory']        = $req->is_mandatory;
        $input['sequence']            = $req->sequence;

        return $input;
    }
}
