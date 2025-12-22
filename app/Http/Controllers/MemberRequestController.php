<?php
namespace App\Http\Controllers;

use App\AgencyUnit;
use App\Coa;
use App\DrService;
use App\DrServiceDoc;
use App\GeneralHelper;
use App\Mail\SendMail;
use App\RequestQuery;
use App\ServiceList;
use App\TrService;
use App\TrServiceWorkFlow;
use App\TrServiceWorkFlowDoc;
use App\WorkFlow;
use App\WorkFlowDoc;
use App\WorkFlowInfo;
use Datatables;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Session;
use Validator;

date_default_timezone_set('Asia/Jakarta');
// define('DATE_TIME', 'Y-m-d H:i:s');
// define('SERVICE_ERROR_MESSAGE', "Sorry, You aren't allowed to see the Request!");

class MemberRequestController extends Controller
{
    protected $table = 'tr_service';
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (session('user_id') != null) {
                return $next($request);
            } else {
                return redirect()->route('login');
            }
        });
    }

    public function check_access($detail)
    {
        $valid = true;
        return $valid;
    }

    /**
     * Clean input data from special characters and symbols
     * @param string $input
     * @return string
     */
    private function clean_input_data($input)
    {
        if (is_null($input) || empty($input)) {
            return $input;
        }

        // Remove special characters except allowed ones
        $clean_data = preg_replace('/[^\w\s\-\.,\(\)\[\]\/\:;]/', '', $input);

        // Trim whitespace
        return trim($clean_data);
    }

    /**
     * Clean multiple input fields
     * @param array $fields
     * @param array $data
     * @return array
     */
    private function clean_input_fields($fields, $data)
    {
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $data[$field] = $this->clean_input_data($data[$field]);
            }
        }

        return $data;
    }

    public function form_init_data()
    {
        $data['agencies']        = AgencyUnit::whereRaw("is_service_unit = 1 AND id_agency_unit_parent IS NULL")->orderBy('agency_unit_name')->get();
        $data['payment_methods'] = GeneralHelper::payment_methods();
        return $data;
    }

    public function create()
    {
        $data                = $this->form_init_data();
        $data['title']       = 'New Request';
        $data['breadcrumps'] = ['Member Area', 'My New Request'];
        $data['source']      = 'tr_service';
        GeneralHelper::add_log(['description' => "Open Create Request", 'id_user' => \Auth::user()->id_user]);

        return view('member.request.form_1', $data);
    }

    public function edit($id_transaction)
    {
        $data   = $this->form_init_data();
        $detail = TrService::find($id_transaction);

        if (! $this->check_access($detail)) {
            Session::flash('message_error', SERVICE_ERROR_MESSAGE);
            return redirect()->back();
        }
        $data['exptype'] = DB::table('ms_exp_type')
            ->selectRaw('id_exptype, exp_type_code, exp_type_name, description')->orderBy('exp_type_code', 'asc')
            ->get();
        $data['detail']      = $detail;
        $data['title']       = 'Edit Request';
        $data['breadcrumps'] = ['Member Area', 'Edit My Request'];
        $data['source']      = 'tr_service';
        GeneralHelper::add_log(['description' => "Open Edit Request " . $detail->transaction_code, 'id_user' => \Auth::user()->id_user]);
        return view('member.request.form_1', $data);
    }

    public function draft_delete($id)
    {
        try {
            DB::beginTransaction();
            $children = DB::table('dr_service')->where('id_transaction_parent', $id)->get();

            foreach ($children as $child) {
                DB::table("dr_service_workflow")->where('id_transaction', $child->id_draft)
                    ->update(['date_deleted' => Date('Y-m-d H:i:s'), 'deleted_by' => Session::get('user_name')]);

                $child               = DrService::find($child->id_draft);
                $child->date_deleted = Date('Y-m-d H:i:s');
                $child->deleted_by   = \Auth::user()->user_name;
                $child->save();
            }

            $row               = DrService::find($id);
            $row->date_deleted = Date('Y-m-d H:i:s');
            $row->deleted_by   = \Auth::user()->user_name;
            $row->save();

            GeneralHelper::add_log(['description' => "DELETE Draft id " . $id, 'id_user' => \Auth::user()->id_user]);
            Session::flash('message_success', 'Data has been deleted');
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            GeneralHelper::add_log(['type' => 'error', 'description' => $e->getMessage(), 'id_user' => \Auth::user()->id_user]);
            Session::flash('message_error', $e->getMessage());
        }

        return redirect()->back();
    }

    public function draft_edit($id_draft)
    {
        $data   = $this->form_init_data();
        $detail = DrService::find($id_draft);
        if (is_null($detail)) {
            Session::flash('message_error', 'Data has been deleted');
            return redirect(route('myrequests.draft'));
        }
        if (! $this->check_access($detail)) {
            Session::flash('message_error', SERVICE_ERROR_MESSAGE);
            return redirect()->back();
        }
        $data['detail']  = $detail;
        $data['exptype'] = DB::table('ms_exp_type')
            ->selectRaw('id_exptype, exp_type_code, exp_type_name, description')->orderBy('exp_type_code', 'asc')
            ->get();

        // dd($data['detail']->required_docs_new($data['detail']->id_draft));
        $data['title']       = 'Request';
        $data['breadcrumps'] = ['Member Area', '(Draft) Request'];
        GeneralHelper::add_log(['description' => "Open Edit Draft " . $detail->id_draft, 'id_user' => \Auth::user()->id_user]);
        return view('member.request.form_1', $data);
    }

    public function index()
    {
        $data['title']       = 'My Request';
        $data['breadcrumps'] = ['Member Area', 'My Request'];
        return view('member.request.list', $data);
    }

    public function home()
    {
        // echo session('user_is_requester').'<br>';
        // echo session('user_menu');
        // exit;
        $data['title']       = 'Home';
        $data['breadcrumps'] = ['Member Area', 'Home Page'];
        return view('member.request.home', $data);
    }

    public function ongoing()
    {
        $data['title']       = 'Ongoing Request';
        $data['breadcrumps'] = ['Member Area', 'Ongoing Request'];
        return view('member.request.ongoing', $data);
    }

    public function history()
    {
        $data['title']       = 'History';
        $data['breadcrumps'] = ['Member Area', 'History'];
        return view('member.request.history', $data);
    }

    public function tracking()
    {
        $data['title']       = 'Tracking';
        $data['breadcrumps'] = ['Member Area', 'Tracking'];
        $data['trackings']   = null;
        $data['route']       = 'myrequests.tracking';
        $data['print']       = false;
        return view('member.request.tracking', $data);
    }

    public function draft()
    {
        $data['title']       = 'Draft';
        $data['breadcrumps'] = ['Member Area', 'Draft'];
        $where               = "date_deleted IS NULL AND id_transaction_parent IS NULL AND id_user_buyer = " . \Auth::user()->id_user . " OR created_by = '" . \Auth::user()->email . "'";
        $data['list']        = DrService::where('id_user_buyer', \Auth::user()->id_user)->orWhere('id_user_buyer', \Auth::user()->email)->whereNull('date_deleted')->whereNull('id_transaction_parent')->orderBy('date_created', 'DESC')->get()->take(100);

        return view('member.request.draft', $data);
    }

    public function upload_storage_file(Request $req)
    {
        // if (!is_null($req->file)) {
        //   $file = $req->file;
        //   $path = 'assets/temp_upload/'.date('Y').'/'.date('m').'/';
        //   $original_name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME), '-');
        //   $file_name = time().'_'.$original_name.'.'.$file->getClientOriginalExtension();
        //   $file->move(public_path($path), $file_name);
        //   return response()->json(['name' => 'Abigail', 'state' => 'CA']);
        // }
    }

    public function store(Request $req)
    {
        DB::beginTransaction();
        try {
            $validator = $this->check($req);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $table_type            = $req->submit;
            $input                 = $this->prepare_data($req);
            $input['date_created'] = Date('Y-m-d H:i:s');
            $input['created_by']   = \Auth::user()->user_name;

            if ($table_type == 'tr_service') {
                $input['id_status']        = 1;
                $input['is_finished']      = 0;
                $input['date_transaction'] = Date('Y-m-d H:i:s');
                $id                        = DB::table($this->table)->insertGetId($input);
            } else {
                unset($input['id_agency_unit_buyer']);
                $this->table = 'dr_service';
                if ($req->id_draft > 0) {
                    $id = $req->id_draft;
                    DB::table($this->table)->where('id_draft', $id)->update($input);
                } else {
                    $id = DB::table($this->table)->insertGetId($input);
                }
            }

            $workflows  = $req->workflows;
            $id_service = $req->id_service;

            if (! is_null($id)) {
                $service = ServiceList::find($id_service);
                $agency  = AgencyUnit::find($req->id_agency_unit_buyer);

                if ($table_type == 'tr_service') {
                    $tr_service                    = TrService::find($id);
                    $tr_service->transaction_code  = TrService::get_ticket_number_by_country_id(1);
                    $tr_service->agency_name_buyer = \Auth::user()->agency->agency_unit_name;
                    $tr_service->agency_code_buyer = \Auth::user()->agency->agency_unit_code;
                } else {
                    $tr_service = DrService::find($id);
                }
                $tr_service->service_name      = $service->service_name;
                $tr_service->service_code      = $service->service_code;
                $tr_service->user_name_buyer   = \Auth::user()->user_name;
                $tr_service->person_name_buyer = \Auth::user()->person_name;
                $tr_service->save();
                /*-- store workflows --*/
                $this->store_workflows($req, $tr_service, $id, $table_type);
                $this->store_coa($req, $id, $table_type);
            }

            $dr_service = DrService::find($req->id_draft);
            if ($dr_service) {
                if ($table_type != 'dr_service') {
                    $childrendr = DB::table('dr_service')->where('id_transaction_parent', $req->id_draft)->get();
                    foreach ($childrendr as $child) {
                        DB::table("dr_service_workflow")->where('id_transaction', $child->id_draft)
                            ->update(['date_deleted' => Date('Y-m-d H:i:s'), 'deleted_by' => Session::get('user_name')]);

                        $childdr               = DrService::find($child->id_draft);
                        $childdr->date_deleted = Date('Y-m-d H:i:s');
                        $childdr->deleted_by   = \Auth::user()->user_name;
                        $childdr->save();
                    }
                    $rowdr               = DrService::find($req->id_draft);
                    $rowdr->date_deleted = Date('Y-m-d H:i:s');
                    $rowdr->deleted_by   = \Auth::user()->user_name;
                    $rowdr->save();
                }
            }

            DB::commit();

            if ($table_type != 'dr_service') {
                GeneralHelper::add_log(['description' => "Create Request " . $tr_service->transaction_code, 'id_user' => \Auth::user()->id_user]);
                $data['detail']           = TrService::find($id);
                $dataTr['type']           = 'request_user';
                $dataTr['ticket']         = $tr_service->transaction_code;
                $dataTr['description']    = $tr_service->description;
                $dataTr['id_transaction'] = $tr_service->id_transaction;
                if ($req->all_notif_email == 1) {
                    Mail::to(\Auth::user()->email)->send(new SendMail($dataTr));
                }
                $serviceemail = ServiceList::find($id_service);
                $agencyemail  = AgencyUnit::find($serviceemail->id_agency_unit);
                if ($agencyemail->email != null) {
                    $dataTr['type']   = 'request';
                    $agencyemailarray = explode(", ", $agencyemail->email);
                    foreach ($agencyemailarray as $emailagency) {
                        Mail::to($emailagency)->send(new SendMail($dataTr));
                    }
                }

                Mail::to($tr_service->supervisor_mail)->send(new SendMail($dataTr));
                Mail::to('undp.id@undp.org')->send(new SendMail($dataTr));

                // Mail::to()->send(new SendMail($dataTr));
                // EmailHelper::send_new_request_notification($tr_service->supervisor_mail, $tr_service->transaction_code, $tr_service->description);

                return view('member.request.success_transaction', $data);

            } else {
                GeneralHelper::add_log(['description' => "Create Draft  " . $tr_service->id_draft, 'id_user' => \Auth::user()->id_user]);
                return \Redirect::route('myrequests.draft')->with('message_success', 'Data has been saved successfully!');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            GeneralHelper::add_log([
                'type'        => 'error',
                'description' => 'store: ' . $e->getMessage(),
                'id_user'     => \Auth::user()->id_user]);
            // return \Redirect::route('myrequests.create')
            //   ->with('message_error', 'store: '.$e->getMessage());

            Session::flash('message_error', 'store: ' . $e->getMessage());
            return redirect()->back()->withInput()->withErrors($validator);
        }
    }

    public function update(Request $req)
    {
        DB::beginTransaction();
        try {
            $validator = $this->check($req, $req->id_transaction);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $input['date_transaction'] = Date('Y-m-d H:i:s');
            $input['description']      = $this->clean_input_data($req->description);
            $input['id_status']        = 1;
            $id                        = $req->id_transaction;
            DB::table($this->table)->where('id_transaction', $id)->update($input);

            $agency     = AgencyUnit::find($req->agency);
            $workflows  = $req->workflows;
            $id_service = $req->id_service;
            $tr_service = TrService::find($id);

            if (! is_null($id)) {
                $workflow_parent_ids         = [];
                $service_workflows           = $tr_service->service_workflows();
                $tmp["date_start_estimated"] = date('Y-m-d H:i:s');
                $tmp["date_end_estimated"]   = date('Y-m-d H:i:s');
                $tmp["date_start_actual"]    = date('Y-m-d H:i:s');
                $first_sequence              = $service_workflows->first();
                DB::table($this->table . '_workflow')->where('id_transaction_workflow', $first_sequence->id_transaction_workflow)->update($tmp);

                foreach ($service_workflows as $service_workflow) {
                    $infos = $req->required_infos;
                    if (! is_null($infos) && count($infos) > 0) {
                        foreach ($infos as $key => $value) {
                            DB::table($this->table . '_workflow_info')
                                ->where(['id_transaction_workflow' => $service_workflow->id_transaction_workflow, 'id_service_workflow_info' => $key])
                                ->update(['info_value' => $value]);
                        }
                    }

                    $docs = $req->required_docs;
                    if (! is_null($docs) && count($docs) > 0) {
                        foreach ($docs as $key => $file) {
                            // $tr_doc_id = DB::table($this->table.'_workflow_doc')
                            //   ->where(['id_transaction_workflow' => $service_workflow->id_transaction_workflow, 'id_service_workflow_info' => $key])->first();
                            $tr_doc_id = DB::table($this->table . '_workflow_doc')
                                ->where(['id_transaction_workflow' => $service_workflow->id_transaction_workflow, 'id_service_workflow_doc' => $key])->first();

                            if (! is_null($file) && ! is_null($tr_doc_id) && $file->isWritable()) {

                                $path          = 'assets/workflow_docs/' . date('Y') . '/' . date('m') . '/';
                                $original_name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME), '-');
                                $file_name     = time() . '_' . $original_name . '.' . $file->getClientOriginalExtension();
                                $file_path     = $file->getClientOriginalExtension();
                                $file->move(public_path($path), $file_name);
                                $tr_doc_upd                = TrServiceWorkFlowDoc::where('id_transaction_workflow_doc', $tr_doc_id->id_transaction_workflow_doc)->first();
                                $tr_doc_upd->document_path = $path . $file_name;
                                $tr_doc_upd->document_type = $file_path;
                                $tr_doc_upd->save();
                            } else if (! is_null($file) && is_null($tr_doc_id) && $file->isWritable()) {
                                $workflow_doc                      = WorkFlowDoc::find($key);
                                $tr_doc['id_transaction_workflow'] = $service_workflow->id_transaction_workflow;
                                $tr_doc['id_service_workflow_doc'] = $workflow_doc->id_service_workflow_doc;
                                $tr_doc['created_by']              = \Auth::user()->user_name;
                                $tr_doc['document_name']           = $workflow_doc->document_name;
                                $tr_doc['document_comment']        = $workflow_doc->description;
                                $tr_doc['sequence']                = $workflow_doc->sequence;
                                $tr_doc['is_mandatory']            = $workflow_doc->is_mandatory == 1 ? 1 : 0;
                                $tr_doc['date_created']            = DATE('Y-m-d H:i:s');
                                $tr_doc['document_type']           = "";
                                $tr_doc['document_path']           = "";

                                $tr_doc_id_new = DB::table($this->table . "_workflow_doc")->insertGetId($tr_doc);
                                $path          = 'assets/workflow_docs/' . date('Y') . '/' . date('m') . '/';
                                $original_name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME), '-');
                                $file_name     = time() . '_' . $original_name . '.' . $file->getClientOriginalExtension();
                                $file_path     = $file->getClientOriginalExtension();
                                $file->move(public_path($path), $file_name);
                                $tr_doc_upd = TrServiceWorkFlowDoc::find($tr_doc_id_new);

                                $tr_doc_upd->document_path = $path . $file_name;
                                $tr_doc_upd->document_type = $file_path;
                                $tr_doc_upd->save();

                                unset($original_name);
                                unset($file_name);
                                unset($tr_doc);
                            }
                        }
                    }
                }
                $this->store_coa($req, $id);
            }

            GeneralHelper::add_log(['description' => "Update Request " . $tr_service->transaction_code, 'id_user' => \Auth::user()->id_user]);
            DB::commit();

            return \Redirect::route('myrequests.ongoing')->with('message_success', 'Data has been saved successfully!');

        } catch (\Exception $e) {

            DB::rollBack();
            GeneralHelper::add_log([
                'type'        => 'error',
                'description' => 'store_coa: ' . $e->getMessage(),
                'id_user'     => \Auth::user()->id_user]);

            Session::flash('message_error', 'store: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function store_coa($req, $id_transaction, $table_type = 'tr_service')
    {
        try {
            $atlas_depts          = $req->atlas_depts;
            $atlas_perc           = $req->atlas_percents;
            $atlas_contract_num   = $req->atlas_contract_num;
            $atlas_exp_type       = $req->atlas_exp_type;
            $atlas_funding_source = $req->atlas_funding_source;

            if ($req->payment_method != 'transfer_cash') {
                $field_ids = [];
                if ($req->payment_method == 'atlas' && ! is_null($atlas_depts) && count($atlas_depts) > 0) {
                    $field = 'id_transaction_coa';
                    foreach ($atlas_depts as $key => $value) {
                        $coa                     = Coa::find($key);
                        $data                    = [];
                        $data["id_transaction"]  = $id_transaction;
                        $data["id_master_coa"]   = $coa->id_master_coa;
                        $where_coa               = [];
                        $where_coa               = $data;
                        $data["is_service_fee"]  = 1;
                        $data["percentage"]      = $atlas_perc[$key];
                        $data["activities"]      = $coa->activities;
                        $data["acc"]             = 0;
                        $data["opu"]             = $coa->opu;
                        $data["fund"]            = $coa->fund;
                        $data["dept"]            = $value;
                        $data["imp_agent"]       = $coa->imp_agent;
                        $data["donor"]           = $coa->donor;
                        $data["pcbu"]            = $coa->pcbu;
                        $data["project"]         = $coa->project;
                        $data["id_exptype"]      = $atlas_exp_type[$key];
                        $data["contract_number"] = $atlas_contract_num[$key];
                        $data["funding_source"]  = $atlas_funding_source[$key];
                        $data['created_by']      = Session::get('user_name');
                        $data['date_created']    = Date('Y-m-d H:i:s');
                        $table                   = $table_type . '_coa_atlas';

                        $row = DB::table($table)->where($where_coa)->first();

                        if (is_null($row)) {
                            $id_transaction_coa = DB::table($table)->insertGetId($data);
                        } else {
                            $id_transaction_coa = $row->id_transaction_coa;
                            DB::table($table)->where($where_coa)->update($data);
                        }
                        $field_ids[] = $id_transaction_coa;
                    }
                    DB::table($table_type . '_coa_other')->where('id_transaction', $id_transaction)->delete();
                } else {
                    $field = 'id_transaction_coa_other';

                    $projects = $req->non_atlas_projects;
                    $ulos     = $req->non_atlas_ulos;
                    $arns     = $req->non_atlas_arns;
                    $percents = $req->non_atlas_percents;
                    $files    = $req->non_atlas_files;

                    $dr_service = DrService::find($req->id_draft);
                    if ($dr_service) {
                        $table                = 'dr_service_coa_other';
                        $dr_service_coa_other = DB::table($table)->where('id_transaction', $req->id_draft)->get();
                        if ($table_type == 'tr_service') {
                            if (count($dr_service_coa_other) > 0) {
                                foreach ($dr_service_coa_other as $value) {
                                    $data                   = [];
                                    $data['id_transaction'] = $id_transaction;
                                    $data['ulo']            = $value->ulo;
                                    $data['arn']            = $value->arn;
                                    $data['project_no']     = $value->project_no;
                                    $where_coa              = [];
                                    $where_coa              = $data;
                                    $data['percentage']     = $value->percentage;
                                    $data['created_by']     = Session::get('user_name');
                                    $data['date_created']   = Date('Y-m-d H:i:s');
                                    if ($value->file_path) {
                                        $data['file_path'] = $value->file_path;
                                    } else {
                                        $data['file_path'] = '-';
                                    }
                                    $table = $table_type . '_coa_other';
                                    $row   = DB::table($table)->where($where_coa)->first();
                                    if (is_null($row)) {
                                        $id_transaction_coa = DB::table($table)->insertGetId($data);
                                    } else {
                                        $id_transaction_coa = $row->id_transaction_coa_other;
                                        DB::table($table)->where($where_coa)->update($data);
                                    }
                                    $field_ids[] = $id_transaction_coa;
                                }
                            }
                        } else {
                            if (! is_null($projects) && count($projects) > 0) {
                                foreach ($projects as $key => $value) {
                                    $data                   = [];
                                    $data['id_transaction'] = $id_transaction;
                                    $data['ulo']            = $ulos[$key];
                                    $data['arn']            = $arns[$key];
                                    $data['project_no']     = $value;
                                    $where_coa              = [];
                                    $where_coa              = $data;
                                    $data['percentage']     = $percents[$key];
                                    $data['created_by']     = Session::get('user_name');
                                    $data['date_created']   = Date('Y-m-d H:i:s');
                                    if (! is_null($files[$key])) {
                                        $path          = 'assets/coa_other/' . date('Y') . '/' . date('m') . '/';
                                        $original_name = Str::slug(pathinfo($files[$key]->getClientOriginalName(), PATHINFO_FILENAME), '-');
                                        $file_name     = time() . '_' . $original_name . '.' . $files[$key]->getClientOriginalExtension();
                                        $files[$key]->move(public_path($path), $file_name);
                                        $fullname          = $path . $file_name;
                                        $contentname       = $files[$key]->getClientOriginalExtension();
                                        $data['file_path'] = $fullname;
                                    } else {
                                        $data['file_path'] = '-';
                                    }
                                    $table = $table_type . '_coa_other';

                                    $row = DB::table($table)->where($where_coa)->first();

                                    if (is_null($row)) {
                                        $id_transaction_coa = DB::table($table)->insertGetId($data);
                                    } else {
                                        $id_transaction_coa = $row->id_transaction_coa_other;
                                        DB::table($table)->where($where_coa)->update($data);
                                    }
                                    $field_ids[] = $id_transaction_coa;
                                }
                            }
                        }
                        // DB::table('dr_service_coa_other')->where('id_transaction', $req->id_draft)->delete();
                    } else {
                        if (! is_null($projects) && count($projects) > 0) {
                            foreach ($projects as $key => $value) {
                                $data                   = [];
                                $data['id_transaction'] = $id_transaction;
                                $data['ulo']            = $ulos[$key];
                                $data['arn']            = $arns[$key];
                                $data['project_no']     = $value;
                                $where_coa              = [];
                                $where_coa              = $data;
                                $data['percentage']     = $percents[$key];
                                $data['created_by']     = Session::get('user_name');
                                $data['date_created']   = Date('Y-m-d H:i:s');
                                if (! is_null($files[$key])) {
                                    $path          = 'assets/coa_other/' . date('Y') . '/' . date('m') . '/';
                                    $original_name = Str::slug(pathinfo($files[$key]->getClientOriginalName(), PATHINFO_FILENAME), '-');
                                    $file_name     = time() . '_' . $original_name . '.' . $files[$key]->getClientOriginalExtension();
                                    $files[$key]->move(public_path($path), $file_name);
                                    $fullname          = $path . $file_name;
                                    $contentname       = $files[$key]->getClientOriginalExtension();
                                    $data['file_path'] = $fullname;
                                } else {
                                    $data['file_path'] = '-';
                                }
                                $table = $table_type . '_coa_other';

                                $row = DB::table($table)->where($where_coa)->first();

                                if (is_null($row)) {
                                    $id_transaction_coa = DB::table($table)->insertGetId($data);
                                } else {
                                    $id_transaction_coa = $row->id_transaction_coa_other;
                                    DB::table($table)->where($where_coa)->update($data);
                                }
                                $field_ids[] = $id_transaction_coa;
                            }
                        }
                    }
                    DB::table($table_type . '_coa_atlas')->where('id_transaction', $id_transaction)->delete();
                }

                if (isset($table)) {
                    $field_id = 'id_transaction';
                    DB::table($table)->where($field_id, $id_transaction)->whereNotIn($field, $field_ids)->delete();
                }
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function store_workflows($req, $tr_service, $id, $table_type = 'tr_service')
    {
        try {
            $total_day           = 0;
            $sequence            = 1;
            $start_date          = DATE('Y-m-d H:i:s');
            $workflow_ids        = [];
            $workflow_parent_ids = [];
            $id_service_insert   = [];
            $docs                = $req->required_docs;
            $infos               = $req->required_infos;
            $workflows           = $req->workflows;

            foreach ($workflows as $key => $value) {
                $workflow_ids[] = $key;
            }

            foreach ($req->checkbox_price_input as $key => $service_input) {
                array_push($id_service_insert, $key);
            }

            if (count($workflow_ids) > 0) {
                $total_day = DB::select("SELECT SUM(workflow_day) as total_day FROM ms_service_workflow
          WHERE id_service_workflow IN (" . implode(',', $workflow_ids) . ')')[0]->total_day;
            }

            $id_service_group = null;
            $sequence         = 1;
            foreach ($workflows as $key => $value) {
                $current_workflow = WorkFlow::find($key);
                if (in_array($current_workflow->id_service, $id_service_insert)) {
                    $existing_child = DB::table($this->table)->where(['id_transaction_parent' => $id,
                        'id_service'                                                              => $current_workflow->service->id_service])->first();

                    if (is_null($existing_child)) {

                        $group                          = [];
                        $group["id_transaction_parent"] = $id;
                        $group["id_service"]            = $current_workflow->service->id_service;
                        $group["service_name"]          = $current_workflow->service->service_name;
                        $group["service_price"]         = $value * $tr_service->qty;
                        $group['date_created']          = Date('Y-m-d H:i:s');
                        $group['created_by']            = \Auth::user()->user_name;

                        $id_transaction_child = DB::table($this->table)->insertGetId($group);

                    } else {
                        $id_transaction_child = ($this->table == 'tr_service') ? $existing_child->id_transaction : $existing_child->id_draft;
                    }

                    if ($this->table == 'tr_service') {
                        $sla_day                        = $current_workflow->workflow_day;
                        $tr_workflow["id_transaction"]  = $id_transaction_child;
                        $tr_workflow["sequence"]        = $sequence;
                        $tr_workflow["workflow_name"]   = $current_workflow->workflow_name;
                        $tr_workflow["workflow_day"]    = $current_workflow->workflow_day;
                        $tr_workflow["percentage_work"] = ($total_day != 0) ? round($sla_day * 100 / $total_day, 2) : 0;

                        $end_date = WorkFlow::get_next_workday($start_date, $current_workflow->workflow_day);

                        $tr_workflow["date_start_estimated"] = $start_date;
                        $tr_workflow["date_end_estimated"]   = $end_date;

                        if ($sequence == 1) {
                            $tr_workflow["date_start_actual"] = $start_date;
                        }

                        $start_date                       = $end_date;
                        $tr_workflow["is_start_billing"]  = $current_workflow->is_start_billing == 1;
                        $tr_workflow["is_start_contract"] = $current_workflow->is_start_contract == 1;

                        $tr_workflow["id_user_pic_primary"]   = $current_workflow->id_user_pic_primary;
                        $tr_workflow["id_user_pic_alternate"] = $current_workflow->id_user_pic_alternate;
                        $workflow_agency                      = $current_workflow->agency;
                        $tr_workflow["id_agency_unit_pic"]    = ! is_null($workflow_agency) ? $current_workflow->agency->id_agency_unit : null;
                        $tr_workflow["agency_unit_code_pic"]  = ! is_null($workflow_agency) ? $current_workflow->agency->agency_unit_code : null;
                        $tr_workflow["agency_unit_name_pic"]  = ! is_null($workflow_agency) ? $current_workflow->agency->agency_unit_name : null;

                        $id_transaction_workflow                   = DB::table($this->table . '_workflow')->insertGetId($tr_workflow);
                        $id_service_workflow                       = $current_workflow->id_service_workflow;
                        $workflow_parent_ids[$id_service_workflow] = $id_transaction_workflow;
                        unset($tr_workflow);
                        $sequence++;
                    } else {
                        $sla_day                        = $current_workflow->workflow_day;
                        $tr_workflow["id_transaction"]  = $id_transaction_child;
                        $tr_workflow["sequence"]        = $sequence;
                        $tr_workflow["workflow_name"]   = $current_workflow->workflow_name;
                        $tr_workflow["workflow_day"]    = $current_workflow->workflow_day;
                        $tr_workflow["percentage_work"] = ($total_day != 0) ? round($current_workflow->workflow_day * 100 / $total_day, 2) : 0;

                        $id_transaction_workflow                   = DB::table($this->table . '_workflow')->insertGetId($tr_workflow);
                        $id_service_workflow                       = $current_workflow->id_service_workflow;
                        $workflow_parent_ids[$id_service_workflow] = $id_transaction_workflow;
                        unset($tr_workflow);
                        $sequence++;
                    }
                }
            }

            if ($this->table == 'tr_service') {
                $this->store_workflow_info($infos, $workflow_parent_ids);
                $this->store_workflow_doc($docs, $workflow_parent_ids, $this->table, null, $req);
            } else {
                $this->store_workflow_doc($docs, $workflow_parent_ids, $this->table, $id, $req);
            }

        } catch (\Exception $e) {
            throw new \Exception("store_workflows: " . $e->getMessage());
        }
    }

    public function store_workflow_info($infos, $workflow_parent_ids)
    {
        try {
            if (is_null($infos) || count($infos) < 1) {
                return null;
            }

            $workflow_info_ids = [];
            foreach ($infos as $key => $value) {
                $workflow_info_ids[] = $key;
            }

            $sent_workflow_infos = WorkFlowInfo::whereIn('id_service_workflow_info', $workflow_info_ids)->get();
            foreach ($sent_workflow_infos as $info) {
                $tr_workflow_info["id_transaction_workflow"]  = $workflow_parent_ids[$info->id_service_workflow];
                $tr_workflow_info['id_service_workflow_info'] = $info->id_service_workflow_info;
                $where                                        = $tr_workflow_info;
                $tr_workflow_info["info_title"]               = $info->info_title;
                $tr_workflow_info["info_value"]               = $infos[$info->id_service_workflow_info];
                $tr_workflow_info["info_type"]                = $info->control_type;
                $tr_workflow_info["description"]              = $info->description;
                $tr_workflow_info["date_created"]             = Date('Y-m-d H:i:s');
                $tr_workflow_info["created_by"]               = \Auth::user()->user_name;
                $tr_workflow_info["is_mandatory"]             = $info->is_mandatory == 1 ? 1 : 0;

                if (DB::table($this->table . '_workflow_info')->where($where)->count() < 1) {
                    DB::table($this->table . '_workflow_info')->insert($tr_workflow_info);
                } else {
                    DB::table($this->table . '_workflow_info')->where($where)->update($tr_workflow_info);
                }
                unset($where);
                unset($tr_workflow_info);
            }
        } catch (\Exception $e) {
            throw new \Exception("store_workflow_info: " . $e->getMessage());
        }
    }

    public function store_workflow_doc($docs, $workflow_parent_ids, $tableType, $id, $request)
    {
        try {

            // dd($request->id_draft);
            $dr_service = DrService::find($request->id_draft);
            // dd($dr_service);
            if ($dr_service) {
                $DrServiceDoc = DrServiceDoc::with('workflowDoc')->where('id_draft', $request->id_draft)->get();
                if (count($DrServiceDoc) > 0) {
                    foreach ($DrServiceDoc as $doc) {
                        $tr_doc['id_transaction_workflow'] = $workflow_parent_ids[$doc->workflowDoc->id_service_workflow];
                        $tr_doc['id_service_workflow_doc'] = $doc->workflowDoc->id_service_workflow_doc;
                        $where                             = $tr_doc;
                        $tr_doc['created_by']              = \Auth::user()->user_name;
                        $tr_doc['document_name']           = $doc->workflowDoc->document_name;
                        $tr_doc['document_comment']        = $doc->workflowDoc->description;
                        $tr_doc['sequence']                = $doc->workflowDoc->sequence;
                        $tr_doc['is_mandatory']            = $doc->workflowDoc->is_mandatory == 1 ? 1 : 0;
                        $tr_doc['date_created']            = DATE('Y-m-d H:i:s');
                        $tr_doc['document_type']           = $doc->doc_type;
                        $tr_doc['document_path']           = $doc->doc_name;

                        $tr_doc_db = DB::table('tr_service_workflow_doc')->where($where)->first();

                        if (is_null($tr_doc_db)) {
                            $tr_doc_id = DB::table("tr_service_workflow_doc")->insertGetId($tr_doc);
                        } else {
                            $tr_doc_id = $tr_doc_db->id_transaction_workflow_doc;
                        }

                        unset($where);
                        unset($tr_doc);
                    }
                }
            }

            if ($tableType == 'tr_service') {
                if (is_null($docs) || count($docs) < 1) {
                    return null;
                }

                $workflow_doc_ids = [];
                foreach ($docs as $key => $value) {
                    $workflow_doc_ids[] = $key;
                }
                foreach ($docs as $key => $file) {
                    $workflow_doc                      = WorkFlowDoc::find($key);
                    $tr_doc['id_transaction_workflow'] = $workflow_parent_ids[$workflow_doc->id_service_workflow];
                    $tr_doc['id_service_workflow_doc'] = $workflow_doc->id_service_workflow_doc;
                    $where                             = $tr_doc;
                    $tr_doc['created_by']              = \Auth::user()->user_name;
                    $tr_doc['document_name']           = $workflow_doc->document_name;
                    $tr_doc['document_comment']        = $workflow_doc->description;
                    $tr_doc['sequence']                = $workflow_doc->sequence;
                    $tr_doc['is_mandatory']            = $workflow_doc->is_mandatory == 1 ? 1 : 0;
                    $tr_doc['date_created']            = DATE('Y-m-d H:i:s');
                    $tr_doc['document_type']           = "";
                    $tr_doc['document_path']           = "";

                    $tr_doc_db = DB::table($this->table . '_workflow_doc')->where($where)->first();

                    if (is_null($tr_doc_db)) {
                        $tr_doc_id = DB::table($this->table . "_workflow_doc")->insertGetId($tr_doc);
                    } else {
                        $tr_doc_id = $tr_doc_db->id_transaction_workflow_doc;
                    }

                    if (! is_null($file) && ! is_null($tr_doc_id)) {
                        $path          = 'assets/workflow_docs/' . date('Y') . '/' . date('m') . '/';
                        $original_name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME), '-');
                        $file_name     = time() . '_' . $original_name . '.' . $file->getClientOriginalExtension();
                        $file->move(public_path($path), $file_name);
                        $tr_doc_upd                = TrServiceWorkFlowDoc::find($tr_doc_id);
                        $tr_doc_upd->document_path = $path . $file_name;
                        $tr_doc_upd->document_type = $file->getClientOriginalExtension();
                        $tr_doc_upd->save();
                    }

                    unset($where);
                    unset($tr_doc);
                }
            } else {
                if (is_null($docs) || count($docs) < 1) {
                    return null;
                }

                $workflow_doc_ids = [];
                foreach ($docs as $key => $value) {
                    $workflow_doc_ids[] = $key;
                }
                $dr_service = DrService::find($id);
                foreach ($docs as $key => $file) {
                    $fullname    = null;
                    $contentname = null;

                    if (! is_null($file)) {
                        $path          = 'assets/workflow_docs/' . date('Y') . '/' . date('m') . '/';
                        $original_name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME), '-');
                        $file_name     = time() . '_' . $original_name . '.' . $file->getClientOriginalExtension();
                        $file->move(public_path($path), $file_name);
                        $fullname    = $path . $file_name;
                        $contentname = $file->getClientOriginalExtension();
                    }
                    $workflow_doc           = WorkFlowDoc::find($key);
                    $tr_doc['id_workflow']  = $workflow_doc->id_service_workflow_doc;
                    $where                  = $tr_doc;
                    $tr_doc['id_draft']     = $dr_service->id_draft;
                    $tr_doc['created_by']   = \Auth::user()->user_name;
                    $tr_doc['doc_name']     = $fullname;
                    $tr_doc['doc_type']     = $contentname;
                    $tr_doc['date_created'] = DATE('Y-m-d H:i:s');

                    $tr_doc_id = DB::table($tableType . "_doc")->insertGetId($tr_doc);

                    unset($where);
                    unset($tr_doc);
                }
            }

        } catch (\Exception $e) {
            throw new \Exception("store_workflow_doc: " . $e->getMessage());
        }
    }

    public function check($request, $id = null)
    {
        // $additional_code_rule = $id == null ? '|unique:ms_service' : '';
        $additional_code_rule = '';
        $rules                = [
            'id_user_buyer'   => 'required',
            'id_currency'     => 'required',
            'supervisor_mail' => 'required|email',
            'qty'             => 'required',
            'description'     => 'required',
            'service_price'   => 'required',
            'payment_method'  => 'required',
            // 'all_notif_email' => 'required',
        ];

        if (is_null($id)) {
            $rules['id_service']             = 'required';
            $rules['id_agency_unit_buyer']   = 'required';
            $rules['id_agency_unit_service'] = 'required';
        }
        return Validator::make($request->all(), $rules);
    }

    public function prepare_data($req)
    {
        $input['id_service']             = $req->id_service;
        $input['id_agency_unit_buyer']   = \Auth::user()->id_agency_unit;
        $input['id_agency_unit_service'] = $req->id_agency_unit_service;
        $input['id_user_buyer']          = \Auth::user()->id_user;
        $input['id_currency']            = $req->id_currency;
        $input['supervisor_mail']        = $req->supervisor_mail;
        $input['qty']                    = $req->qty;
        $input['description']            = $this->clean_input_data($req->description);
        $input['service_price']          = $req->service_price;
        $input['payment_method']         = $req->payment_method;
        $input['all_notif_email']        = 1;
        // $input['all_notif_email']        = $req->all_notif_email;
        return $input;
    }

    public function old_input()
    {
        $data['id_agency_unit_service'] = $this->input->post('service_category');
        $data['id_service']             = $this->input->post('service');
        $data['service_code']           = $this->input->post('service_code');
        $data['service_name']           = $this->input->post('service_name');
        $data['id_agency_unit_buyer']   = $this->session->userdata('id_agency_unit');
        $data['agency_code_buyer']      = $this->session->userdata('agency_unit_code');
        $data['agency_name_buyer']      = $this->session->userdata('agency_unit_name');
        $data['id_user_buyer']          = $this->session->userdata('id_user');
        $data['user_name_buyer']        = $this->session->userdata('user_name');
        $data['person_name_buyer']      = $this->session->userdata('person_name');
//        $data['id_uom'] = $this->input->post('id_uom');
//        $data['uom_code'] = $this->input->post('uom_code');
//        $data['uom_name'] = $this->input->post('uom');
        $data['id_currency']     = $this->input->post('id_currency');
        $data['currency_name']   = "USD";
        $data['currency_code']   = $this->input->post('currency_code');
        $data['supervisor_mail'] = $this->input->post('supervisor_mail');
        $data['qty']             = $this->input->post('quantity');
        $data['description']     = $this->input->post('desc');
//        $data['ereq'] = $this->input->post('e-req');
        $data['service_price'] = $this->input->post('total_amount');
        $data['id_project']    = $this->input->post('project');
//        $data['project_code'] = $this->input->post('project_code');
//        $data['project_name'] = $this->input->post('project_name');
        $data['date_transaction'] = date(DATE_FORMAT_MYSQL);
        $data['id_status']        = 1;
        $data['payment_method']   = $this->input->post('payment_type');
        $data['is_finished']      = 0;
        $data['all_notif_email']  = 1;
        // $data['all_notif_email']  = $this->input->post('all_notif_email') == "1" ? 1 : 0;

        $this->load->model("tr_service");
// GET TICKET
        $this->load->model("view_new_ticket");
        $this->load->model("ms_agency_unit");
        $parent     = $this->ms_agency_unit->get_by_id($this->input->post("agency"));
        $id_country = $parent["data"][0]["id_country"];

        $this->load->model("ms_country");
        $country      = $this->ms_country->get_by_id($id_country);
        $country_code = $country["data"][0]["country_code"];

        $ticket_new  = $this->view_new_ticket->get_ticket_no($country_code);
        $ticket_code = "T" . strtoupper($country_code) . str_pad($ticket_new, 10, "0", STR_PAD_LEFT);

        $data["transaction_code"] = $ticket_code;

        $sla_day = $row["workflow_day"];

        $tr_workflow["id_transaction"] = $id_group;
        $tr_workflow["sequence"]       = $sequence;
        $tr_workflow["workflow_name"]  = $row["workflow_name"];
        $tr_workflow["workflow_day"]   = $row["workflow_day"];

        $tr_workflow["percentage_work"] = round($row["workflow_day"] * 100 / $total_day, 2);

//                $end_date = workday_add_obj($start_date, $row["workflow_day"]);
        $end_date                            = $this->view_workday->get_next_workday($start_date, $row["workflow_day"]);
        $tr_workflow["date_start_estimated"] = $start_date;
        $tr_workflow["date_end_estimated"]   = $end_date;
        if ($sequence == 1) {
            $tr_workflow["date_start_actual"] = $start_date;
        }
        $start_date = $end_date;

        $tr_workflow["id_agency_unit_pic"]   = $row["id_agency_unit"];
        $tr_workflow["agency_unit_code_pic"] = $row["agency_unit_code"];
        $tr_workflow["agency_unit_name_pic"] = $row["agency_unit_name"];

        $tr_workflow["id_user_pic_primary"]   = $row["id_user_pic_primary"];
        $tr_workflow["id_user_pic_alternate"] = $row["id_user_pic_alternate"];

        $tr_workflow["is_start_billing"]  = $row["is_start_billing"] == 1;
        $tr_workflow["is_start_contract"] = $row["is_start_contract"] == 1;

//                var_dump($tr_workflow);
        $this->tr_service_workflow->insert($tr_workflow);
        $id_transaction_workflow = $this->db->insert_id();
        $id_service_workflow     = $row["id_service_workflow"];
    }

    # general API

    public function get_coa_by_project($project_code, Request $req)
    {
        $donor = "";
        if (! is_null($req->donor)) {
            $donor = "and `donor` = '" . $req->donor . "' ";
        }
        $activity = "";
        if (! is_null($req->activity)) {
            $activity = "and `activities` = '" . $req->activity . "' ";
        }
        $project_code = strtolower($project_code);
        $results      = Coa::select(DB::raw('DISTINCT(id_master_coa) as id_master_coa,ms_coa.*'))->whereRaw("LOWER(project) = '" . $project_code . "' $donor $activity")->orderBy("pcbu")->get();
        $exptype      = DB::table('ms_exp_type')
            ->selectRaw('id_exptype, exp_type_code, exp_type_name, description')->orderBy('exp_type_code', 'asc')
            ->get();
        return response()->json(['data' => $results, 'exp_type' => $exptype]);
    }

    public function get_projects(Request $req)
    {
        $keyword = strtolower($req->keyword);
        $results = Coa::select(DB::raw("DISTINCT(project) as id, project as text"))->whereRaw("LOWER(project) LIKE '%" . $keyword . "%'")
            ->orderBy("project")->take(25)->get();
        return response()->json(['data' => $results]);
    }

    public function ongoing_search()
    {
        return Datatables::of(
            TrService::ongoing_search([
                'id_agency_unit_service'      => Session::get('user_agency_unit_id'),
                'with_workflow_begin_and_end' => true,
            ]))
            ->editColumn('delay_duration', function ($list) {return $list->delay_duration > 0 ? 'Delayed' : 'Ontime';})
            ->make(true);
    }

    public function ongoing_request_search(Request $req)
    {
        return Datatables::of(
            TrService::ongoing_search([
                'id_agency_unit_buyer' => Session::get('user_agency_unit_id'),
                'user_name_buyer'      => Session::get('user_name'),
                'id_status'            => '-1,0,1,2,5,6,7',
            ]))
            ->editColumn('id_project', function ($list) {return '-';})
        // ->editColumn('date_authorized', function($list){ return date('d-m-Y', strtotime($list->date_authorized)); })
            ->editColumn('service_name', function ($list) {
                $desc = $list->service_name . ' - ' . $list->description;
                if (! empty($list->comment) && $list->id_status == -1) {
                    $desc .= "<br>comment: " . $list->comment;
                }

                return $desc;
            })
            ->editColumn('delay_duration', function ($list) {return $list->delay_duration . ' day(s)';})
            ->addColumn('action', function ($list) {
                $action = "";
                if ($list->id_status == '1') {
                    $action = "<a href='' title='Please Response'><i class='fa fa-phone'></i></a> | ";
                    $action .= "<a href='" . route('myrequests.delete', [$list->id_transaction]) . "' title='Delete'><i class='fa fa-trash'></i> </a>";
                } elseif ($list->id_status == '-1') {
                    $action = "<a href='" . route('myrequests.edit', [$list->id_transaction]) . "' title='Edit'><i class='fa fa-edit'></i> </a> | ";
                    $action .= "<a href='" . route('myrequests.delete', [$list->id_transaction]) . "' title='Delete'><i class='fa fa-trash'></i> </a>";
                } elseif ($list->date_finished != null && $list->date_rating == null) {
                    $action = "<a href='' title='Please Rate'><i class='fa fa-star'></i></a>";
                }
                return $action;
            })->make(true);
    }

    public function old_ongoing_request_search(Request $req)
    {
        $where = "( id_agency_unit_buyer=" . session('user_agency_unit_id') . " OR user_name_buyer = '" . session('user_name') . "' )";

        $start_date = $req->start_date;
        $end_date   = $req->end_date;

        if (! empty($start_date)) {
            if (! empty($end_date)) {
                $where .= " AND (DATE(tr_service.date_transaction) BETWEEN '" . $start_date . "' AND '" . $end_date . "') ";
            } else {
                $where .= " AND DATE(tr_service.date_transaction) = '" . $start_date . "'";
            }
        }

        if ($req->is_mine == 1) {
            $where .= " AND tr_service.id_user_buyer = " . session('user_id');
        }

        if (! empty($req->status)) {
            $reject = null;
            $where .= " AND id_status = '" . $req->status . "' " . $reject;
        }

        if (! empty($req->id_service_unit)) {
            $where .= " AND id_agency_unit_service = '" . $req->id_service_unit . "'";
        }

        $where .= " AND date_deleted IS NULL";

        $list = RequestQuery::ongoing($where);

        return Datatables::of($list)
            ->editColumn('date_authorized', function ($list) {
                return (! empty($list->date_authorized)) ? date('d-m-Y', strtotime($list->date_authorized)) : "";
            })
            ->editColumn('id_project', function ($list) {return '-';})
            ->editColumn('transaction_code', function ($list) {return "<a href='" . route('myservices.view', [$list->id_transaction]) . "'>" . $list->transaction_code . "</a>";})
            ->editColumn('service_name', function ($list) {
                $desc = $list->service_name . ' - ' . $list->description;
                if (! empty($list->comment) && $list->id_status == -1) {
                    $desc .= "<br><b>Return comment: </b><br>" . $list->comment;
                }

                return $desc;
            })
            ->addColumn('action', function ($list) {
                $action = "";
                if ($list->id_status == '1') {
                    $action = "<a href='' title='Please Response'><i class='fa fa-phone'></i></a> | ";
                    $action .= "<a href='" . route('myrequests.delete', [$list->id_transaction]) . "' title='Delete'><i class='fa fa-trash'></i> </a>";
                } elseif ($list->id_status == '-1') {
                    $action = "<a href='" . route('myrequests.edit', [$list->id_transaction]) . "' title='Edit'><i class='fa fa-edit'></i> </a> | ";
                    $action .= "<a href='" . route('myrequests.delete', [$list->id_transaction]) . "' title='Delete'><i class='fa fa-trash'></i> </a>";
                } elseif ($list->date_finished != null && $list->date_rating == null) {
                    $action = "<a href='#' title='Please Rate' ";
                    $action .= "onclick=\"addRating(" . $list->id_transaction . ",'" . $list->agency_name_service . "','" . addslashes($list->service_name) . "','" . $list->delay . "')\">";
                    $action .= "<i class='fa fa-star'></i></a>";
                }if (in_array(session('user_id'), [$list->id_user_pic_primary, $list->id_user_pic_alternate])) {
                    $action .= "<a href='" . route('myservices.view_with_response_action', ['id_transaction' => $list->id_transaction]) . "' onClick='showLoading()' class='btn btn-sm btn-clean btn-icon btn-icon-md' title='Need Action'><i class='fa fa-eye'></i></a>";
                }
                return $action;
            })
            ->rawColumns(['transaction_code', 'action', 'service_name'])
            ->make(true);
    }

    public function ongoing_search_home(Request $req)
    {
        $where = "( id_agency_unit_service =" . session('user_agency_unit_id') . " AND id_status = 2 )";
        $list  = RequestQuery::ongoing_home($where);
        return Datatables::of($list)
            ->editColumn('date_authorized', function ($list) {
                return (! empty($list->date_authorized)) ? date('d-M-Y', strtotime($list->date_authorized)) : "";
            })
            ->editColumn('transaction_code', function ($list) {
                return $list->transaction_code;
            })
            ->editColumn('service_name', function ($list) {
                return $list->service_name . ' - ' . $list->description;
            })
            ->editColumn('agency_name_buyer', function ($list) {
                return $list->agency_name_buyer;
            })
            ->editColumn('delay', function ($list) {return $list->delay . ' day(s)';})
        // ->editColumn('service_name', function($list){ return $list->service_name.' - '.$list->description; })
            ->addColumn('action', function ($list) {
                return "<a href='" . route('myservices.view', [$list->id_transaction]) . "' title='view'><i class='fa fa-eye'></i> </a>";
            })->make(true);
    }

    public function history_request_search(Request $req)
    {
        /*
      TrService::ongoing_search([
      'id_agency_unit_buyer' => Session::get('user_agency_unit_id'),
      'user_name_buyer' => Session::get('user_name'),
      'id_status' => '3,5,6,7',
      'with_rating_only' => true
      ]))
    */

        return Datatables::of(
            TrService::history_search([
                'rating'               => $req->rating,
                'id_service_unit'      => $req->id_service_unit,
                'start_date'           => $req->start_date,
                'end_date'             => $req->end_date,
                'id_agency_unit_buyer' => Session::get('user_agency_unit_id'),
                'user_name_buyer'      => Session::get('user_name'),
                'id_status'            => '3,5,6,7',
                'with_rating_only'     => true,
            ]))
            ->editColumn('id_project', function ($list) {return '-';})
        // ->editColumn('date_authorized', function($list){ return date('d-m-Y', strtotime($list->date_authorized)); })
            ->editColumn('service_name', function ($list) {return $list->service_name . ' - ' . $list->description;})
            ->editColumn('delay_duration', function ($list) {return $list->delay_duration . ' day(s)';})
            ->addColumn('action', function ($list) {
                $action = "";
                $action .= "<a href='" . route('myservices.view', ['id_transaction' => $list->id_transaction]) . "' onClick='showLoading()' class='btn btn-sm btn-clean btn-icon btn-icon-md' title='Need Action'><i class='fa fa-eye'></i></a> ";
                // if ($list->id_status == '-1') {
                //   $action .= "<a href='' title='Please Response'><i class='fa fa-edit'></i></a> | ";
                //   $action .= "<a href='' title='Delete'><i class='fa fa-trash'></i> </a>";
                // }elseif ($list->date_finished != null && $list->date_rating == null) {
                //   $action .= "<a href='' title='Please Rate'><i class='fa fa-star'></i></a>";
                // }
                return $action;
            })->make(true);
    }

    public function tracking_request_search(Request $req)
    {
        $id_agency_unit_service = $req->category;
        $id_agency_unit_buyer   = $req->with_id_agency_buyer > 0 ? Session::get('user_agency_unit_id') : null;
        $transaction_code       = $req->transaction_code;

        $tr = TrService::tracking([
            'transaction_code'       => $transaction_code,
            'id_agency_unit_service' => $id_agency_unit_service,
            'id_agency_unit_buyer'   => $id_agency_unit_buyer,
            'id_status'              => '2',
        ]);

        $id_current = 0;
        $data       = ['trackings' => [], 'workflows' => []];
        if (! is_null($tr)) {

            foreach ($tr as &$t) {
                $end_date = date('Y-m-d');
                if (empty($t->date_end_actual)) {
                    $t->style    = "";
                    $t->date_end = "-";
                } else {
                    $end_date    = $t->date_end_actual;
                    $t->date_end = date('d-M-Y', strtotime($t->date_end_actual));
                    $t->style    = "background-color: #CFC";
                }

                $t->delay = GeneralHelper::workday_delay($end_date, $t->date_end_estimated);
                if ($t->delay > 0) {
                    $t->style = "background-color: #FCC";
                    if ($t->date_end == "-") {
                        $t->date_end = "";
                    }
                    if (! empty($t->date_start_actual) && ! $t->date_end_actual) {
                        $t->date_end = "Current";
                    }
                }

                if ($id_current != $t->id_transaction) {
                    $id_current          = $t->id_transaction;
                    $current_transaction = [
                        'name'             => $t->agency_name_service . " - " . $t->service_name,
                        'description'      => $t->description,
                        'transaction_code' => $t->transaction_code,
                        'date_authorized'  => date('d-M-Y', strtotime($t->date_authorized)),
                        'workflows'        => [$t],

                    ];
                    $data["trackings"][$id_current] = $current_transaction;
                    // $data["data"][$t->agency_name_service." - ".$t->service_name][$id_current]['seq'.$t->sequence] = $t;
                }
                if ($t->sequence > 1) {
                    $data["trackings"][$id_current]['workflows'][] = $t;
                }

            }
        }
        $data['transaction_code']     = $transaction_code;
        $data['category']             = $req->category;
        $data['with_id_agency_buyer'] = $req->with_id_agency_buyer;
        $data['title']                = 'Tracking';
        $data['print']                = (count($data['trackings']) > 0 && $req->btn_submit == 'excel');
        $data['breadcrumps']          = ['Member Area', 'Tracking'];
        return view('member.request.tracking', $data);
        // return response()->json($data);
    }

    public function test_code()
    {
        // $tr_service = TrService::whereRaw('id_transaction_parent IS NULL')->orderBy('id_transaction', 'desc')->first();
        // EmailHelper::send_new_request_notification('im.rahmat11@gmail.com', $tr_service->transaction_code, $tr_service->description);
        // dd(GeneralHelper::send_email("im.rahmat11@gmail.com", "[OneClick] - New Request - ", "TEST", null, null, false));
    }

    public function delete_transaction(Request $req)
    {
        try {
            DB::beginTransaction();

            $id                = $req->id_transaction;
            $TrServiceParent   = TrService::find($id);
            $TrServiceRaw      = TrService::where('id_transaction_parent', $id);
            $TrService         = $TrServiceRaw->get();
            $TrServiceId       = $TrServiceRaw->pluck('id_transaction')->toArray();
            $TrServiceWorkFlow = TrServiceWorkFlow::with('docs', 'infos')->whereIn('id_transaction', $TrServiceId)->get();

            //workflow
            foreach ($TrServiceWorkFlow as $workflow) {
                //infos

                foreach ($workflow->infos as $info) {
                    if ($info) {
                        $info->deleted_by = \Auth::user()->id_user;
                        $info->save();
                        $info->delete();
                    }
                }
                //docs
                foreach ($workflow->docs as $doc) {
                    if ($doc) {
                        $doc->deleted_by = \Auth::user()->id_user;
                        $doc->save();
                        $doc->delete();
                    }
                }
                $workflow->delete();
            }

            //child
            foreach ($TrService as $service) {
                $service->delete();
            }
            //parent
            $TrServiceParent->delete();

            GeneralHelper::add_log(['description' => "DELETE Transaction ticket " . $TrServiceParent->transaction_code, 'id_user' => \Auth::user()->id_user]);
            Session::flash('message_success', 'Data has been deleted');
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            GeneralHelper::add_log(['type' => 'error', 'description' => $e->getMessage(), 'id_user' => \Auth::user()->id_user]);
            Session::flash('message_error', $e->getMessage());
        }

        return redirect()->back();
    }

}
