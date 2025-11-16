<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB, Session;

class TrBilling extends Model
{
  use SoftDeletes;
  protected $table = 'tr_billing';
  protected $primaryKey = 'id_billing';
  const UPDATED_AT = 'date_updated';
  const DELETED_AT = 'date_deleted';
  const CREATED_AT = 'date_created';

  public static function basic_raw_mapping($additional_filter = null){
    $where = "tr_billing.id_billing IS NOT NULL";
    if (!is_null($additional_filter)) $where .= " AND (".$additional_filter.")";

    $query = self::select(DB::raw("COALESCE(tr_billing.invoice_no, '-') AS invoice_no, tr_billing.id_billing, tr_billing.id_agency_unit_buyer, tr_billing.agency_name, tr_billing.currency_name, tr_billing.amount_billing_local, tr_billing.amount_billing, tr_billing.date_created, MAX(tr_billing_detail.date_payment) AS date_payment")) 
      ->join('tr_billing_detail','tr_billing.id_billing', '=', 'tr_billing_detail.id_billing')
      ->whereRaw($where)
      ->groupBy(["tr_billing.invoice_no","tr_billing.id_billing","tr_billing.id_agency_unit_buyer","tr_billing.agency_name","tr_billing.currency_name","tr_billing.amount_billing_local","tr_billing.amount_billing","tr_billing.date_created"]);

    return $query;
  }

  public function details(){
    return $this->hasMany('App\TrBillingDetail', 'id_billing');
  }

  public function creator(){
    return $this->belongsTo('App\SecUser', 'created_by', 'user_name');
  }

  public static function custom_detail_by_id($id){
    $query = "SELECT t.id_transaction, t.transaction_code, b.invoice_no, b.id_billing, d.id_billing_detail, b.id_agency_unit_buyer, agency_name, b.currency_name, b.amount_billing_local AS total_amount_local, b.amount_billing AS total_amount, COALESCE(d.date_payment, '-') AS date_payment, b.date_created, b.date_due_payment,
                 u.person_name AS issued_by, d.amount_billing_local, d.amount_billing, service_name, t.description, b.description AS invoice_description, b.unore, t.service_price
          FROM tr_billing b
          JOIN tr_billing_detail d ON b.id_billing=d.id_billing
          JOIN sec_user u ON b.created_by=u.user_name
          JOIN tr_service t ON d.id_transaction=t.id_transaction
          WHERE b.id_billing = $id";
    return DB::select($query);

  }

  public static function custom_detail_by_id_new($id){
    $query = "SELECT t.id_transaction, t.transaction_code, b.invoice_no, b.id_billing, d.id_billing_detail, b.id_agency_unit_buyer, agency_name, b.currency_name, b.amount_billing_local AS total_amount_local, b.amount_billing AS total_amount, COALESCE(d.date_payment, '-') AS date_payment, b.date_created, b.date_due_payment,
                 u.person_name AS issued_by, d.amount_billing_local, d.amount_billing, service_name, t.description, b.description AS invoice_description, b.unore, t.service_price
          FROM tr_billing b
          JOIN tr_billing_detail d ON b.id_billing=d.id_billing
          JOIN sec_user u ON b.created_by=u.user_name
          JOIN tr_service t ON d.id_transaction=t.id_transaction
          WHERE b.id_billing = $id AND t.qty != 0";
    return DB::select($query);

  }

  public static function custom_detail_by_id1($id){
    $query = "SELECT t.id_transaction, t.transaction_code, b.invoice_no, b.id_billing, d.id_billing_detail, b.id_agency_unit_buyer, agency_name, b.currency_name, b.amount_billing_local AS total_amount_local, b.amount_billing AS total_amount, COALESCE(d.date_payment, '-') AS date_payment, b.date_created, b.date_due_payment,
    u.person_name AS issued_by, d.amount_billing_local, d.amount_billing, service_name, t.description, b.description AS invoice_description, b.unore, t.service_price, au.agency_unit_code, t.person_name_buyer, t.qty
    FROM tr_billing b
    JOIN tr_billing_detail d ON b.id_billing=d.id_billing
    JOIN sec_user u ON b.created_by=u.user_name
    JOIN tr_service t ON d.id_transaction=t.id_transaction
    JOIN ms_agency_unit au ON t.id_agency_unit_service=au.id_agency_unit
    WHERE b.id_billing = $id";
    return DB::select($query);

  }

  public static function billing_detail_coa_by_id($id){
    $query = "SELECT b.id_billing, d.id_billing_detail, COALESCE(b.invoice_no, '-') AS invoice_no, 
                b.date_created, b.date_due_payment, b.agency_code, t.id_transaction, t.transaction_code, g.agency_unit_code AS unit_code_service, 
                agency_name, t.agency_code_buyer, t.description, t.person_name_buyer,  b.currency_name, t.qty, 
                b.amount_billing_local AS total_amount_local, b.amount_billing AS total_amount, COALESCE(d.date_payment, '-') AS date_payment,
                b.amount_billing AS total_amount_billing, b.amount_billing_local AS total_amount_billing_local,
                 u.person_name AS issued_by, d.amount_billing_local, d.amount_billing, service_name, b.description AS invoice_description, b.unore,
                acc, opu, fund, dept, imp_agent, donor, pcbu, project, activities, arn, ulo, project_no, t.payment_method
          FROM tr_billing b
          JOIN tr_billing_detail d ON b.id_billing=d.id_billing
          JOIN sec_user u ON b.created_by=u.user_name
          JOIN tr_service t ON d.id_transaction=t.id_transaction
          LEFT JOIN tr_service_coa_atlas a ON t.id_transaction=a.id_transaction
          LEFT JOIN tr_service_coa_other o ON t.id_transaction=o.id_transaction
          JOIN ms_agency_unit g ON t.id_agency_unit_service=g.id_agency_unit          
          WHERE b.id_billing = $id";
    return DB::select($query);
  }

  public static function billing_detail_coa_by_id_new($id){
    $query = "SELECT b.id_billing, d.id_billing_detail, COALESCE(b.invoice_no, '-') AS invoice_no, 
                b.date_created, b.date_due_payment, b.agency_code, t.id_transaction, t.transaction_code, g.agency_unit_code AS unit_code_service, 
                agency_name, t.agency_code_buyer, t.description, t.person_name_buyer,  b.currency_name, t.qty, 
                b.amount_billing_local AS total_amount_local, b.amount_billing AS total_amount, COALESCE(d.date_payment, '-') AS date_payment,
                b.amount_billing AS total_amount_billing, b.amount_billing_local AS total_amount_billing_local,
                 u.person_name AS issued_by, d.amount_billing_local, d.amount_billing, service_name, b.description AS invoice_description, b.unore,
                acc, opu, fund, dept, imp_agent, donor, pcbu, project, activities, arn, ulo, project_no, t.payment_method
          FROM tr_billing b
          JOIN tr_billing_detail d ON b.id_billing=d.id_billing
          JOIN sec_user u ON b.created_by=u.user_name
          JOIN tr_service t ON d.id_transaction=t.id_transaction
          LEFT JOIN tr_service_coa_atlas a ON t.id_transaction=a.id_transaction
          LEFT JOIN tr_service_coa_other o ON t.id_transaction=o.id_transaction
          JOIN ms_agency_unit g ON t.id_agency_unit_service=g.id_agency_unit          
          WHERE b.id_billing = $id AND t.qty != 0";
    return DB::select($query);
  }

  public static function transaction_ready_to_bill_2($where = ""){
    if ($where != "") {
      $where .= " AND id_transaction NOT IN (SELECT `tr_billing_detail`.`id_transaction` FROM `tr_billing` JOIN `tr_billing_detail` on `tr_billing_detail`.`id_billing` = `tr_billing`.`id_billing`
      where `tr_billing`.`date_deleted` is null)";
    } else {
      $where = "id_transaction NOT IN (SELECT `tr_billing_detail`.`id_transaction` FROM `tr_billing` JOIN `tr_billing_detail` on `tr_billing_detail`.`id_billing` = `tr_billing`.`id_billing`
      where `tr_billing`.`date_deleted` is null)";
    }

    $query = "SELECT p.id_agency_unit, p.agency_unit_code, p.agency_unit_name, id_transaction, transaction_code, id_agency_unit_buyer, service_name, t.description, date_authorized, date_finished, is_finished, service_price, payment_method
        FROM (
          SELECT tr.id_transaction, tr.transaction_code, tr.id_agency_unit_buyer, tr.service_name, tr.description, tr.date_authorized, tr.date_finished, tr.is_finished, (SELECT sum(`service_price`) FROM `tr_service` WHERE `id_transaction_parent` = tr.id_transaction and `id_status` = 5 and `is_finished` = 1) as 'service_price', tr.payment_method
          FROM (SELECT * FROM tr_service WHERE id_transaction_parent IS NULL AND date_deleted IS NULL AND is_free_of_charge=0 AND date_start_billing IS NULL AND service_price > 0 AND is_finished = 1 AND id_status IN (4, 5, 6) ) tr
        ) t 
        JOIN ms_agency_unit a ON a.id_agency_unit=t.id_agency_unit_buyer
        JOIN ms_agency_unit p ON p.id_agency_unit=a.id_agency_unit_parent
        WHERE $where ORDER BY transaction_code";

    return DB::select($query);
  }

  public static function transaction_ready_to_bill($where = ""){
    if ($where != "") {
      $where .= " AND id_transaction NOT IN (SELECT `tr_billing_detail`.`id_transaction` FROM `tr_billing` JOIN `tr_billing_detail` on `tr_billing_detail`.`id_billing` = `tr_billing`.`id_billing`
      where `tr_billing`.`date_deleted` is null)";
    } else {
      $where = "id_transaction NOT IN (SELECT `tr_billing_detail`.`id_transaction` FROM `tr_billing` JOIN `tr_billing_detail` on `tr_billing_detail`.`id_billing` = `tr_billing`.`id_billing`
      where `tr_billing`.`date_deleted` is null)";
    }

    $query = "SELECT p.id_agency_unit, p.agency_unit_code, p.agency_unit_name, id_transaction, transaction_code, id_agency_unit_buyer, service_name, t.description, date_authorized, date_finished, is_finished, service_price, payment_method
        FROM (
          SELECT tr.id_transaction, tr.transaction_code, tr.id_agency_unit_buyer, tr.service_name, tr.description, tr.date_authorized, tr.date_finished, tr.is_finished, (tr.service_price * tr.qty) as service_price, tr.payment_method
          FROM (SELECT * FROM tr_service WHERE id_transaction_parent IS NULL AND date_deleted IS NULL AND is_free_of_charge=0 AND date_start_billing IS NULL AND service_price > 0 AND is_finished = 1 AND id_status IN (4, 5, 6) ) tr
        ) t 
        JOIN ms_agency_unit a ON a.id_agency_unit=t.id_agency_unit_buyer
        JOIN ms_agency_unit p ON p.id_agency_unit=a.id_agency_unit_parent
        WHERE $where ORDER BY transaction_code DESC";

    return DB::select($query);
  }

  public static function transaction_ready_to_bill_backup($where = ""){
    if ($where != "") {
      $where .= " AND id_transaction NOT IN (SELECT id_transaction FROM tr_billing_detail)";
    } else {
      $where = "id_transaction NOT IN (SELECT id_transaction FROM tr_billing_detail)";
    }

    $query = "SELECT p.id_agency_unit, p.agency_unit_code, p.agency_unit_name, id_transaction, transaction_code, id_agency_unit_buyer, service_name, t.description, date_authorized, date_finished, is_finished, service_price, payment_method
        FROM (
          SELECT tr.id_transaction, tr.transaction_code, tr.id_agency_unit_buyer, tr.service_name, tr.description, tr.date_authorized, tr.date_finished, tr.is_finished, tr.service_price, tr.payment_method
          FROM (SELECT * FROM tr_service WHERE id_transaction_parent IS NULL AND date_deleted IS NULL AND is_free_of_charge=0 AND date_start_billing IS NULL AND service_price > 0 AND is_finished = 1 AND id_status IN (4, 5, 6) ) tr
        ) t 
        JOIN ms_agency_unit a ON a.id_agency_unit=t.id_agency_unit_buyer
        JOIN ms_agency_unit p ON p.id_agency_unit=a.id_agency_unit_parent
        WHERE $where ORDER BY transaction_code";

    return DB::select($query);
  }

  public static function agency_to_bill($where){
    if ($where == "") {
      $where = " p.id_agency_unit IS NOT NULL ";
    }

    $query = "SELECT p.id_agency_unit, p.agency_unit_name, SUM(service_price) AS service_price
        FROM (
          SELECT tr.id_transaction, tr.id_agency_unit_buyer, tr.is_finished, tr.service_price
          FROM (SELECT * FROM tr_service WHERE id_transaction_parent IS NULL AND date_deleted IS NULL AND is_free_of_charge=0 AND date_start_billing IS NULL AND service_price > 0 AND is_finished = 1 AND id_status IN (4, 5, 6)) tr
        ) t 
        JOIN ms_agency_unit a ON a.id_agency_unit=t.id_agency_unit_buyer
        JOIN ms_agency_unit p ON p.id_agency_unit=a.id_agency_unit_parent
        WHERE $where GROUP BY p.id_agency_unit, p.agency_unit_name ORDER BY p.agency_unit_name";

    return DB::select($query);
  }

  public static function glje_list(){
    $query = "SELECT glje.id_glje, COALESCE(date_downloaded, '-') AS date_downloaded, glje_no, COUNT(DISTINCT id_transaction) AS trans, glje.date_created
              FROM (SELECT * FROM tr_glje WHERE date_deleted IS NULL) glje
              JOIN (SELECT * FROM tr_glje_detail WHERE date_deleted IS NULL) detail ON detail.id_glje = glje.id_glje
              GROUP BY glje.id_glje, date_downloaded, glje_no";

    return DB::select($query);
  }

  public static function glje_transaction_to_bill($id_glje = null){
    if (is_null($id_glje)){
      $where = "id_transaction NOT IN (SELECT id_transaction FROM tr_glje_detail)";
    }else{
      $where = "id_transaction IN (SELECT id_transaction FROM tr_glje_detail WHERE id_glje = ".$id_glje.")";
    }
    $query = "SELECT p.id_agency_unit, p.agency_unit_code, p.agency_unit_name, id_transaction, transaction_code, id_agency_unit_buyer, service_name, t.description, date_authorized, date_finished, is_finished, service_price
        FROM (
          SELECT tr.id_transaction, tr.transaction_code, tr.id_agency_unit_buyer, tr.service_name, tr.description, tr.date_authorized, tr.date_finished, tr.is_finished, tr.service_price
          FROM (SELECT * FROM tr_service 
                WHERE id_transaction_parent IS NULL AND 
                    date_deleted IS NULL AND 
                    is_free_of_charge=0 AND 
                    date_start_billing IS NULL AND 
                    service_price > 0 AND 
                    payment_method='atlas' AND 
                    id_status IN (4, 5, 6) 
                ) tr
        ) t 
        JOIN ms_agency_unit a ON a.id_agency_unit=t.id_agency_unit_buyer
        JOIN ms_agency_unit p ON p.id_agency_unit=a.id_agency_unit_parent
        WHERE $where";

    return DB::select($query);
  }
}
