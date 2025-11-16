<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SecUser;
use App\AgencyUnit;
use App\ServiceList;
use App\GeneralHelper;
use App\Coa;
use App\WorkFlowDoc;
use App\TrBilling;
use App\TrBillingDetail;
use App\TrService;
use DB, Session;
use Validator;
use Datatables;
use PDF;

date_default_timezone_set('Asia/Jakarta');
define('DATE_TIME', 'Y-m-d H:i:s');
define('SERVICE_ERROR_MESSAGE', "Sorry, You aren't allowed to see the Request!");
class MemberBillingController extends Controller
{
  protected $table = 'tr_billing';
  public function __construct(){
    $this->middleware(function ($request, $next){
      if (session('user_id') != null) {
        return $next($request);
      }else{
        return redirect()->route('login');
      }
    });
  }

  function check_access($detail = null){
    if (in_array(session('user_menu'), ['service_unit', 'finance'])) return true;
    $user_name = session('user_name');
    $user_id_agency = session('user_agency_unit_id');
    $is_valid = (($user_name == $detail->created_by) || ($user_id_agency == $detail->id_agency_unit_buyer));

    return $is_valid;
  }

  public function index(){
    $data['title'] = 'My Billing';
    $data['breadcrumps'] = ['Member Area', 'My Billing'];
    return view('member.billing.index', $data);
  }

  public function add(){
    $data['title'] = 'My Billing - Add';
    $data['agencies'] = TrBilling::agency_to_bill("");
    $data['breadcrumps'] = ['Member Area', 'My Billing - Add'];
    GeneralHelper::add_log(['description' => "Open Add Billing ", 'id_user' => \Auth::user()->id_user]);
    return view('member.billing.form', $data);
  }

  public function create(Request $req) {
    try{
      
      DB::beginTransaction();
      $ids = $req->ids;
      if($ids == null){
        DB::rollBack();
        GeneralHelper::add_log(['type' => 'error', 'description' => 'create billing Service Bill is empty', 'id_user' => \Auth::user()->id_user]);
        Session::flash('message_error', "Invoice can't be created : Service to Bill cannot be empty");
        return redirect()->back();
      }
      $prices = $req->prices;
      $description = $req->description;
      $unore = $req->unore;
      $current_date = date(DATE_TIME);
      $due_date = GeneralHelper::next_day(date(DATE_TIME), 30);
      $tr_service = TrService::find(reset($ids));
      // dd($req->all());
      $amount = 0;
      
      foreach($ids as $key => $id){
        $amount += $req->prices[$key];
      }

      $agency = AgencyUnit::find($req->id_agency_unit)->toArray();
    
      $data_billing["id_agency_unit_buyer"] = $agency["id_agency_unit"];
      $data_billing["agency_code"] = $agency["agency_unit_code"]; 
      $data_billing["agency_name"] = $agency["agency_unit_name"]; 
      $data_billing["description"] = $description; 
      $data_billing["unore"] = $unore; 
      $data_billing["id_currency"] = $tr_service->id_currency;
      $data_billing["currency_code"] = $tr_service->currency_code;
      $data_billing["currency_name"] = $tr_service->currency_name;
      $data_billing["amount_billing"] = $amount; 
      $data_billing["amount_billing_local"] = $amount * $unore; 
      $data_billing["date_due_payment"] = $due_date; 
      $data_billing["date_created"] = $current_date; 
      $data_billing["created_by"] = session('user_name'); 

      $id_billing = DB::table($this->table)->insertGetId($data_billing);
      
      foreach ($ids as $value) {
        $data_detail = [];
        $price = $prices[$value];
        $data_detail["id_billing"] = $id_billing;
        $data_detail["id_transaction"] = $value;
        $data_detail["amount_billing"] = $price;
        $data_detail["amount_billing_local"] = $price * $unore;
        $data_detail["date_due_payment"] = $due_date;
        DB::table($this->table.'_detail')->insert($data_detail); 
      }

      DB::commit();
      GeneralHelper::add_log(['description' => "Create Billing ".$id_billing, 'id_user' => \Auth::user()->id_user]);
      Session::flash('message_success', "Invoice has been created: ID $id_billing");
      return redirect()->route('mybillings.index');

    }catch(\Exception $e){
      DB::rollBack();
      GeneralHelper::add_log(['type' => 'error', 'description' => 'create billing '.$e->getMessage(), 'id_user' => \Auth::user()->id_user]);
      Session::flash('message_error', "Invoice can't be created : ".$e->getMessage());
      return redirect()->back();
    }
  }

  public function show($id){
    $data['title'] = 'My Billing';
    $data['detail'] = TrBilling::find($id);
    $data['print'] = false;

    if (!$this->check_access($data['detail'])) {
      Session::flash('message_error', SERVICE_ERROR_MESSAGE);
      return redirect()->back();
    }

    $data['details'] = TrBilling::custom_detail_by_id($id);
    $data['breadcrumps'] = ['Member Area', 'My Billing Detail'];
    GeneralHelper::add_log(['description' => "Show Billing ".$id, 'id_user' => \Auth::user()->id_user]);

    return view('member.billing.detail', $data);
  }

  public function print($id){
    $data['detail'] = TrBilling::find($id);
    $data['print'] = true;

    if (!$this->check_access($data['detail'])) {
      Session::flash('message_error', SERVICE_ERROR_MESSAGE);
      return redirect()->back();
    }

    $data['details'] = TrBilling::custom_detail_by_id1($id);
    GeneralHelper::add_log(['description' => "Print Billing ".$id, 'id_user' => \Auth::user()->id_user]);


    $pdf = PDF::loadview('member.billing.detail_pdf', $data)->setPaper('a4', 'landscape');
    return $pdf->stream('billing-'.$id.'.pdf');
  }

  public function show_group($id){
    $data['title'] = 'My Billing Group';
    $data['billing'] = TrBilling::billing_detail_coa_by_id($id);
    if (!$this->check_access($data['billing'])) {
      Session::flash('message_error', SERVICE_ERROR_MESSAGE);
      return redirect()->back();
    }
    if (count($data['billing']) == 0){
      Session::flash('message_error', "No Billing Details Found!");
      return redirect()->back(); 
    }
    $data['breadcrumps'] = ['Member Area', 'My Billing Detail'];
    GeneralHelper::add_log(['description' => "Show Billing Group ".$id, 'id_user' => \Auth::user()->id_user]);
    return view('member.billing.detail_group', $data);
  }

  public function edit_group($id){
    $data['title'] = 'My Billing Group - Edit';
    $data['billing'] = TrBilling::custom_detail_by_id($id);
    $id_agency_buyer = $data['billing'][0]->id_agency_unit_buyer;
    $where = "p.id_agency_unit = ".$id_agency_buyer;
    $data["transaction"] = TrBilling::transaction_ready_to_bill($where);

    if (!$this->check_access($data['billing'])) {
      Session::flash('message_error', SERVICE_ERROR_MESSAGE);
      return redirect()->back();
    }
    $data['breadcrumps'] = ['Member Area', 'My Billing Detail - Edit'];
    GeneralHelper::add_log(['description' => "Edit Billing Group ".$id, 'id_user' => \Auth::user()->id_user]);

    return view('member.billing.edit_group', $data);
  }

  public function update_group(Request $req) {
    try{
      DB::beginTransaction();
      $id_billing = $req->id_billing;
      $ids = $req->ids;
      $prices = $req->prices;
      $description = $req->description;
      $unore = $req->unore;
      $current_date = date(DATE_TIME);
      $due_date = GeneralHelper::next_day(date(DATE_TIME), 30);
      $tr_service = TrService::find(reset($ids));
      
      $amount = 0;
      foreach($ids as $key => $id){
        $amount += $prices[$key];
      }
    
      $data_billing["description"] = $description; 
      $data_billing["unore"] = $unore; 
      $data_billing["id_currency"] = $tr_service->id_currency;
      $data_billing["currency_code"] = $tr_service->currency_code;
      $data_billing["currency_name"] = $tr_service->currency_name;
      $data_billing["amount_billing"] = $amount; 
      $data_billing["amount_billing_local"] = $amount * $unore; 
      $data_billing["date_due_payment"] = $due_date; 

      DB::table($this->table)->where('id_billing', $id_billing)->update($data_billing);
      DB::table($this->table.'_detail')->where('id_billing', $id_billing)->delete();

      foreach ($ids as $value) {
        $data_detail = [];
        $price = $prices[$value];
        $data_detail["id_billing"] = $id_billing;
        $data_detail["id_transaction"] = $value;
        $data_detail["amount_billing"] = $price;
        $data_detail["amount_billing_local"] = $price * $unore;
        $data_detail["date_due_payment"] = $due_date;
        DB::table($this->table.'_detail')->insert($data_detail); 
      }

      DB::commit();
      GeneralHelper::add_log(['description' => "Update Billing ".$req->id_billing, 'id_user' => \Auth::user()->id_user]);
      Session::flash('message_success', "Invoice has been updated");

    }catch(\Exception $e){
      DB::rollBack();
      GeneralHelper::add_log(['type' => 'error', 'description' => $e->getMessage(), 'id_user' => \Auth::user()->id_user]);
      Session::flash('message_error', "Invoice can't be updated : ".$e->getMessage());
    }
    return redirect()->back();
  }

  public function delete(Request $req) {
    try{
      $id = $req->id;
      $row = TrBilling::find($id);
      if (!$this->check_access($row)) {
        Session::flash('message_error', SERVICE_ERROR_MESSAGE);
        return response()->json(['message'=>'error'], 401);
      }
      // $billingDetails = TrBillingDetail::where('id_billing', $id)->get();
      // if ($billingDetails->isNotEmpty()) {
      //     foreach ($billingDetails as $billingDetail) {
      //         $billingDetail->date_deleted = Date('Y-m-d H:i:s');
      //         $billingDetail->deleted_by =  \Auth::user()->user_name; // Assuming you have an authenticated user
      //         $billingDetail->save();
      //     }
      //     // Success message or further processing
      // }
      $row->date_deleted = Date('Y-m-d H:i:s');
      $row->deleted_by = \Auth::user()->user_name;
      $row->save();

      GeneralHelper::add_log(['description' => "DELETE billing id ".$id, 'id_user' => \Auth::user()->id_user]);
      return response()->json(['message'=>'success']);

    }catch(\Exception $e){
      GeneralHelper::add_log(['type' => 'error', 'description' => 'delete invoice_no: '.$e->getMessage(), 'id_user' => \Auth::user()->id_user]);
      return response()->json(['message'=>'error'], 500);
    }
  }

  public function finalize($id) {
    try{
      $detail = TrBilling::find($id);
      if (!$this->check_access($detail)) {
        Session::flash('message_error', SERVICE_ERROR_MESSAGE);
        return redirect()->back();
      }

      $data["invoice_no"] = $invoice_no = str_pad($id, 5, "0",STR_PAD_LEFT)."/UNDP/".date("m/Y");
      $data["date_finalized"] = date(DATE_TIME);
      DB::table($this->table)->where('id_billing', $id)->update($data);

      GeneralHelper::add_log(['description' => "Finalize billing id ".$id, 'id_user' => \Auth::user()->id_user]);
      Session::flash('message_success', "Invoice has been finalized $invoice_no");
      return redirect()->route('mybillings.index');
    }catch(\Exception $e){
      GeneralHelper::add_log(['type' => 'error', ' description' => 'finalize: '.$e->getMessage(), 'id_user' => \Auth::user()->id_user]);
      Session::flash('message_error', "Invoice can't be finalized : ".$e->getMessage());
      return redirect()->back();
    }
  }

  public function pay(Request $req) {
    try{
      
      $detail = TrBilling::find($req->id_billing);
      if (!$this->check_access($detail)) {
        Session::flash('message_error', SERVICE_ERROR_MESSAGE);
        return redirect()->back();
      }

      $details = $req->date_payments;
      
      foreach ($details as $key => $value) {
        $billing_detail = TrBillingDetail::find($key);
        $billing_detail->is_paid = 1;
        $billing_detail->date_payment = $value;
        $billing_detail->save();

        $data = [];
        $data['is_paid'] = 1;
        $data['date_paid'] = $value;
        DB::table('tr_service')->where('id_transaction', $billing_detail->id_transaction)->update($data);
      }
      
      GeneralHelper::add_log(['description' => "Pay Billing ".$req->id_billing, 'id_user' => \Auth::user()->id_user]);
      Session::flash('message_success', "Invoice has been paid");
    }catch(\Exception $e){
      GeneralHelper::add_log(['type' => 'error', 'description' => $e->getMessage(), 'id_user' => \Auth::user()->id_user]);
      Session::flash('message_error', "Invoice can't be paid : ".$e->getMessage());
    }
    return redirect()->back();
  }

  public function glje(){
    $data['title'] = 'My Billing - GLJE';
    $data['breadcrumps'] = ['Member Area', $data['title']];
    $data['list'] = TrBilling::glje_list();
    return view('member.billing.glje_index', $data);
  }

  public function glje_add(){
    $data['title'] = 'My Billing - GLJE Add';
    $transaction_to_bills = TrBilling::glje_transaction_to_bill();
    $headers = [];
    $coas = [];
    foreach ($transaction_to_bills as $value) {
      $headers[$value->id_agency_unit] = $value->agency_unit_code;
      // $coa = DB::table('tr_service_coa_atlas')->where(['id_transaction'=>$value->id_transaction, 'is_service_fee' =>1])->get()->toArray();
      // $coas[$value->id_transaction] = json_encode($coa);
    }
    $data['coas'] = $coas;
    $data['glje'] = $transaction_to_bills;
    $data['headers'] = $headers;
    $data['breadcrumps'] = ['Member Area', $data['title']];
    return view('member.billing.glje_form', $data);
  }

  public function glje_edit($id){
    $detail = DB::table('tr_glje')->where('id_glje', $id)->first();
    $data['detail'] = $detail;
    $current_transaction_to_bills = TrBilling::glje_transaction_to_bill($id);
    $data['current_glje'] = $current_transaction_to_bills;
    $transaction_to_bills = TrBilling::glje_transaction_to_bill();
    $data['title'] = 'My Billing - GLJE Edit';
    $headers = [];
    $coas = [];
    foreach ($transaction_to_bills as $value) {
      $headers[$value->id_agency_unit] = $value->agency_unit_code;
      // $coa = DB::table('tr_service_coa_atlas')->where(['id_transaction'=>$value->id_transaction, 'is_service_fee' =>1])->get()->toArray();
      // $coas[$value->id_transaction] = json_encode($coa);
    }
    $data['coas'] = $coas;
    $data['glje'] = $transaction_to_bills;
    $data['headers'] = $headers;
    $data['breadcrumps'] = ['Member Area', $data['title']];
    return view('member.billing.glje_form', $data);
  }

  function insert_detail_gjle($id_glje, $id_transactions){
    try {
      $value_coa = 0;
      $glje_data['date_updated'] = DATE(DATE_TIME);
      $glje_data['updated_by'] = \Auth::user()->user_name;
      DB::table("tr_glje")->where('id_glje', $id_glje)->update($glje_data);

      foreach ($id_transactions as $id_transaction){
        $coa = DB::table('tr_service_coa_atlas')->where('id_transaction', $id_transaction)->get();
        $trans = TrService::find($id_transaction);
        $ticket = $trans->transaction_code;
        $service_price = $trans->service_price;
        $desc = $trans->description;

        if (!empty($coa)){
          foreach ($coa as $data_coa){
            $coa_value = round( $data_coa->percentage * $service_price / 100, 2);
            unset($data);
            // 70 %
            $data["id_glje"] = $id_glje;
            $data["id_transaction"] = $id_transaction;
            $data["ticket"] = $ticket . " - " . $desc;
            $data["ticket"] = substr($data["ticket"], 0, 25);
            $data["id_transaction_coa"] = $data_coa->id_transaction_coa;
            $data["acc"] = "64398";
            $data["opu"] = $data_coa->opu;
            $data["fund"] = $data_coa->fund;
            $data["dept"] = $data_coa->dept;
            $data["imp_agent"] = $data_coa->imp_agent;
            $data["donor"] = $data_coa->donor;
            $data["pcbu"] = $data_coa->pcbu;
            $data["project"] = $data_coa->project;
            $data["activities"] = $data_coa->activities;
            $data["percentage"] = $data_coa->percentage;
            $data["glje_percent"] = 70;
            $data["value"] = round($coa_value * $data["glje_percent"] / 100, 2);
            $val = $data["value"];
            $value_coa += $val;
            DB::table('tr_glje_detail')->insert($data);

            unset($data);
            // 30 %
            $data["id_glje"] = $id_glje;
            $data["id_transaction"] = $id_transaction;
            $data["ticket"] = $ticket . " - " . $desc;
            $data["ticket"] = substr($data["ticket"], 0, 25);
            $data["id_transaction_coa"] = $data_coa->id_transaction_coa;
            $data["acc"] = "74598";
            $data["opu"] = $data_coa->opu;
            $data["fund"] = $data_coa->fund;
            $data["dept"] = $data_coa->dept;
            $data["imp_agent"] = $data_coa->imp_agent;
            $data["donor"] = $data_coa->donor;
            $data["pcbu"] = $data_coa->pcbu;
            $data["project"] = $data_coa->project;
            $data["activities"] = $data_coa->activities;
            $data["percentage"] = $data_coa->percentage;
            $data["glje_percent"] = 30;
            $data["value"] = $coa_value - $val;                        
            $value_coa += $data["value"];
            DB::table('tr_glje_detail')->insert($data);
          }
        }

        if ($value_coa > 0) {
          // lawannya
          unset($data);
          // 70 %
          $data["id_glje"] = $id_glje;
          $data["id_transaction"] = $id_transaction;
          $data["ticket"] = "Cost Recovery 70%";
          $data["id_transaction_coa"] = 0;
          $data["acc"] = "64398";
          $data["opu"] = "IDN";
          $data["fund"] = "11300";
          $data["dept"] = "40801";
          $data["imp_agent"] = "001981";
          $data["donor"] = "00012";
          $data["pcbu"] = "IDN10";
          $data["project"] = "00048762";
          $data["activities"] = "OS";
          $data["percentage"] = 100;
          $data["glje_percent"] = -70;
          $data["value"] = round($data["glje_percent"] * $value_coa / 100, 2);
          $val = $data["value"];
          DB::table('tr_glje_detail')->insert($data);

          unset($data);
          // 30 %
          $data["id_glje"] = $id_glje;
          $data["id_transaction"] = $id_transaction;
          $data["ticket"] = "Cost Recovery 30%";
          $data["id_transaction_coa"] = 0;
          $data["acc"] = "74598";
          $data["opu"] = "IDN";
          $data["fund"] = "11300";
          $data["dept"] = "40801";
          $data["imp_agent"] = "001981";
          $data["donor"] = "00012";
          $data["pcbu"] = "IDN10";
          $data["project"] = "00048762";
          $data["activities"] = "OS";
          $data["percentage"] = 100;
          $data["glje_percent"] = -30;
          $data["value"] = -1 * ($value_coa + $val);
          DB::table('tr_glje_detail')->insert($data);
        }
      }
    }catch(\Exception $e){
      throw new Exception("insert_detail_gjle:".$e->getMessage());
    }
  }

  public function glje_create(Request $req){
    try{
      DB::beginTransaction();
      if (!$this->check_access(null)) {
        Session::flash('message_error', SERVICE_ERROR_MESSAGE);
        return redirect()->back();
      }

      $ids = $req->id_transactions;

      if (!empty($ids)){
        $glje_data['date_created'] = DATE(DATE_TIME);
        $glje_data['created_by'] = \Auth::user()->user_name;
        $id_glje = DB::table('tr_glje')->insertGetId($glje_data);
        $this->insert_detail_gjle($id_glje, $ids);
      }else{
        session::flash('message_error', "GLJE Transaction can't be empty");
        return redirect()->back();
      }
      
      GeneralHelper::add_log(['description' => "GLJE ADD ", 'id_user' => \Auth::user()->id_user]);
      Session::flash('message_success', "GLJE has been created");
      DB::commit();
      return redirect()->route('mybillings.glje_index');
    }catch(\Exception $e){
      DB::rollBack();
      GeneralHelper::add_log(['type' => 'error', ' description' => 'GLJE ADD: '.$e->getMessage(), 'id_user' => \Auth::user()->id_user]);
      Session::flash('message_error', "GLJE can't be created : ".$e->getMessage());
      return redirect()->back();
    }
  }

  public function glje_update(Request $req){
    try{
      DB::beginTransaction();
      if (!$this->check_access(null)) {
        Session::flash('message_error', SERVICE_ERROR_MESSAGE);
        return redirect()->back();
      }

      $ids = $req->id_transactions;

      if (!empty($ids)){
        $id_glje = $req->id_glje;
        DB::table('tr_glje_detail')->where('id_glje', $id_glje)->delete();
        $this->insert_detail_gjle($id_glje, $ids);
      }else{
        session::flash('message_error', "GLJE Transaction can't be empty");
        return redirect()->back();
      }
      
      GeneralHelper::add_log(['description' => "GLJE ADD ", 'id_user' => \Auth::user()->id_user]);
      Session::flash('message_success', "GLJE has been updated");
      DB::commit();
      return redirect()->route('mybillings.glje_index');
    }catch(\Exception $e){
      DB::rollBack();
      GeneralHelper::add_log(['type' => 'error', ' description' => 'GLJE Update: '.$e->getMessage(), 'id_user' => \Auth::user()->id_user]);
      Session::flash('message_error', "GLJE can't be updated : ".$e->getMessage());
      return redirect()->back();
    }
  }

  public function glje_delete($id){
    try{
      $detail = DB::table('tr_glje')->where('id_glje', $id)->first();
      if (!$this->check_access($detail)) {
        Session::flash('message_error', SERVICE_ERROR_MESSAGE);
        return redirect()->back();
      }

      DB::table('tr_glje_detail')->where('id_glje', $id)->delete();
      DB::table('tr_glje')->where('id_glje', $id)->delete();

      GeneralHelper::add_log(['description' => "GLJE DELETE ID ".$id, 'id_user' => \Auth::user()->id_user]);
      Session::flash('message_success', "GLJE has been deleted");
      return redirect()->route('mybillings.glje_index');
    }catch(\Exception $e){
      GeneralHelper::add_log(['type' => 'error', ' description' => 'GLJE DELETE: '.$e->getMessage(), 'id_user' => \Auth::user()->id_user]);
      Session::flash('message_error', "GLJE can't be deleted : ".$e->getMessage());
      return redirect()->back();
    }
  }

  public function glje_show($id){
    $data['title'] = 'My Billing - GLJE Show';
    $data['breadcrumps'] = ['Member Area', $data['title']];
    $glje = DB::table('tr_glje')->where('id_glje', $id)->first();
    $data['content'] = $this->glje_content_by_id($id);
    $data['detail'] = $glje;
    return view('member.billing.glje_show', $data);
  }

  public function glje_update_no(Request $req){
    try{
      $detail = DB::table('tr_glje')->where('id_glje', $req->id_glje)->first();
      if (!$this->check_access($detail)) {
        Session::flash('message_error', SERVICE_ERROR_MESSAGE);
        return redirect()->back();
      }

      DB::table('tr_glje')->where('id_glje', $req->id_glje)->update(['glje_no' => $req->glje_no]);
      GeneralHelper::add_log(['description' => "GLJE UPDATE ID ".$req->id_glje, 'id_user' => \Auth::user()->id_user]);
      Session::flash('message_success', "GLJE has been updated: ".$req->glje_no);
      return redirect()->route('mybillings.glje_index');
    }catch(\Exception $e){
      GeneralHelper::add_log(['type' => 'error', ' description' => 'GLJE DELETE: '.$e->getMessage(), 'id_user' => \Auth::user()->id_user]);
      Session::flash('message_error', "GLJE can't be updated : ".$e->getMessage());
      return redirect()->back();
    }
  }

  public function glje_download($id){
    $content = $this->glje_content_by_id($id);
    $data["date_downloaded"] = date(DATE_TIME);
    DB::table("tr_glje")->where('id_glje', $id)->update($data);
    // Redirect output to a client’s web browser (Excel5)
    //    header('Content-Type: text/plain');
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="glje.txt"');
    header('Cache-Control: max-age=0');
    // If you're serving to IE 9, then the following may be needed
    header('Cache-Control: max-age=1');

    // If you're serving to IE over SSL, then the following may be needed
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
    header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
    header('Pragma: public'); // HTTP/1.0
    header("Content-Length: " . strlen($content));
    echo $content;
  }

  function glje_content_by_id($id){
    $content  = "HEADER\tBUSINESS_UNIT\tJOURNAL_ID\tJOURNAL_DATE\tADJUSTING_ENTRY\tACCOUNTING_PERIOD\tADB_DATE\tLEDGER_GROUP\tLEDGER\tREVERSAL_CD\tREVERSAL_DATE\tReversal Adjusting Period\tREVERSAL_CD_ADB\tREVERSAL_DATE_ADB\tSOURCE\tTRANS_REF_NUM\tDESCRIPTION 30 Charcters Limit\tCURRENCY_CD\tRT_TYPE\tCUR_EFFDT\tRATE MULTI\tSYSTEM_SOURCE\tDOC_TYPE\tDOC_SEQ_NBR\r\n";            
    $content .= "H\tUNDP1\tNEXT\t{xxx}\tN\t{xxx}\t\tACTUALS\t\t\t\t\t\t\tEXT\t5\t{xxx}\tUSD\tUNORE\t\t1\tEXV\t\t\r\n";
    $content .= "LINE\tBUSINESS_UNIT\tJOURNAL_LINE\tLEDGER\tACCOUNT\tALTACCT\tDEPTID\tOPERATING_UNIT\tPRODUCT\tFUND_CODE\tCLASS_FLD\tPROGRAM_CODE\tBUDGET_REF\tAFFILIATE\tAFFILIATE_INTRA1\tAFFILIATE_INTRA2\tCHARTFIELD1\tCHARTFIELD2\tCHARTFIELD3\tPROJECT_ID\tBOOK_CODE\tBUDGET_PERIOD\tSCENARIO\tSTATISTICS_CODE\tMONETARY_AMOUNT\tMOVEMENT_FLAG\tSTATISTIC_AMOUNT\tJRNL_LN_REF\tLINE_DESCR\tCURRENCY_CD\tRT_TYPE\tFOREIGN_AMOUNT\tEXCHANGE_RT\tBUSINESS_UNIT_PC\tACTIVITY_ID\tANALYSIS_TYPE\tRESOURCE_TYPE\tRESOURCE_CATEGORY\tRESOURCE_SUB_CAT\tBUDGET_DT\tBUDGET_LINE_STATUS\tENTRY_EVENT\tIU_TRAN_GRP_NBR\tIU_ANCHOR_FLG\tOPEN_ITEM_KEY\r\n";
        
    $glje_details = DB::table('tr_glje_detail')->where('id_glje', $id)->get();

    foreach ($glje_details as $row){
      $row->ticket = str_replace('\n', ' ', $row->ticket);
      $row->ticket = str_replace('\r', ' ', $row->ticket);
      $gl = $row->value > 0 ? "GLE" : "GLR";
      
      $content .= "L\tUNDP1\tNEXT\tUSD\t{$row->acc}\t\t{$row->dept}\t{$row->opu}\t\t{$row->fund}\t\t\t\t\t\t\t{$row->imp_agent}\t{$row->donor}\t\t{$row->project}\t\t\t\t\t{$row->value}\tN\t\t\t{$row->ticket}\tUSD\tUNORE\t{$row->value}\t1\t{$row->pcbu}\t{$row->activities}\t{$gl}\t\t\t\t\t\t\t\t\t\r\n";
    }

    return $content;
  }

  public function billing_list(Request $req){
    $where = null;
    // if (session('user_agency_unit_id') != null && \Auth::user()->agency->agency_unit_code != 'FRMU') {
    //   $where = "id_agency_unit_buyer = ".Session::get('user_agency_unit_id');
    //   $where .= " OR id_agency_unit_buyer = ".\Auth::user()->agency->parent->id_agency_unit;
    // }

    $start_date = $req->start_date;
    $end_date = $req->end_date;

    if (!empty($start_date)){
      if (!empty($end_date)){
        $where .= " (DATE(tr_billing.date_created) BETWEEN '".$start_date."' AND '".$end_date."') ";
      }else{
        $where .= " DATE(tr_billing.date_created) = '".$start_date."'";
      }
    }
    return Datatables::of(TrBilling::basic_raw_mapping($where))
    ->editColumn('amount_billing_local', function($list){
      return number_format($list->amount_billing_local);
    })
    ->editColumn('amount_billing', function($list){
      return number_format($list->amount_billing);
    })
    ->addColumn('action', function($list){
      $action = "";
      if ($list->invoice_no != '-') {
        $action = "<a href='".route('mybillings.show', ['id' => $list->id_billing])."' title='View Invoice'><i class='fa fa-eye'></i></a>";
      }else {
        $action = "<a href='".route('mybillings.show_group', ['id' => $list->id_billing])."' title='View Invoice'><i class='fa fa-eye'></i></a> &nbsp; | &nbsp;";
        $action .= "<a href='#' onclick='deleteRow(".$list->id_billing.")' title='Delete'><i class='fa fa-trash'></i> </a>";
      }
      return $action;
    })->make(true);
  }

  public function transaction_ready_to_bill($id_agency_unit){
    if ($id_agency_unit < 0 ){
      return response()->json(['message' => 'error'], 404);
    }
    $results = TrBilling::transaction_ready_to_bill("p.id_agency_unit = $id_agency_unit");
    return response()->json(['data' => $results]);
  }
}
