<?php
namespace App\Http\Controllers;

use App\AgencyUnit;
use App\DrServiceDoc;
use App\GeneralHelper;
use App\Mail\SendMail;
use App\RequestQuery;
use App\SecUser;
use App\ServiceList;
use App\TrService;
use App\TrServiceWorkFlow;
use App\TrServiceWorkFlowDoc;
use App\TrServiceWorkFlowInfo;
use App\WorkFlow;
use App\WorkFlowDoc;
use App\WorkFlowInfo;
use Datatables;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Session;

date_default_timezone_set('Asia/Jakarta');
define('DATE_TIME', 'Y-m-d H:i:s');
define('SERVICE_ERROR_MESSAGE', "Sorry, You aren't allowed to see the Request!");
class MemberServiceController extends Controller
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

    public function api_user_middleware(Request $request)
    {
        return $request->user();
    }

    public function check_access($detail)
    {
        return GeneralHelper::check_access($detail);
    }

    public function index(Request $request)
    {
        $data['title']       = 'My Service';
        $data['breadcrumps'] = ['Member Area', 'My Service'];
        $status              = null;
        $tiker_tr            = null;
        $type                = null;
        if ($request->status != null) {
            $status   = $request->status;
            $tiker_tr = $request->tiker_tr;
            $type     = $request->type;
        }
        $data['status']   = $status;
        $data['tiker_tr'] = $tiker_tr;
        $data['type']     = $type;
        return view('member.service.index', $data);
    }

    public function show($id_transaction)
    {
        try {
            $detail = TrService::find($id_transaction);

            if (! $this->check_access($detail)) {
                Session::flash('message_error', SERVICE_ERROR_MESSAGE);
                return redirect()->back();
            }

            $data['title']       = 'My Service Detail';
            $data['detail']      = $detail;
            $service_agency      = AgencyUnit::find($detail->id_agency_unit_service);
            $data['users']       = SecUser::get_by_id_agency_parent2();
            $data['breadcrumps'] = ['Member Area', $data['title']];
            $data['docs']        = $detail->required_docs();
            $data['infos']       = $detail->required_infos();
            $data['workflows']   = $detail->service_workflows();
            GeneralHelper::add_log(['description' => "Show Service " . $detail->transaction_code, 'id_user' => \Auth::user()->id_user]);
            // dd($data);
            return view('member.service.show', $data);
        } catch (\Exception $e) {
            return redirect()->back();
        }
    }

    public function download_file($url)
    {
        $path = 'app/public/files/' . $url;
        return redirect('/storage/' . $path);
    }

    public function view($id_transaction)
    {
        try {
            $data['title'] = 'My Service Detail';
            $detail        = TrService::find($id_transaction);

            if (! $this->check_access($detail)) {
                Session::flash('message_error', SERVICE_ERROR_MESSAGE);
                return redirect()->back();
            }

            $data['detail']      = $detail;
            $service_agency      = AgencyUnit::find($detail->id_agency_unit_service);
            $data['users']       = SecUser::get_by_id_agency_parent($service_agency->id_agency_unit_parent);
            $data['breadcrumps'] = ['Member Area', $data['title']];
            $data['docs']        = $detail->required_docs();
            $data['infos']       = $detail->required_infos();
            $data['workflows']   = $detail->service_workflows();

            foreach ($data['docs'] as $docs) {
                if (strpos($docs->document_path, 'assets') !== false) {
                    $docs->document_path = asset($docs->document_path);
                } else {
                    if (\Storage::disk('public')->exists('files/' . $docs->document_path)) {
                        $pathcheck = public_path($docs->document_path);
                        $isExists  = file_exists($pathcheck);
                        if ($isExists) {
                            $docs->document_path = asset($docs->document_path);
                        } else {
                            $path                = route('myservices.download_file', [$docs->document_path]);
                            $docs->document_path = $path;
                        }
                    } else {
                        $docs->document_path = asset($docs->document_path);
                    }
                }
            }

            GeneralHelper::add_log(['description' => "View Service " . $detail->transaction_code, 'id_user' => \Auth::user()->id_user]);

            return view('member.service.view', $data);
        } catch (\Exception $e) {
            return redirect()->back();
        }
    }

    public function edit($id_transaction)
    {
        try {
            $data['title'] = 'My Service Detail';
            $detail        = TrService::find($id_transaction);

            if (! $this->check_access($detail)) {
                Session::flash('message_error', SERVICE_ERROR_MESSAGE);
                return redirect()->back();
            }
            $data['detail']      = $detail;
            $service_agency      = AgencyUnit::find($detail->id_agency_unit_service);
            $data['users']       = SecUser::get_by_id_agency_parent2();
            $data['breadcrumps'] = ['Member Area', $data['title']];
            $data['docs']        = $detail->required_docs();
            $data['infos']       = $detail->required_infos();
            $data['workflows']   = $detail->service_workflows();
            GeneralHelper::add_log(['description' => "Open Edit Service " . $detail->transaction_code, 'id_user' => \Auth::user()->id_user]);

            return view('member.service.edit', $data);
        } catch (\Exception $e) {
            return redirect()->back();
        }
    }

    public function view_with_response_action($id_transaction)
    {
        try {
            $data['title'] = 'My Service Detail - Need Action';
            $detail        = TrService::find($id_transaction);

            if (! $this->check_access($detail) && session('user_role_id') != 3) {
                Session::flash('message_error', SERVICE_ERROR_MESSAGE);
                return redirect()->back();
            }

            $data['detail'] = $detail;

            $service_agency      = AgencyUnit::find($detail->id_agency_unit_service);
            $data['users']       = SecUser::get_by_id_agency_parent($service_agency->id_agency_unit_parent);
            $data['breadcrumps'] = ['Member Area', $data['title']];
            $data['docs']        = $detail->required_docs();
            $data['infos']       = $detail->required_infos();
            $data['workflows']   = $detail->service_workflows();
            GeneralHelper::add_log(['description' => "View Service - Need Action " . $detail->transaction_code, 'id_user' => \Auth::user()->id_user]);
            $parent                    = TrService::parent_service($detail->id_service);
            $service_list              = ServiceList::service_req($parent);
            $service_info_list         = ServiceList::service_info_req($parent);
            $data['service_lists']     = $service_list;
            $data['service_info_list'] = $service_info_list;
            // dd($data['service_info_list']);
            foreach ($data['workflows'] as $workflow) {
                $current = (! is_null($workflow->date_start_actual) && is_null($workflow->date_end_actual));
                if ($current) {
                    if ($workflow->id_user_pic_primary == \Auth::user()->id_user || $workflow->id_user_pic_alternate == \Auth::user()->id_user) {
                        return view('member.service.view_response_action', $data);
                    } elseif (session('user_role_id') == 3) {
                        return view('member.service.view_response_action', $data);
                    } else {
                        return redirect()->back();
                    }
                }
            }

        } catch (\Exception $e) {
            return redirect()->back();
        }
    }
    public function delete_doc(Request $req)
    {

        $TrServiceWorkFlowDoc = TrServiceWorkFlowDoc::find($req->id_transaction_workflow_doc);
        if ($TrServiceWorkFlowDoc) {
            $TrServiceWorkFlowDoc->delete();
        }

        return back();

    }

    public function delete_temporary_doc(Request $req)
    {

        $TrServiceWorkFlowDoc = TrServiceWorkFlowDoc::find($req->id_transaction_workflow_doc);
        if ($TrServiceWorkFlowDoc) {
            $TrServiceWorkFlowDoc->document_path = '';
            $TrServiceWorkFlowDoc->save();
        }

        return back();

    }

    public function delete_temporary_doc_draft(Request $req)
    {
        $TrServiceWorkFlowDoc = DrServiceDoc::find($req->id_transaction_workflow_doc);
        if ($TrServiceWorkFlowDoc) {
            $TrServiceWorkFlowDoc->delete();
        }
        return back();
    }

    public function delete_temporary_doc_draft_coa_other(Request $req)
    {
        $TrServiceCoaOtherDoc = DB::table('dr_service_coa_other')
            ->where('id_transaction_coa_other', $req->id_workflow_doc_coa_other)->first();
        if ($TrServiceCoaOtherDoc) {
            $TrServiceCoaOtherDoc = DB::table('dr_service_coa_other')
                ->where('id_transaction_coa_other', $req->id_workflow_doc_coa_other)->update(['file_path' => null]);
        }
        return back();
    }

    public function upload_final_doc(Request $req)
    {

        $docs = $req->required_docs;
        foreach ($docs as $key => $file) {
            $TrServiceWorkFlowDoc = TrServiceWorkFlowDoc::find($key);
            $path                 = 'assets/workflow_docs/' . date('Y') . '/' . date('m') . '/';
            $original_name        = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME), '-');
            $file_name            = time() . '_' . $original_name . '.' . $file->getClientOriginalExtension();
            $file->move(public_path($path), $file_name);
            $TrServiceWorkFlowDoc->document_path = $path . $file_name;
            $TrServiceWorkFlowDoc->document_type = $file->getClientOriginalExtension();
            $TrServiceWorkFlowDoc->save();

        }

        return 1;

    }

    public function delete_info(Request $req)
    {

        $TrServiceWorkFlowInfo = TrServiceWorkFlowInfo::find($req->id_transaction_workflow_info);
        if ($TrServiceWorkFlowInfo) {
            $TrServiceWorkFlowInfo->delete();
        }

        return back();

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

            foreach ($infos as $infoo => $value) {
                if ($value != null) {
                    $info = WorkFlowInfo::where('id_service_workflow_info', $infoo)->first();

                    $tr_workflow_info["id_transaction_workflow"] = $workflow_parent_ids;

                    $tr_workflow_info['id_service_workflow_info'] = $info->id_service_workflow_info;
                    $where                                        = $tr_workflow_info;
                    $tr_workflow_info["info_title"]               = $info->info_title;
                    $tr_workflow_info["info_value"]               = $value;
                    $tr_workflow_info["info_type"]                = $info->control_type;
                    $tr_workflow_info["description"]              = $info->description;
                    $tr_workflow_info["date_created"]             = Date('Y-m-d H:i:s');
                    $tr_workflow_info["created_by"]               = \Auth::user()->user_name;
                    $tr_workflow_info["is_mandatory"]             = $info->is_mandatory == 1 ? 1 : 0;

                    DB::table($this->table . '_workflow_info')->insert($tr_workflow_info);

                    unset($where);
                    unset($tr_workflow_info);
                }
            }
        } catch (\Exception $e) {
            throw new \Exception("store_workflow_info: " . $e->getMessage());
        }
    }

    public function rework_workflow(Request $req)
    {
        if ($req->type == 'rework') {
            $this->rework($req);
        } elseif ($req->type == 'goback') {
            $this->goback($req);
        }
        return route('myservices.index');
    }

    public function finish_service(Request $req)
    {
        $trarray_finish = [];
        try {
            DB::beginTransaction();
            $tr_service    = TrService::find($req->id_transaction);
            $child_ids     = TrService::where('id_transaction_parent', $tr_service->id_transaction)->get()->pluck('id_transaction')->toArray();
            $workflowcheck = TrServiceWorkFlow::whereIn('id_transaction', $child_ids)->get();
            $ticket_no     = $tr_service->transaction_code;
            foreach ($workflowcheck as $workflow) {
                if ($workflow->date_start_actual != null && $workflow->date_end_actual != null) {
                    if (! in_array($workflow->id_transaction, $trarray_finish)) {
                        array_push($trarray_finish, $workflow->id_transaction);
                    }
                } else {
                    if (in_array($workflow->id_transaction, $trarray_finish)) {
                        $index = array_search($workflow->id_transaction, $trarray_finish);
                        unset($trarray_finish[$index]);
                    }
                }
            }
            $price = 0;
            foreach ($trarray_finish as $id_transaction) {
                $tr_service_child = TrService::find($id_transaction);
                $price            = $price + $tr_service_child->service_price;
            }

            array_push($trarray_finish, $tr_service->id_transaction);
            // dd($trarray_finish);

            // dd('masuk');
            $data_final['id_status']     = 5;
            $data_final['is_finished']   = 1;
            $data_final['date_finished'] = Date('Y-m-d H:i:s');
            DB::table('tr_service')->whereIn('id_transaction', $trarray_finish)->update($data_final);

            GeneralHelper::add_log(['description' => "Confirm Service " . $ticket_no, 'id_user' => \Auth::user()->id_user]);
            $tr_service->service_price = $price;
            $tr_service->save();
            DB::commit();

            $TrServiceDelete = TrService::where('id_transaction_parent', $tr_service->id_transaction)
                ->whereNull('is_finished')
                ->get();
            $TrServiceDeletearr = $TrServiceDelete->pluck('id_transaction')->toArray();
            $workflowdelete     = TrServiceWorkFlow::whereIn('id_transaction', $TrServiceDeletearr)->get();

            $workflowdelete->each->delete();
            $TrServiceDelete->each->delete();

            Session::flash('message_success', "Transaction " . $ticket_no . " has been Finish");
            $dataTr['ticket']            = $tr_service->transaction_code;
            $dataTr['description']       = $tr_service->description;
            $dataTr['person_name_buyer'] = $tr_service->person_name_buyer;
            $dataTr['confirmby']         = \Auth::user()->person_name;
            $emailto                     = SecUser::find($tr_service->id_user_buyer);
            $dataTr['type']              = 'completed';
            Mail::to($emailto->email)->send(new SendMail($dataTr));
            return redirect()->route('myservices.index');
        } catch (\Exception $e) {
            DB::rollback();
            Session::flash('message_error', 'Warning ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function confirm_service(Request $req)
    {
        $id = $req->id_transaction;

        try {
            DB::beginTransaction();
            $current_id_transaction_workflow = $req->id_transaction_workflow;
            // dd($current_id_transaction_workflow);
            $transaction_workflow = TrServiceWorkFlow::find($current_id_transaction_workflow);
            $current_workflow     = $transaction_workflow;
            $current_seq          = $transaction_workflow->sequence;
            $infos                = $req->infos;
            $tr_service           = TrService::find($id);
            $ticket_no            = $tr_service->transaction_code;
            // dd($infos);
            // if (is_array($infos) && count($infos) > 0){
            //   foreach ($infos as $key => $value) {
            //     DB::table('tr_service_workflow_info')->where('id_transaction_workflow_info', $key)->update(['value' => $value]);
            //   }
            // }
            // $this->update_doc($current_workflow, $this->input->post());

            $transaction_workflow->date_end_actual      = Date('Y-m-d H:i:s');
            $transaction_workflow->completed_by         = session('user_name');
            $transaction_workflow->delay_reason         = '';
            $transaction_workflow->date_start_estimated = Date('Y-m-d H:i:s');
            $transaction_workflow->date_end_estimated   = GeneralHelper::workday_add(Date('Y-m-d H:i:s'), $transaction_workflow->workflow_day);
            $transaction_workflow->save();
            $next_seq = $current_seq + 1;

            $final         = true;
            $start_date    = Date('Y-m-d H:i:s');
            $child_ids     = TrService::where('id_transaction_parent', $transaction_workflow->tr_service->id_transaction_parent)->get()->pluck('id_transaction')->toArray();
            $next_workflow = TrServiceWorkFlow::whereIn('id_transaction', $child_ids)->where('sequence', $next_seq)->first();

            // dd('keluar');
            if (! is_null($next_workflow)) {
                $current_workflow                    = $next_workflow;
                $next_workflow                       = TrServiceWorkFlow::find($next_workflow->id_transaction_workflow);
                $next_workflow->date_start_actual    = Date('Y-m-d H:i:s');
                $next_workflow->date_start_estimated = Date('Y-m-d H:i:s');
                $next_workflow->date_end_estimated   = GeneralHelper::workday_add(Date('Y-m-d H:i:s'), $next_workflow->workflow_day);
                $next_workflow->save();
                $final = false;
            }

            // $id_wf = $wf["id_transaction_workflow"];
            // $final = false;
            // $curr_workflow = $wf["workflow_name"];
            // $pic = $wf["person_name_primary"];

            $tr_service      = TrService::find($transaction_workflow->id_transaction)->parent;
            $ticket_no       = $tr_service->transaction_code;
            $requester_name  = $tr_service->person_name_buyer;
            $requester       = $tr_service->user_name_buyer;
            $desc            = $tr_service->description;
            $all_notif_email = $tr_service->all_notif_email;

            if ($final) {
                array_push($child_ids, $tr_service->id_transaction);
                $data_final['id_status']     = 5;
                $data_final['is_finished']   = 1;
                $data_final['date_finished'] = Date('Y-m-d H:i:s');
                DB::table('tr_service')->whereIn('id_transaction', $child_ids)->update($data_final);

                $final_workflow                   = TrServiceWorkFlow::find($transaction_workflow->id_transaction_workflow);
                $final_workflow->is_start_billing = 1;
                $final_workflow->save();
                // $result["sequence"] = $current_seq;
                // $to[] = $requester;
                // $this->email->send_mail_completed($to, $ticket_no, $requester_name, $desc, base_url(""));
            } elseif ($all_notif_email == '1') {
                // $to[] = $requester;
                // $this->email->send_notification($to, $ticket_no, $requester_name, $desc, $curr_workflow, $pic, base_url(""));
            }

            $this->store_workflow_info($infos, $current_id_transaction_workflow);

            if ($req->required_docs != null) {
                $docs = $req->required_docs;

                foreach ($docs as $key => $file) {
                    $workflow_doc                      = WorkFlowDoc::find($key);
                    $tr_doc['id_transaction_workflow'] = $current_id_transaction_workflow;
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
                        $tr_doc_id    = $tr_doc_db->id_transaction_workflow_doc;
                        $tr_doc_ready = TrServiceWorkFlowDoc::where('id_transaction_workflow_doc', $tr_doc_id);
                        $tr_doc_ready->restore();
                    }

                    if (! is_null($file) && ! is_null($tr_doc_id)) {

                        $path = 'assets/workflow_docs/' . date('Y') . '/' . date('m') . '/';

                        $original_name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME), '-');

                        $file_name = time() . '_' . $original_name . '.' . $file->getClientOriginalExtension();

                        $file->move(public_path($path), $file_name);
                        $tr_doc_upd = TrServiceWorkFlowDoc::find($tr_doc_id);

                        $tr_doc_upd->document_path = $path . $file_name;

                        $tr_doc_upd->document_type = $file->getClientOriginalExtension();

                        $tr_doc_upd->save();

                    }

                    unset($where);
                    unset($tr_doc);
                }
            }

            GeneralHelper::add_log(['description' => "Confirm Service " . $ticket_no, 'id_user' => \Auth::user()->id_user]);
            DB::commit();
            Session::flash('message_success', "Transaction " . $ticket_no . " has been confirmed");

            $dataTr['ticket']            = $tr_service->transaction_code;
            $dataTr['description']       = $tr_service->description;
            $dataTr['person_name_buyer'] = $tr_service->person_name_buyer;
            $dataTr['confirmby']         = \Auth::user()->person_name;
            $emailto                     = SecUser::find($tr_service->id_user_buyer);

            $data['workflow_service'] = $tr_service->service_workflows();
            $count_workflow           = count($data['workflow_service']);
            $last_workflow            = $data['workflow_service'][$count_workflow - 1];
            foreach ($data['workflow_service'] as $workflow) {
                $current   = (! is_null($workflow->date_start_actual) && is_null($workflow->date_end_actual));
                $completed = (! is_null($last_workflow->date_start_actual) && ! is_null($last_workflow->date_end_actual));
                if ($current) {
                    $dataTr['type']            = 'confirm_to_nextflow_pic';
                    $emailtopic                = SecUser::find($workflow->id_user_pic_primary);
                    $dataTr['person_name_pic'] = $emailtopic->person_name;
                    $dataTr['workflowname']    = $workflow->workflow_name;
                    Mail::to($emailtopic->email)->send(new SendMail($dataTr));

                    $dataTr['type'] = 'confirm_to_nextflow';
                    Mail::to($emailto->email)->send(new SendMail($dataTr));

                    $emailtopicalternate = SecUser::find($workflow->id_user_pic_alternate);
                    if ($emailtopicalternate != null) {
                        $dataTr['type']            = 'confirm_to_nextflow_pic';
                        $dataTr['person_name_pic'] = $emailtopicalternate->person_name;
                        Mail::to($emailtopicalternate->email)->send(new SendMail($dataTr));
                    }

                    return redirect()->route('myservices.index');
                }
                if ($completed) {
                    $dataTr['type'] = 'completed';
                    Mail::to($emailto->email)->send(new SendMail($dataTr));
                    return redirect()->route('myservices.index');
                }
            }

        } catch (\Exception $e) {
            DB::rollback();
            Session::flash('message_error', 'Warning ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function rate(Request $req)
    {
        try {
            $detail = TrService::find($req->id_transaction);

            if (! $this->check_access($detail)) {
                Session::flash('message_error', SERVICE_ERROR_MESSAGE);
                return redirect()->back();
            }

            $input["date_rating"]    = date('Y-m-d H:i:s');
            $input["service_rating"] = $req->rating;
            $input["rating_comment"] = $req->comment;
            DB::table('tr_service')->where('id_transaction', $detail->id_transaction)->update($input);

            GeneralHelper::add_log(['description' => "Rate " . $detail->transaction_code, 'id_user' => \Auth::user()->id_user]);
            Session::flash('message_success', "Transaction " . $detail->transaction_code . " has been rated");
            return redirect()->route('myrequests.ongoing');

        } catch (\Exception $e) {
            Session::flash('message_error', $e->getMessage());
            return redirect()->back();
        }
    }

    public function rework(Request $req)
    {
        try {
            DB::beginTransaction();
            $id_transaction_workflow                 = $req->id_transaction_workflow;
            $add_price                               = $req->add_price;
            $add_workday                             = $req->add_workday;
            $tr_service_workflow                     = TrServiceWorkFlow::find($id_transaction_workflow);
            $tr_service_workflow->workflow_day       = $tr_service_workflow->workflow_day + $add_workday;
            $tr_service_workflow->date_end_estimated = GeneralHelper::workday_add($tr_service_workflow->date_start_estimated, $tr_service_workflow->workflow_day);
            $date_end_estimated                      = $tr_service_workflow->date_end_estimated;
            $tr_service_workflow->save();

            $tr_service                = TrService::find($tr_service_workflow->tr_service->id_transaction_parent);
            $tr_service->service_price = $tr_service->service_price + $add_price;
            $tr_service->save();

            $cmt['comment']                 = $req->rework_comment;
            $cmt['type']                    = 'rework';
            $cmt['id_transaction']          = $tr_service_workflow->id_transaction;
            $cmt['id_transaction_workflow'] = $id_transaction_workflow;
            $cmt['date_created']            = Date('Y-m-d H:i:s');
            $cmt['created_by']              = session('user_name');
            DB::table('tr_comment')->insert($cmt);

            // send email

            if ($req->cb_rework == '1') { // mail
                $to        = $tr_service->user_name_buyer;
                $requester = $tr_service->person_name_buyer;
                $ticket    = $tr_service->transaction_code;
                // @EmailHelper::send_mail_rework($to, $requester, $ticket, $req->rework_comment);
            }

            GeneralHelper::add_log(['description' => "Rework Service " . $tr_service->transaction_code, 'id_user' => \Auth::user()->id_user]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            $error = 'Rework Error: ' . $e->getMessage();
            throw new \Exception($error);
        }

    }

    public function reject(Request $req)
    {
        $id_transaction = $req->id_transaction;

        try {
            $update["is_finished"]   = 1;
            $update["id_status"]     = 3;
            $update["date_finished"] = date(DATE_TIME);
            $update["comment"]       = $req->comment;

            DB::table($this->table)->where('id_transaction', $id_transaction)->update($update);

            $tr_service                  = TrService::find($id_transaction);
            $dataTr['type']              = 'reject';
            $dataTr['ticket']            = $tr_service->transaction_code;
            $dataTr['comment']           = $req->comment;
            $dataTr['person_name_buyer'] = $tr_service->person_name_buyer;
            $dataTr['rejectby']          = \Auth::user()->person_name;
            $emailto                     = SecUser::find($tr_service->id_user_buyer);
            Mail::to($emailto->email)->send(new SendMail($dataTr));

            // @EmailHelper::send_mail_reject($tr_service->user_name_buyer, $tr_service->person_name_buyer, $tr_service->transaction_code, $req->comment);

            Session::flash('message', 'Transaction ' . $tr_service->transaction_code . ' has been rejected!');
            Session::flash('message_type', 'success');
            GeneralHelper::add_log(['description' => "Reject Service " . $id_transaction, 'id_user' => \Auth::user()->id_user]);
        } catch (\Exception $e) {
            GeneralHelper::add_log([
                'type'        => 'error',
                'description' => $e->getMessage(),
                'id_user'     => \Auth::user()->id_user]);

            Session::flash('message', $e->getMessage());
            Session::flash('message_type', 'error');
        }
        return redirect()->back();
    }

    public function update_service(Request $req)
    {
        $id_transaction = $req->id_transaction;

        try {
            DB::beginTransaction();

            $update = [];
            if (! empty($req->description)) {
                $update["description"] = $req->description;
            }

            if (! is_null($req->is_free_of_charge)) {
                $update["is_free_of_charge"] = $req->is_free_of_charge;
            }

            if ($req->cancel == 1) {
                $update["is_finished"]   = 1;
                $update["id_status"]     = 6;
                $update["date_finished"] = DATE('Y-m-d H:i:s');
            }

            $update['date_updated'] = date(DATE_TIME);
            DB::table($this->table)->where('id_transaction', $id_transaction)->update($update);

            $primary_pics = $req->primary_pics;

            if (! is_null($primary_pics) && count($primary_pics) > 0) {

                $alternate_pics = $req->alternate_pics;

                foreach ($primary_pics as $tr_workflow_id => $pic_id) {
                    $update_w = [];

                    $row = TrServiceWorkFlow::find($tr_workflow_id);

                    if ($row->sequence == 1) {
                        $row->date_end_actual = date(DATE_TIME);
                    } elseif ($row->sequence == 2) {
                        $row->date_start_actual = date(DATE_TIME);
                    }

                    $row->id_user_pic_primary   = $pic_id;
                    $row->id_user_pic_alternate = $alternate_pics[$tr_workflow_id];
                    $row->save();
                }
            }

            // EmailHelper::send_mail_confirmation();

            DB::commit();
            Session::flash('message_success', 'Service has been updated!');
            Session::flash('message_type', 'success');
            return redirect()->route('myservices.index');

        } catch (\Exception $e) {
            echo $e->getMessage();exit;
            DB::rollback();
            GeneralHelper::add_log([
                'type'        => 'error',
                'description' => $e->getMessage(),
                'id_user'     => \Auth::user()->id_user]);

            Session::flash('message_error', $e->getMessage());
            Session::flash('message_type', 'error');
            return redirect()->back();
        }
    }

    public function return (Request $req)
    {
        $id_transaction = $req->id_transaction;

        try {
            DB::beginTransaction();
            $update['comment']         = $req->comment;
            $update['id_status']       = -1;
            $update['date_authorized'] = null;
            $update['authorized_by']   = null;

            DB::table($this->table)->where('id_transaction', $id_transaction)->update($update);
            $tr_service = TrService::find($id_transaction);

            $dataTr['type']              = 'return';
            $dataTr['ticket']            = $tr_service->transaction_code;
            $dataTr['comment']           = $req->comment;
            $dataTr['person_name_buyer'] = $tr_service->person_name_buyer;
            $dataTr['returnby']          = \Auth::user()->person_name;
            $emailto                     = SecUser::find($tr_service->id_user_buyer);
            Mail::to($emailto->email)->send(new SendMail($dataTr));
            // @EmailHelper::send_mail_return($tr_service->user_name_buyer, $tr_service->person_name_buyer, $tr_service->transaction_code, $req->comment);

            Session::flash('message_success', 'Transaction ' . $tr_service->transaction_code . ' has been returned!');

            $workflows = TrService::find($id_transaction)->service_workflows();
            foreach ($workflows as $wf) {
                $update_w["date_start_actual"] = null;
                $update_w["date_end_actual"]   = null;
                DB::table('tr_service_workflow')->where('id_transaction_workflow', $wf->id_transaction_workflow)->update($update_w);
            }

            DB::commit();
        } catch (\Exception $e) {
            Session::flash('message_error', $e->getMessage());
            DB::rollback();
            GeneralHelper::add_log([
                'type'        => 'error',
                'description' => $e->getMessage(),
                'id_user'     => \Auth::user()->id_user]);
        }
        return redirect()->route('myservices.index');
    }

    public function goback(Request $req)
    {
        try {
            DB::beginTransaction();
            $tr_service_workflow            = TrServiceWorkFlow::find($req->id_transaction_workflow);
            $cmt['comment']                 = $req->goback_comment;
            $cmt['type']                    = 'goback';
            $cmt['id_transaction']          = $tr_service_workflow->id_transaction;
            $cmt['id_transaction_workflow'] = $req->id_transaction_workflow;
            $cmt['date_created']            = Date('Y-m-d H:i:s');
            $cmt['created_by']              = session('user_name');
            DB::table('tr_comment')->insert($cmt);
            $tr_service_workflow->date_start_actual = null;
            $tr_service_workflow->save();
            $prev_sequence = $tr_service_workflow->sequence - 1;

            if ($prev_sequence >= 2) {
                TrServiceWorkFlow::join('tr_service', 'tr_service.id_transaction', '=', 'tr_service_workflow.id_transaction')
                    ->where(['tr_service.id_transaction_parent' => $tr_service_workflow->tr_service->id_transaction_parent, 'tr_service_workflow.sequence' => $prev_sequence])
                    ->update(['tr_service_workflow.date_end_actual' => null]);
            }

            $tr_service = $tr_service_workflow->tr_service->parent;

            if ($req->cb_goback == '1') {
                $to        = $tr_service->user_name_buyer;
                $requester = $tr_service->person_name_buyer;
                $ticket    = $tr_service->transaction_code;
                // @EmailHelper::send_mail_goback($to, $requester, $ticket, $req->goback_comment);
            }

            Session::flash('message_success', 'Transaction ' . $tr_service->transaction_code . ' has been returned to previous step');
            DB::commit();
        } catch (\Exception $e) {
            Session::flash('message_error', $e->getMessage());
            DB::rollback();
            GeneralHelper::add_log([
                'type'        => 'error',
                'description' => $e->getMessage(),
                'id_user'     => \Auth::user()->id_user]);
        }
        return redirect()->route('myservices.index');
    }

    public function assign_pic(Request $req)
    {
        $id_transaction = $req->id_transaction;
        try {
            DB::beginTransaction();
            $data['is_free_of_charge'] = $req->is_free_of_charge == "1" ? 1 : 0;
            $data['date_authorized']   = date(DATE_TIME);
            $data['authorized_by']     = session('user_name');
            $data['id_status']         = 2;

            DB::table('tr_service')->where('id_transaction', $id_transaction)->update($data);

            $primary_pics   = $req->primary_pics;
            $alternate_pics = $req->alternate_pics;
            $start_date     = date(DATE_TIME);

            foreach ($primary_pics as $tr_workflow_id => $pic_id) {
                $update_w             = [];
                $row                  = TrServiceWorkFlow::find($tr_workflow_id);
                $id_transaction_child = $row->id_transaction;

                // if ($row->sequence == 1){
                //   $row->date_end_actual = date(DATE_TIME);
                // }else

                if ($row->sequence == 2) {
                    $row->date_start_actual = date(DATE_TIME);
                }

                $end_date                   = WorkFlow::get_next_workday($start_date, $row->workflow_day);
                $row->date_start_estimated  = $start_date;
                $row->date_end_estimated    = $end_date;
                $row->id_user_pic_primary   = $pic_id;
                $row->id_user_pic_alternate = $alternate_pics[$tr_workflow_id];
                $row->save();
                $start_date = $end_date;

                // DB::table('tr_service_workflow')->where('id_transaction_workflow', $tr_workflow_id)->update($update_w);
            }

            //tidak masuk kondisi ini
            if (isset($id_transaction_child) && $id_transaction_child > 0) {
                // $seq1_workflow = TrServiceWorkFlow::where(['id_transaction' => $id_transaction_child, 'sequence' => 1])
                // ->update(['date_end_actual' => date(DATE_TIME)]);

                $child_ids = TrService::where('id_transaction_parent', $id_transaction)->get()->pluck('id_transaction')->toArray();
                DB::table("tr_service_workflow")->where('sequence', 1)->whereIn('id_transaction', $child_ids)->update(['date_end_actual' => date(DATE_TIME)]);
            }

            $tr_service = TrService::find($id_transaction_child)->parent;
            // $curr_workflow = TrServiceWorkFlow::whereRaw("id_transaction = ".$id_transaction_child.' AND date_start_actual IS NOT NULL AND date_end_actual IS NULL')->first();
            // $pic = $curr_workflow->primary_pic;

            // EmailHelper::send_mail_confirmation($tr_service->supervisor_mail, $tr_service->person_name_buyer, $tr_service->transaction_code, $tr_service->description, $pic->person_name, $data['is_free_of_charge'], $pic->email);
            $data['workflow_service'] = $tr_service->service_workflows();
            foreach ($data['workflow_service'] as $workflow) {
                $current = (! is_null($workflow->date_start_actual) && is_null($workflow->date_end_actual));
                if ($current) {
                    $pic                         = $workflow->primary_pic;
                    $dataTr['type']              = 'assign_pic';
                    $dataTr['ticket']            = $tr_service->transaction_code;
                    $dataTr['person_name_buyer'] = $tr_service->person_name_buyer;
                    $dataTr['confirmby']         = $pic->person_name;
                    $emailto                     = SecUser::find($tr_service->id_user_buyer);
                    Mail::to($emailto->email)->send(new SendMail($dataTr));
                    // Mail::to($emailto->email)->send(new SendMail($dataTr));
                    $emailtopic = SecUser::find($workflow->id_user_pic_primary);

                    $dataTr['type']            = 'confirm_to_nextflow_pic';
                    $dataTr['description']     = $tr_service->description;
                    $dataTr['person_name_pic'] = $emailtopic->person_name;
                    Mail::to($emailtopic->email)->send(new SendMail($dataTr));
                    $emailtopicalternate = SecUser::find($workflow->id_user_pic_alternate);
                    if ($emailtopicalternate != null) {
                        $dataTr['type']            = 'confirm_to_nextflow_pic';
                        $dataTr['person_name_pic'] = $emailtopicalternate->person_name;
                        Mail::to($emailtopicalternate->email)->send(new SendMail($dataTr));
                    }
                    Session::flash('message_success', 'Data has been assigned!');
                    Session::flash('message_type', 'success');
                    GeneralHelper::add_log(['description' => "Assign PIC Service " . $tr_service->transaction_code, 'id_user' => \Auth::user()->id_user]);

                    DB::commit();
                    return redirect()->route('myservices.index');
                }
            }
        } catch (\Exception $e) {
            DB::rollback();
            Session::flash('message_error', 'assign_pic: ' . $e->getMessage());
            Session::flash('message_type', 'danger');
            GeneralHelper::add_log([
                'type'        => 'error',
                'description' => $e->getMessage(),
                'id_user'     => \Auth::user()->id_user]);
            return redirect()->back();
        }
    }

    public function tracking()
    {
        $data['title']                = 'Service Tracking';
        $data['breadcrumps']          = ['Member Area', $data['title']];
        $data['trackings']            = null;
        $data['with_id_agency_buyer'] = 0;
        $data['print']                = false;
        return view('member.request.tracking', $data);
    }

    public function list_new(Request $req)
    {
        $my_agency = \Auth::user()->agency;

        if ($my_agency->agency_unit_code == 'MGMT') {}
        if (session('user_role_id') == 3) {
            $where = "id_status = 1";
        } else {
            $where = "id_status = 1 AND id_agency_unit_service IN (" . Session::get('user_agency_unit_id') . ")";
        }
        $start_date = $req->start_date;
        $end_date   = $req->end_date;

        if (! empty($start_date)) {
            if (! empty($end_date)) {
                $where .= " AND (DATE(date_transaction) BETWEEN '" . $start_date . "' AND '" . $end_date . "') ";
            } else {
                $where .= " AND DATE(date_transaction) = '" . $start_date . "'";
            }
        }

        return Datatables::of(
            TrService::select('id_transaction', 'transaction_code', 'service_name', 'id_status',
                'agency_name_buyer', 'date_transaction', 'description', 'person_name_buyer')
                ->whereRaw($where)
        )
            ->editColumn('date_transaction', function ($list) {return Date('d-m-Y', strtotime($list->date_transaction));})
            ->editColumn('service_name', function ($list) {return $list->service_name . ' - ' . $list->description;})
            ->editColumn('description', function ($list) {return $list->description;})
            ->editColumn('person_name_buyer', function ($list) {return $list->person_name_buyer;})
            ->editColumn('agency_name_buyer', function ($list) {return $list->agency_name_buyer;})
            ->editColumn('id_status', function ($list) {return 'New Request';})
            ->addColumn("action", function ($list) {
                if (session('user_role_id') != 3) {
                    return "<a href='" . route('myservices.show', ['id_transaction' => $list->id_transaction]) . "' onClick='showLoading()' class='btn btn-default' title='Request Detail'><i class='fa fa-edit'></i></a>";
                } else {
                    return "<a href='" . route('myservices.view', ['id_transaction' => $list->id_transaction]) . "' onClick='showLoading()' class='btn btn-sm btn-clean btn-icon btn-icon-md' title='Need Action'><i class='fa fa-eye'></i></a><a href='" . route('myservices.show', ['id_transaction' => $list->id_transaction]) . "' onClick='showLoading()' class='btn btn-default' title='Request Detail'><i class='fa fa-edit'></i></a>";
                }
            })
            ->make(true);
    }

    public function list_ongoing(Request $req)
    {
        $my_agency = \Auth::user()->agency;
        $user_id   = session('user_id');

        if ($my_agency->agency_unit_code == 'MGMT') {}

        $filters['tr_service.id_agency_unit_service'] = $my_agency->id_agency_unit;
        return Datatables::of(
            TrService::basic_mapping_data("
        tr_service.transaction_code, tr_service.id_transaction, tr_service.service_name, tr_service.description, tr_service.person_name_buyer,
        tr_service.agency_name_buyer,
        tsw.workflow_name, tsw.date_start_actual, tsw.date_end_estimated,
        status_name, service_category.agency_unit_name as service_category_name,
        fn_get_number_workday(date_end_estimated, now(), false) AS delay_duration,
        primary_pic.person_name as user_primary_name, alternate_pic.person_name as user_alternate_name")
                ->join("sec_user as primary_pic", "primary_pic.id_user", "=", "tsw.id_user_pic_primary")
                ->join("sec_user as alternate_pic", "alternate_pic.id_user", "=", "tsw.id_user_pic_alternate")
                ->where($filters)
                ->whereRaw("tr_service.id_status = 2 AND tsw.date_start_actual IS NOT NULL AND tsw.date_end_actual IS NULL")
        )
            ->editColumn('person_name_buyer', function ($list) {return $list->person_name_buyer . ' - ' . $list->agency_name_buyer;})
            ->editColumn('service_name', function ($list) {return $list->service_name . '-' . $list->description;})
            ->editColumn('user_primary_name', function ($list) {return $list->user_primary_name . ' / ' . $list->user_alternate_name;})
            ->editColumn('date_start_actual', function ($list) {return Date('d-m-Y', strtotime($list->date_start_actual));})
            ->editColumn('date_end_estimated', function ($list) {return Date('d-m-Y', strtotime($list->date_end_estimated));})
        // ->editColumn('id_status', function($list){ return 'New Request'; })
            ->addColumn("action", function ($list) {
                return "<a href='" . route('myservices.view', ['id_transaction' => $list->id_transaction]) . "' onClick='showLoading()' class='btn btn-sm btn-clean btn-icon btn-icon-md' title='Need Action'><i class='fa fa-eye'></i></a>
      <a href='" . route('myservices.edit', ['id_transaction' => $list->id_transaction]) . "' onClick='showLoading()' class='btn btn-sm btn-clean btn-icon btn-icon-md' title='Request Detail'><i class='fa fa-edit'></i></a>";
            })
            ->make(true);
    }

    public function list_ongoing_new(Request $req)
    {
        $where = "id_status = 2";
        if (session('user_role_id') == 3) {
            $where2 = "";
        } else {
            $where2 = "( id_agency_unit_service = " . session('user_agency_unit_id') . " OR id_user_pic_primary = " . session('user_id') . " OR id_user_pic_alternate = " . session('user_id') . ")";
        }
        $start_date = $req->start_date;
        $end_date   = $req->end_date;

        if (! empty($start_date)) {
            if (! empty($end_date)) {
                $where2 .= " AND (DATE(date_start_actual) BETWEEN '" . $start_date . "' AND '" . $end_date . "') ";
            } else {
                $where2 .= " AND DATE(date_start_actual) = '" . $start_date . "'";
            }
        }

        $list = RequestQuery::ongoing_home($where, $where2);

        return Datatables::of($list)
            ->editColumn('date_start_actual', function ($list) {
                return (! empty($list->date_start_actual)) ? date('d-m-Y', strtotime($list->date_start_actual)) : "";
            })
            ->editColumn('date_end_estimated', function ($list) {
                return (! empty($list->date_end_estimated)) ? date('d-m-Y', strtotime($list->date_end_estimated)) : "";
            })
            ->editColumn('delay', function ($list) {return $list->delay . ' day(s)';})
            ->editColumn('user_name_primary', function ($list) {return $list->user_name_primary . ' / ' . $list->user_name_alternate;})
            ->editColumn('service_name', function ($list) {return $list->service_name . ' - ' . $list->description;})
            ->addColumn("action", function ($list) {
                if (session('user_role_id') == 3) {
                    return "<a href='" . route('myservices.view_with_response_action', ['id_transaction' => $list->id_transaction]) . "' onClick='showLoading()' class='btn btn-sm btn-clean btn-icon btn-icon-md' title='Need Action'><i class='fa fa-eye'></i></a><a href='" . route('myservices.edit', ['id_transaction' => $list->id_transaction]) . "' onClick='showLoading()' class='btn btn-sm btn-clean btn-icon btn-icon-md' title='Request Detail'><i class='fa fa-edit'></i></a>";
                } else {
                    if (in_array(session('user_id'), [$list->id_user_pic_primary, $list->id_user_pic_alternate])) {
                        return "<a href='" . route('myservices.view_with_response_action', ['id_transaction' => $list->id_transaction]) . "' onClick='showLoading()' class='btn btn-sm btn-clean btn-icon btn-icon-md' title='Need Action'><i class='fa fa-eye'></i></a><a href='" . route('myservices.edit', ['id_transaction' => $list->id_transaction]) . "' onClick='showLoading()' class='btn btn-sm btn-clean btn-icon btn-icon-md' title='Request Detail'><i class='fa fa-edit'></i></a>";
                    } else {
                        return "<a href='" . route('myservices.view', ['id_transaction' => $list->id_transaction]) . "' onClick='showLoading()' class='btn btn-sm btn-clean btn-icon btn-icon-md' title='Need Action'><i class='fa fa-eye'></i></a>
          <a href='" . route('myservices.edit', ['id_transaction' => $list->id_transaction]) . "' onClick='showLoading()' class='btn btn-sm btn-clean btn-icon btn-icon-md' title='Request Detail'><i class='fa fa-edit'></i></a>";
                    }
                }
            })->make(true);
    }

    public function list_document(Request $req)
    {
        $where      = "id_status in (2,3,4,5)";
        $where2     = "";
        $start_date = $req->start_date;
        $end_date   = $req->end_date;

        if (! empty($start_date)) {
            if (! empty($end_date)) {
                $where2 .= "ts.date_created BETWEEN '" . $start_date . "' AND '" . $end_date . "' and";
            } else {
                $where2 .= "ts.date_created = '" . $start_date . "' and";
            }
        } else {
            $start_date = date('Y-m-d', strtotime('-5 years'));
            $end_date   = date('Y-m-d');
            $where2 .= "ts.date_created BETWEEN '" . $start_date . "' AND '" . $end_date . "' and";
        }

        $list = RequestQuery::document_list($where, $where2);

        return Datatables::of($list)
            ->editColumn('date_created', function ($list) {
                return (! empty($list->date_created)) ? date('Y-m-d', strtotime($list->date_created)) : "";
            })
            ->editColumn('service_name', function ($list) {
                $desc = $list->service_name . ' - ' . $list->description;
                return $desc;
            })
            ->addColumn("action", function ($list) {
                return '<a href="javascript:void(0);" class="btn btn-secondary" data-toggle="modal"
        data-target="#listModal" data-backdrop="static" data-keyboard="false" onclick="document_list(this.id)" id="' . $list->id_transaction . '"><i class="fa fa-eye"></i> List</a>';
            })->make(true);
    }

    public function detail_document(Request $req)
    {
        $trservice = TrService::find($req->id);
        $docs      = $trservice->required_docs();
        $arr       = [];
        foreach ($docs as $doc) {
            $downloadarr   = ['name_service' => [], 'id_service' => [], 'name_workflow' => [], 'id_workflow' => [], 'document_name' => [], 'document_url' => []];
            $trservicework = TrServiceWorkflow::find($doc->id_transaction_workflow);
            if (strpos($doc->document_path, 'assets') !== false) {
                $doc->document_path = asset($doc->document_path);
            } else {
                if (\Storage::disk('public')->exists('files/' . $doc->document_path)) {
                    $pathcheck = public_path($doc->document_path);
                    $isExists  = file_exists($pathcheck);
                    if ($isExists) {
                        $doc->document_path = asset($doc->document_path);
                    } else {
                        $path               = route('myservices.download_file', [$doc->document_path]);
                        $doc->document_path = $path;
                    }
                } else {
                    $doc->document_path = asset($doc->document_path);
                }
            }
            array_push($downloadarr['name_service'], $doc->document_name);
            array_push($downloadarr['id_service'], $doc->id_service_workflow_doc);
            array_push($downloadarr['name_workflow'], $trservicework->workflow_name);
            array_push($downloadarr['id_workflow'], $trservicework->id_transaction_workflow);
            array_push($downloadarr['document_name'], $doc->document_name);
            array_push($downloadarr['document_url'], \URL::to($doc->document_path));

            array_push($arr, $downloadarr);
        }

        return $arr;
    }

    public function list_tracking(Request $req)
    {
        $my_agency = \Auth::user()->agency;
        $user_id   = session('user_id');

        if ($my_agency->agency_unit_code == 'MGMT') {}
        config()->set('database.connections.mysql.strict', false);
        \DB::reconnect(); //important as the existing connection i
        if (session('user_role_id') == 3) {
            $where = "tr_service.id_status NOT IN (1,2)";
        } else {
            $where = "tr_service.id_agency_unit_service = " . $my_agency->id_agency_unit . " AND tr_service.id_status NOT IN (1,2)";
        }
        $start_date = $req->start_date;
        $end_date   = $req->end_date;

        if (! empty($start_date)) {
            if (! empty($end_date)) {
                $where .= " AND (DATE(tr_service.date_transaction) BETWEEN '" . $start_date . "' AND '" . $end_date . "') ";
            } else {
                $where .= " AND DATE(tr_service.date_transaction) = '" . $start_date . "'";
            }
        }

        return Datatables::of(
            TrService::select(DB::raw("DISTINCT tr_service.id_transaction, tr_service.transaction_code, tr_service.service_name, tr_service.description, tr_service.person_name_buyer, tr_service.agency_name_buyer, tr_service.service_price, tr_service.service_rating, tr_service.date_finished, tr_service.date_transaction, fn_get_number_workday(max_workflow.date_end_estimated, tr_service.date_finished, false) AS delay_duration, status_name,
        date_end_estimated"))
                ->join("ms_status", "ms_status.id_status", "=", "tr_service.id_status", "INNER")
                ->join(DB::raw("(SELECT g.id_transaction_parent, MAX(date_end_estimated) AS date_end_estimated
                  FROM (SELECT * FROM tr_service_workflow WHERE date_deleted IS NULL) wf
                  JOIN (SELECT * FROM tr_service WHERE transaction_code IS NULL AND date_deleted IS NULL) g ON g.id_transaction=wf.id_transaction
                  GROUP BY g.id_transaction_parent) as max_workflow"), "max_workflow.id_transaction_parent", "=", "tr_service.id_transaction")
                ->whereRaw($where)
        )
            ->editColumn('person_name_buyer', function ($list) {return $list->person_name_buyer . ' - ' . $list->agency_name_buyer;})
            ->editColumn('service_name', function ($list) {return $list->service_name . '-' . $list->description;})
            ->editColumn('date_transaction', function ($list) {return Date('d-m-Y', strtotime($list->date_transaction));})
            ->editColumn('date_finished', function ($list) {return Date('d-m-Y', strtotime($list->date_finished));})
            ->addColumn("action", function ($list) {
                return "<a href='" . route('myservices.view', ['id_transaction' => $list->id_transaction]) . "' onClick='showLoading()' class='btn btn-sm btn-clean btn-icon btn-icon-md' title='Need Action'><i class='fa fa-eye'></i></a>";
            })
            ->make(true);

        // config()->set('database.connections.mysql.strict', true);
        // \DB::reconnect(); //important as the existing connection i
    }

    public function restore_request_search(Request $req)
    {

        $start_date = $req->start_date;
        $end_date   = $req->end_date;
        $where      = "date_deleted IS NOT NULL";
        if (! empty($start_date)) {
            if (! empty($end_date)) {
                $where .= " AND (DATE(tr_service.date_transaction) BETWEEN '" . $start_date . "' AND '" . $end_date . "') ";
            } else {
                $where .= " AND DATE(tr_service.date_transaction) = '" . $start_date . "'";
            }
        }

        if (! empty($req->status)) {
            $reject = null;
            if ($req->status == 2) {
                $reject = "OR id_status = -1";
            }
            $where .= " AND id_status = '" . $req->status . "' " . $reject;
        }

        if (! empty($req->id_service_unit)) {
            $where .= " AND id_agency_unit_service = '" . $req->id_service_unit . "'";
        }
        $list = RequestQuery::restore($where);

        return Datatables::of($list)
            ->editColumn('date_created', function ($list) {
                return (! empty($list->date_created)) ? date('d-m-Y', strtotime($list->date_created)) : "";
            })
            ->editColumn('service_name', function ($list) {
                $desc = $list->service_name . ' - ' . $list->description;
                return $desc;
            })
            ->addColumn('action', function ($list) {
                $action = "";
                $action .= "<a href='" . route('myservices.restore', [$list->id_transaction]) . "' title='Edit'><i class='fa fa-check'></i> </a>";
                return $action;
            })
            ->make(true);
    }

    public function restore_transaction(Request $req)
    {
        try {
            DB::beginTransaction();
            $id = $req->id_transaction;
            // $query = "SELECT ts.* FROM tr_service ts WHERE ts.date_deleted IS NOT NULL AND ts.id_transaction = $id;";

            // $TrServiceParent = DB::select($query);
            $TrServiceParent = TrService::withTrashed()
                ->where('id_transaction', $id)
                ->first();

            $TrServiceRaw = TrService::withTrashed()
                ->where('id_transaction_parent', $id);

            $TrService = $TrServiceRaw
                ->get();

            $TrServiceId = $TrServiceRaw
                ->pluck('id_transaction')
                ->toArray();

            $TrServiceWorkFlow = TrServiceWorkFlow::withTrashed()
                ->with('docs', 'infos')
                ->whereIn('id_transaction', $TrServiceId)
                ->orderBy('date_start_actual', 'DESC')
                ->get();

            if ($TrServiceParent->id_status != 5) {
                //workflow
                foreach ($TrServiceWorkFlow as $workflow) {
                    //infos
                    $TrServiceWorkFlowInfo = TrServiceWorkFlowInfo::withTrashed()->where('id_transaction_workflow', $workflow->id_transaction_workflow)->get();
                    foreach ($TrServiceWorkFlowInfo as $info) {
                        if ($info) {
                            $info->deleted_by   = null;
                            $info->date_deleted = null;
                            $info->save();
                        }
                    }
                    //docs
                    $TrServiceWorkFlowDoc = TrServiceWorkFlowDoc::withTrashed()->where('id_transaction_workflow', $workflow->id_transaction_workflow)->get();
                    foreach ($TrServiceWorkFlowDoc as $doc) {
                        if ($doc) {
                            $doc->deleted_by   = null;
                            $doc->date_deleted = null;
                            $doc->save();
                        }
                    }
                    $workflow->deleted_by   = null;
                    $workflow->date_deleted = null;
                    $workflow->save();
                }

                //child
                foreach ($TrService as $service) {
                    $service->deleted_by   = null;
                    $service->date_deleted = null;
                    $service->save();
                }
                //parent
                $TrServiceParent->id_status    = 1;
                $TrServiceParent->deleted_by   = null;
                $TrServiceParent->date_deleted = null;
                $TrServiceParent->save();
            } else {
                //workflow
                foreach ($TrServiceWorkFlow as $workflow) {
                    //infos
                    $TrServiceWorkFlowInfo = TrServiceWorkFlowInfo::withTrashed()->where('id_transaction_workflow', $workflow->id_transaction_workflow)->get();
                    foreach ($TrServiceWorkFlowInfo as $info) {
                        if ($info) {
                            $info->deleted_by   = null;
                            $info->date_deleted = null;
                            $info->save();
                        }
                    }
                    //docs
                    $TrServiceWorkFlowDoc = TrServiceWorkFlowDoc::withTrashed()->where('id_transaction_workflow', $workflow->id_transaction_workflow)->get();
                    foreach ($TrServiceWorkFlowDoc as $doc) {
                        if ($doc) {
                            $doc->deleted_by   = null;
                            $doc->date_deleted = null;
                            $doc->save();
                        }
                    }
                    $workflow->deleted_by   = null;
                    $workflow->date_deleted = null;
                    $workflow->save();
                }

                if (! is_null($TrServiceWorkFlow[0]->date_start_actual) && ! is_null($TrServiceWorkFlow[0]->date_end_actual)) {
                    $TrServiceWorkFlow[0]->date_end_actual = null;
                    $TrServiceWorkFlow[0]->save();
                }

                //child
                foreach ($TrService as $service) {
                    if ($service->id_status == 5) {
                        $service->id_status     = 2;
                        $service->is_finished   = 0;
                        $service->date_finished = null;
                    }
                    $service->deleted_by   = null;
                    $service->date_deleted = null;
                    $service->save();
                }
                //parent
                $TrServiceParent->id_status     = 2;
                $TrServiceParent->is_finished   = 0;
                $TrServiceParent->date_finished = null;
                $TrServiceParent->deleted_by    = null;
                $TrServiceParent->date_deleted  = null;
                $TrServiceParent->save();
            }

            GeneralHelper::add_log(['description' => "RESTORE Transaction ticket " . $TrServiceParent->transaction_code, 'id_user' => \Auth::user()->id_user]);
            Session::flash('message_success', 'Data has been restore');
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            GeneralHelper::add_log(['type' => 'error', 'description' => $e->getMessage(), 'id_user' => \Auth::user()->id_user]);
            Session::flash('message_error', $e->getMessage());
        }

        return redirect()->back();
    }
}
