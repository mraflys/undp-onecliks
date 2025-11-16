<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Cache, DB, Session;
use App\GeneralHelper;
use Datatables;
date_default_timezone_set('Asia/Jakarta');

class ApiFrontEndController extends Controller
{
  public function summary_ontime_and_delay($id_agency_unit){
    $where = "WHERE id_status = 2 AND id_transaction_parent IS NULL";
    if ($id_agency_unit > 0) {
      $where .= " AND id_agency_unit_service = ".$id_agency_unit;
    }    
    $query = "SELECT SUM(IF(delay > 0, 1, 0)) AS delay, SUM(IF(delay = 0, 1, 0)) AS ontime
    FROM (
      SELECT fn_get_number_workday(date_end_estimated, now(), false) AS delay, 1
      FROM ( SELECT * FROM tr_service $where ) tr 
      JOIN ( SELECT * FROM tr_service WHERE date_deleted IS NULL AND id_transaction_parent IS NOT NULL) gr ON gr.id_transaction_parent=tr.id_transaction
      JOIN (SELECT * FROM tr_service_workflow WHERE date_start_actual IS NOT NULL AND date_end_actual IS NULL) tw ON gr.id_transaction=tw.id_transaction
    ) t";

    $summary = DB::select($query)[0];
    return response()->json(['data' => $summary]);
  }

  public function summary_ongoing($id_agency_unit){
    $where = "WHERE id_status = 2 AND id_transaction_parent IS NULL";
    if ($id_agency_unit > 0) {
      $where .= " AND id_agency_unit_service = ".$id_agency_unit;
    }    
    $query = "SELECT agency.id_agency_unit, agency.agency_unit_name, agency.agency_unit_code, is_service_unit, COUNT(tr.id_transaction) AS count
            FROM (SELECT * FROM ms_agency_unit WHERE id_agency_unit_parent = 1 AND date_deleted IS NULL) agency
            LEFT JOIN ( SELECT * FROM tr_service $where ) tr ON tr.id_agency_unit_service=agency.id_agency_unit
            JOIN ( SELECT * FROM tr_service WHERE date_deleted IS NULL AND id_transaction_parent IS NOT NULL) gr ON gr.id_transaction_parent=tr.id_transaction
            JOIN (SELECT * FROM tr_service_workflow WHERE date_start_actual IS NOT NULL AND date_end_actual IS NULL) tw ON gr.id_transaction=tw.id_transaction
            GROUP BY agency.id_agency_unit, agency.agency_unit_name, is_service_unit,agency.agency_unit_code";
    $summary = DB::select($query);
    return response()->json(['data' => $summary]);

  }
}
