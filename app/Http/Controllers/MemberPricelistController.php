<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB, Session;
use Validator;
use Datatables;
use App\PriceList;
use App\ServiceList;
use App\GeneralHelper;

date_default_timezone_set('Asia/Jakarta');

class MemberPricelistController extends Controller
{
  public function index(){
    $data['title'] = 'PriceList';
    $data['breadcrumps'] = ['Member Area', 'PriceList'];
    return view('member.pricelist.index', $data);
  }

  public function pricelist(Request $req){
    $period = !empty($req->period) ? $req->period : date('Y-m-d');
    $filter = "(DATE(date_start_price) <= '".$period."' AND DATE(date_end_price) >= '".$period."') AND ms_service_pricelist.id_currency = 1";
    
    $id_service_unit = $req->id_service_unit;
    if (!empty($id_service_unit)) {
      $filter .= " AND ms_service.id_agency_unit = ".$id_service_unit;
    }
    $service_name = strtolower(urldecode($req->service_name));
    if (!empty($service_name)) {
      $filter .= " AND LOWER(ms_service.service_name) LIKE '%".$service_name."%'";
    }
    return Datatables::of(ServiceList::pricelist_mapping($filter))
    ->editColumn('price', function($list){ return number_format($list->price, 2); })
    ->make(true);
  }
}
