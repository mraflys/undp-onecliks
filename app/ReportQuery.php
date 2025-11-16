<?php
namespace App;
use DB;
use Session;
use Cache;

class ReportQuery 
{
  
	public static function critical($where){
		$query = "SELECT id_agency_unit, agency_unit_code, agency_unit_name, ag.id_status, status_name, COALESCE(COUNT(id_transaction), 0) AS cnt, COALESCE(SUM(value), 0) AS value
        FROM (SELECT id_agency_unit, agency_unit_code, agency_unit_name, id_status, status_name 
              FROM (SELECT id_agency_unit, agency_unit_code, agency_unit_name 
                    FROM ms_agency_unit 
                    WHERE date_deleted IS NULL AND id_agency_unit IN (2,3,4,5,6)) a
              JOIN ms_status
              ORDER BY id_status
              ) ag
        LEFT JOIN
          ( SELECT tr.id_transaction, tr.id_status, tr.id_agency_unit_service, tr.transaction_code, SUM(io.info_value) AS value
            FROM (SELECT * 
                  FROM tr_service 
                  WHERE ((id_agency_unit_service = 2 AND service_name LIKE '%recruitment%') OR
                         (id_agency_unit_service = 3 AND service_name LIKE '%helpdesk problem solving%') OR
                         (id_agency_unit_service = 4 AND service_name LIKE '%payment%') OR
                         (id_agency_unit_service = 5 AND service_name LIKE '%individual consultant%') OR
                         (id_agency_unit_service = 6 AND service_name LIKE '%travel%')) AND
                        date_deleted IS NULL AND
                        id_transaction_parent IS NULL AND $where
                  ) tr
            JOIN (SELECT * FROM tr_service WHERE date_deleted IS NULL AND id_transaction_parent IS NOT NULL) gr ON gr.id_transaction_parent=tr.id_transaction
            JOIN (SELECT * FROM tr_service_workflow WHERE date_deleted IS NULL) wf ON wf.id_transaction=gr.id_transaction
            LEFT JOIN (SELECT * 
                       FROM tr_service_workflow_info 
                       WHERE date_deleted IS NULL AND 
                             ( info_title LIKE '%amount of%' OR info_title LIKE '%voucher amount%' )
                       ) io ON io.id_transaction_workflow=wf.id_transaction_workflow
            GROUP BY tr.id_transaction, tr.id_status, tr.id_agency_unit_service, tr.transaction_code
          ) t ON t.id_agency_unit_service=ag.id_agency_unit AND t.id_status=ag.id_status
          GROUP BY id_agency_unit, agency_unit_code, agency_unit_name, ag.id_status, status_name";

    return DB::select($query);
	}

	public static function detail($where = null){
    if(!is_null($where)){
      $where = "WHERE $where";
    }
    
		$query = "SELECT
            tr.id_transaction, tr.transaction_code, tr.service_name, tr.description, tr.date_finished, tr.person_name_buyer, tr.authorized_by,
            tr.agency_code_buyer, tr.date_authorized, tr.id_agency_unit_service, tr.service_rating, IF(tr.is_free_of_charge, 0, tr.service_price) AS service_price,
            wf.sequence, wf.id_transaction_workflow, wf.workflow_name, wf.workflow_day, wf.completed_by,  
            fn_get_number_workday(wf.date_start_actual, date_end_actual, false) as timeliness,
            info.id_transaction_workflow_info, info.info_title, info.info_value,
            inv.invoice_no, inv.invoice_date, glje.glje_no, glje.glje_date, info2.info_value as 'amount_of'
          FROM (SELECT * FROM tr_service WHERE date_deleted IS NULL AND id_transaction_parent IS NULL) tr
          JOIN (SELECT * FROM tr_service WHERE date_deleted IS NULL AND id_transaction_parent IS NOT NULL) gr ON tr.id_transaction=gr.id_transaction_parent
          JOIN (SELECT * FROM tr_service_workflow WHERE date_deleted IS NULL) wf ON gr.id_transaction=wf.id_transaction
          LEFT JOIN (SELECT * FROM tr_service_workflow_info WHERE date_deleted IS NULL) info ON info.id_transaction_workflow=wf.id_transaction_workflow
          LEFT JOIN (SELECT * FROM tr_service_workflow_info WHERE date_deleted IS NULL) info2 ON info2.id_transaction_workflow_info = info.id_transaction_workflow_info AND info2.info_title like '%Amount Of%'
          LEFT JOIN (SELECT invoice_no, id_transaction, d.date_payment  AS invoice_date FROM tr_billing b
          JOIN tr_billing_detail d ON b.id_billing=d.id_billing 
                    ) inv ON inv.id_transaction=tr.id_transaction
          LEFT JOIN (SELECT DISTINCT id_transaction, glje_no, if(glje_no IS NOT NULL, d.date_updated, NULL) AS glje_date FROM tr_glje g
          JOIN tr_glje_detail d ON g.id_glje=d.id_glje
                    ) glje ON glje.id_transaction=tr.id_transaction $where";

    return DB::select($query);
	}

  public static function timeliness_query($where){
    return "SELECT id_agency_unit, agency_unit_code, agency_unit_name, COALESCE(COUNT(id_transaction), 0) AS cnt, COALESCE(SUM(sla >= delay), 0) AS ontime, COALESCE(SUM(sla < delay), 0) AS delay
                      FROM (SELECT id_agency_unit, agency_unit_code, agency_unit_name 
                            FROM ms_agency_unit 
                            WHERE date_deleted IS NULL AND id_agency_unit IN (2,3,4,5,6)
                            ) ag
                      LEFT JOIN (
                                  SELECT tr.id_transaction, tr.id_agency_unit_service, tr.date_authorized, tr.date_finished, SUM(workflow_day) AS sla, fn_get_number_workday(tr.date_authorized, tr.date_finished, false) AS delay
                                  FROM (SELECT * FROM tr_service WHERE date_deleted IS NULL AND id_transaction_parent IS NULL AND date_authorized IS NOT NULL AND date_finished IS NOT NULL AND $where) tr
                                  JOIN (SELECT * FROM tr_service WHERE date_deleted IS NULL AND id_transaction_parent IS NOT NULL ) gr ON gr.id_transaction_parent=tr.id_transaction
                                  JOIN (SELECT * FROM tr_service_workflow WHERE date_deleted IS NULL) wf ON wf.id_transaction=gr.id_transaction
                                  GROUP BY tr.id_transaction, tr.id_agency_unit_service, tr.date_authorized, tr.date_finished
                                ) t ON t.id_agency_unit_service=ag.id_agency_unit
                      GROUP BY id_agency_unit, agency_unit_code, agency_unit_name";
  }

  public static function timeliness_analysis($where){
    $query = self::timeliness_query($where);
    return DB::select($query);
  }

  public static function timeliness_detail($where){
    $query = "SELECT tr.id_transaction, transaction_code, service_name, agency_code_buyer, id_agency_unit_service, agency_unit_code, person_name, workflow_name, workflow_day AS sla, date_start_actual, date_end_actual, date_start_estimated, date_end_estimated, actual-workflow_day AS delay, IF (workflow_day + delay <= 0, -workflow_day, delay) AS delay_true, actual, date_end_actual AS date_finished
          FROM (SELECT id_transaction, transaction_code, service_name, id_agency_unit_service, agency_code_buyer, date_finished, id_status
                FROM tr_service
                WHERE date_deleted IS NULL AND id_transaction_parent IS NULL AND date_authorized IS NOT NULL AND date_finished IS NOT NULL AND id_status IN (5,6,7) AND $where) tr
          JOIN (SELECT id_transaction, id_transaction_parent
                FROM tr_service 
                WHERE date_deleted IS NULL AND id_transaction_parent IS NOT NULL) gr ON gr.id_transaction_parent=tr.id_transaction
          JOIN (SELECT id_transaction, id_transaction_workflow, workflow_name, workflow_day, date_start_actual, date_start_estimated, date_end_actual, date_end_estimated, updated_by, fn_get_number_workday(date_start_actual, date_end_actual, false) AS actual, fn_get_number_workday(date_end_estimated, date_end_actual, true) as delay
                FROM tr_service_workflow
                WHERE date_deleted IS NULL AND date_end_actual IS NOT NULL AND date_end_estimated IS NOT NULL AND sequence > 1) wf ON wf.id_transaction=gr.id_transaction
          JOIN (SELECT * FROM sec_user WHERE date_deleted IS NULL) u ON u.user_name=wf.updated_by
          JOIN (SELECT * FROM ms_agency_unit WHERE date_deleted IS NULL) a ON a.id_agency_unit=tr.id_agency_unit_service";
    return DB::select($query);
  }

  public static function coa_detail($where){
    $query = "SELECT transaction_code, service_name, t.description, u.agency_unit_name AS unit_name, p.agency_unit_code AS agency_code, acc, opu, fund, dept, imp_agent, donor, pcbu, project, activities, contract_number, funding_source, c.id_exptype, exp_type_code, exp_type_name, percentage, service_price, s.id_status, status_name, agency_name_buyer as requester_unit
          FROM (SELECT * FROM tr_service WHERE date_deleted IS NULL AND $where) t
          JOIN (SELECT * FROM tr_service_coa_atlas WHERE date_deleted IS NULL) c ON c.id_transaction=t.id_transaction
          JOIN ms_status s ON s.id_status=t.id_status
          LEFT JOIN ms_agency_unit u ON u.id_agency_unit=t.id_agency_unit_buyer
          LEFT JOIN ms_agency_unit p ON p.id_agency_unit=u.id_agency_unit_parent
          LEFT JOIN ms_exp_type met ON met.id_exptype=c.id_exptype";
    return DB::select($query);
  }

  public static function coa_detail_excel($where){
    $query = "SELECT transaction_code, service_name, u.agency_unit_code, t.description, u.agency_unit_name AS unit_name, acc, opu, fund, dept, service_name as dateperiod, imp_agent, donor, pcbu, project, activities, contract_number, funding_source, c.id_exptype, exp_type_code, exp_type_name, percentage, service_price, status_name, agency_name_buyer as requester_unit
          FROM (SELECT * FROM tr_service WHERE date_deleted IS NULL AND $where) t
          JOIN (SELECT * FROM tr_service_coa_atlas WHERE date_deleted IS NULL) c ON c.id_transaction=t.id_transaction
          JOIN ms_status s ON s.id_status=t.id_status
          LEFT JOIN ms_agency_unit u ON u.id_agency_unit=t.id_agency_unit_service
          LEFT JOIN ms_agency_unit p ON p.id_agency_unit=u.id_agency_unit_parent
          LEFT JOIN ms_exp_type met ON met.id_exptype=c.id_exptype";
    return DB::select($query);
  }

  public static function monthly_income($where){
    $query = "SELECT a.id_agency_unit, a.agency_unit_name, a.agency_unit_code, color, COALESCE(id_transaction, 0) AS id_transaction, COALESCE(service_price, 0) AS service_price, COALESCE(workflow_day,0) AS workflow_day
              FROM (SELECT * FROM ms_agency_unit WHERE id_agency_unit_parent = 1 AND is_service_unit=1) a
              LEFT JOIN (
                SELECT tr.id_transaction, wf.id_agency_unit_pic, tr.service_price, SUM(wf.workflow_day) AS workflow_day
                FROM (SELECT * FROM tr_service WHERE id_transaction_parent IS NULL AND date_deleted IS NULL ) tr 
                JOIN (
                    SELECT * FROM (
                      SELECT id_transaction, date_payment FROM tr_billing_detail WHERE date_payment IS NOT NULL AND is_paid
                      UNION DISTINCT
                      SELECT id_transaction, date_updated 
                      FROM tr_glje_detail a
                      JOIN (SELECT id_glje FROM tr_glje WHERE glje_no IS NOT NULL) b ON a.id_glje=b.id_glje
                    ) t
                    WHERE $where
                      ) x ON x.id_transaction=tr.id_transaction
                JOIN (SELECT * FROM tr_service WHERE id_transaction_parent IS NOT NULL AND date_deleted IS NULL ) gr ON gr.id_transaction_parent=tr.id_transaction
                JOIN (SELECT * FROM tr_service_workflow WHERE date_deleted IS NULL AND id_agency_unit_pic IS NOT NULL) wf ON wf.id_transaction=gr.id_transaction
                GROUP BY tr.id_transaction, wf.id_agency_unit_pic, tr.service_price
                ) t ON t.id_agency_unit_pic=a.id_agency_unit 
                ";
    return DB::select($query);
  }

  public static function monthly_expenditure($where){
    $query = "SELECT a.id_agency_unit, a.agency_unit_name, a.agency_unit_code, COALESCE(SUM(service_price), 0) AS expenditure
              FROM (SELECT * FROM ms_agency_unit WHERE id_agency_unit_parent = 1 AND date_deleted IS NULL) a
              JOIN (SELECT * FROM tr_service WHERE id_transaction_parent IS NULL AND date_deleted IS NULL ) tr ON a.id_agency_unit=tr.id_agency_unit_buyer
              JOIN (
                  SELECT * FROM (
                    SELECT id_transaction, date_payment FROM tr_billing_detail WHERE date_payment IS NOT NULL AND is_paid
                    UNION DISTINCT
                    SELECT id_transaction, date_updated 
                    FROM tr_glje_detail a
                    JOIN (SELECT id_glje FROM tr_glje WHERE glje_no IS NOT NULL) b ON a.id_glje=b.id_glje
                  ) t
                  WHERE $where
                    ) p ON p.id_transaction=tr.id_transaction
              GROUP BY a.id_agency_unit, a.agency_unit_name, a.agency_unit_code";
    return DB::select($query);
  }

  public static function monthly_agency_expenditure($where){
    $query = "SELECT p.id_agency_unit, p.agency_unit_name, p.agency_unit_code, COALESCE(SUM(service_price), 0) AS expenditure
              FROM (SELECT * FROM ms_agency_unit WHERE date_deleted IS NULL) a
              JOIN (SELECT * FROM ms_agency_unit WHERE date_deleted IS NULL AND id_agency_unit_parent IS NULL) p ON p.id_agency_unit=a.id_agency_unit_parent
              JOIN (SELECT * FROM tr_service WHERE id_transaction_parent IS NULL AND date_deleted IS NULL ) tr ON a.id_agency_unit=tr.id_agency_unit_buyer
              JOIN (
                  SELECT * FROM (
                    SELECT id_transaction, date_payment FROM tr_billing_detail WHERE date_payment IS NOT NULL AND is_paid
                    UNION DISTINCT
                    SELECT id_transaction, date_updated 
                    FROM tr_glje_detail a
                    JOIN (SELECT id_glje FROM tr_glje WHERE glje_no IS NOT NULL) b ON a.id_glje=b.id_glje
                  ) t
                  WHERE $where
                    ) x ON x.id_transaction=tr.id_transaction
              GROUP BY p.id_agency_unit, p.agency_unit_name, p.agency_unit_code";
    return DB::select($query);
  }

  public static function invoice_issue($where){
      $query = "SELECT id_billing, date_created, agency_name, agency_code, aging,
                    SUM(amount_billing) AS amount_billing, SUM(amount_billing_local) AS amount_billing_local, 
                    MAX(date_payment) AS date_payment, MAX(date_due_payment) AS date_due_payment,
                    SUM(amount_paid) AS amount_paid, SUM(amount_paid_local) AS amount_paid_local
          FROM (
          SELECT invoice_no AS id_billing, bi.date_created, agency_name, agency_code, amount_billing, amount_billing_local, 
                 de.date_payment, de.date_due_payment, IF (is_paid = 1, amount_billing, 0) AS amount_paid, IF (is_paid = 1, amount_billing_local, 0) AS amount_paid_local, 
                 DATEDIFF(now(), bi.date_created) AS aging 
          FROM (SELECT invoice_no, id_billing, date_created, agency_name, agency_code FROM tr_billing WHERE date_deleted IS NULL AND invoice_no IS NOT NULL) bi
          JOIN (SELECT * FROM tr_billing_detail) de ON de.id_billing=bi.id_billing
          ) t
          WHERE $where
          GROUP BY id_billing, date_created, agency_name, agency_code, aging";
      return DB::select($query);
  }

  public static function dsa_advance($where){
      $query = "SELECT name.info_value AS name, voucher.info_value AS voucher, tr.transaction_code, tr.id_status,
                      tr.user_name_buyer, tr.person_name_buyer, tr.supervisor_mail,
                     fn_str_to_date(dep_date.info_value) AS departure_date, 
                     ret_date.info_value AS return_date, 
                     tr.date_finished AS settlement_date, 
                     set_voucher.info_value AS settlement_voucher,
                     aging
              FROM (SELECT * FROM tr_service WHERE id_transaction_parent IS NULL AND date_deleted IS NULL AND service_name LIKE '%DSA%' AND id_status IN (2, 4, 5) AND date_created <= now()) tr 
              JOIN (SELECT gr.id_transaction, gr.id_transaction_parent, wf.id_transaction_workflow, info.info_title, info.info_value
                    FROM (SELECT id_transaction, id_transaction_parent FROM tr_service WHERE id_transaction_parent IS NOT NULL AND date_deleted IS NULL ) gr 
                    JOIN (SELECT id_transaction, id_transaction_workflow FROM tr_service_workflow WHERE date_deleted IS NULL) wf ON wf.id_transaction=gr.id_transaction
                    JOIN (SELECT id_transaction_workflow, info_title, info_value 
                            FROM tr_service_workflow_info 
                            WHERE date_deleted IS NULL AND info_title LIKE 'traveller\'s name%') info ON info.id_transaction_workflow=wf.id_transaction_workflow
                    ) name ON name.id_transaction_parent=tr.id_transaction
              JOIN (SELECT gr.id_transaction, gr.id_transaction_parent, wf.id_transaction_workflow, info.info_title, info.info_value
                    FROM (SELECT id_transaction, id_transaction_parent FROM tr_service WHERE id_transaction_parent IS NOT NULL AND date_deleted IS NULL ) gr 
                    JOIN (SELECT id_transaction, id_transaction_workflow FROM tr_service_workflow WHERE date_deleted IS NULL) wf ON wf.id_transaction=gr.id_transaction
                    JOIN (SELECT id_transaction_workflow, info_title, info_value 
                            FROM tr_service_workflow_info 
                            WHERE date_deleted IS NULL AND info_title LIKE 'voucher number%') info ON info.id_transaction_workflow=wf.id_transaction_workflow
                    ) voucher ON voucher.id_transaction_parent=tr.id_transaction
              JOIN (SELECT gr.id_transaction, gr.id_transaction_parent, wf.id_transaction_workflow, info.info_title, info.info_value
                    FROM (SELECT id_transaction, id_transaction_parent FROM tr_service WHERE id_transaction_parent IS NOT NULL AND date_deleted IS NULL ) gr 
                    JOIN (SELECT id_transaction, id_transaction_workflow FROM tr_service_workflow WHERE date_deleted IS NULL) wf ON wf.id_transaction=gr.id_transaction
                    JOIN (SELECT id_transaction_workflow, info_title, info_value 
                            FROM tr_service_workflow_info 
                            WHERE date_deleted IS NULL AND info_title LIKE 'departure date') info ON info.id_transaction_workflow=wf.id_transaction_workflow
                    ) dep_date ON dep_date.id_transaction_parent=tr.id_transaction
              JOIN (SELECT gr.id_transaction, gr.id_transaction_parent, wf.id_transaction_workflow, info.info_title, info.info_value, DATEDIFF(now(), info.info_value) AS aging
                    FROM (SELECT id_transaction, id_transaction_parent FROM tr_service WHERE id_transaction_parent IS NOT NULL AND date_deleted IS NULL ) gr 
                    JOIN (SELECT id_transaction, id_transaction_workflow FROM tr_service_workflow WHERE date_deleted IS NULL) wf ON wf.id_transaction=gr.id_transaction
                    JOIN (SELECT id_transaction_workflow, info_title, fn_str_to_date(info_value) AS info_value
                            FROM tr_service_workflow_info 
                            WHERE date_deleted IS NULL AND info_title LIKE 'return date' AND fn_str_to_date(info_value) <= now() ) info ON info.id_transaction_workflow=wf.id_transaction_workflow
                    ) ret_date ON ret_date.id_transaction_parent=tr.id_transaction
              JOIN (SELECT gr.id_transaction, gr.id_transaction_parent, wf.id_transaction_workflow, info.info_title, info.info_value
                    FROM (SELECT id_transaction, id_transaction_parent FROM tr_service WHERE id_transaction_parent IS NOT NULL AND date_deleted IS NULL ) gr 
                    JOIN (SELECT id_transaction, id_transaction_workflow FROM tr_service_workflow WHERE date_deleted IS NULL) wf ON wf.id_transaction=gr.id_transaction
                    JOIN (SELECT id_transaction_workflow, info_title, info_value 
                            FROM tr_service_workflow_info 
                            WHERE date_deleted IS NULL AND info_title LIKE 'Payment voucher%') info ON info.id_transaction_workflow=wf.id_transaction_workflow
                    ) set_voucher ON set_voucher.id_transaction_parent=tr.id_transaction
              WHERE $where";
      return DB::select($query);
  } 

  public static function dsa_advance_null($where){
      $query = "SELECT name.info_value AS name, voucher.info_value AS voucher, tr.transaction_code, tr.id_status,
                     fn_str_to_date(dep_date.info_value) AS departure_date, 
                     ret_date.info_value AS return_date, 
                     tr.date_finished AS settlement_date, 
                     set_voucher.info_value AS settlement_voucher,
                     aging
              FROM (SELECT * FROM tr_service WHERE id_transaction_parent IS NULL AND date_deleted IS NULL AND service_name LIKE '%DSA%' AND id_status IN (2, 4, 5) AND date_created <= now()) tr 
              JOIN (SELECT gr.id_transaction, gr.id_transaction_parent, wf.id_transaction_workflow, info.info_title, info.info_value
                    FROM (SELECT id_transaction, id_transaction_parent FROM tr_service WHERE id_transaction_parent IS NOT NULL AND date_deleted IS NULL ) gr 
                    JOIN (SELECT id_transaction, id_transaction_workflow FROM tr_service_workflow WHERE date_deleted IS NULL) wf ON wf.id_transaction=gr.id_transaction
                    JOIN (SELECT id_transaction_workflow, info_title, info_value 
                            FROM tr_service_workflow_info 
                            WHERE date_deleted IS NULL AND info_title LIKE 'traveller\'s name%') info ON info.id_transaction_workflow=wf.id_transaction_workflow
                    ) name ON name.id_transaction_parent=tr.id_transaction
              JOIN (SELECT gr.id_transaction, gr.id_transaction_parent, wf.id_transaction_workflow, info.info_title, info.info_value
                    FROM (SELECT id_transaction, id_transaction_parent FROM tr_service WHERE id_transaction_parent IS NOT NULL AND date_deleted IS NULL ) gr 
                    JOIN (SELECT id_transaction, id_transaction_workflow FROM tr_service_workflow WHERE date_deleted IS NULL) wf ON wf.id_transaction=gr.id_transaction
                    JOIN (SELECT id_transaction_workflow, info_title, info_value 
                            FROM tr_service_workflow_info 
                            WHERE date_deleted IS NULL AND info_title LIKE 'voucher number%') info ON info.id_transaction_workflow=wf.id_transaction_workflow
                    ) voucher ON voucher.id_transaction_parent=tr.id_transaction
              JOIN (SELECT gr.id_transaction, gr.id_transaction_parent, wf.id_transaction_workflow, info.info_title, info.info_value
                    FROM (SELECT id_transaction, id_transaction_parent FROM tr_service WHERE id_transaction_parent IS NOT NULL AND date_deleted IS NULL ) gr 
                    JOIN (SELECT id_transaction, id_transaction_workflow FROM tr_service_workflow WHERE date_deleted IS NULL) wf ON wf.id_transaction=gr.id_transaction
                    JOIN (SELECT id_transaction_workflow, info_title, info_value 
                            FROM tr_service_workflow_info 
                            WHERE date_deleted IS NULL AND info_title LIKE 'departure date') info ON info.id_transaction_workflow=wf.id_transaction_workflow
                    ) dep_date ON dep_date.id_transaction_parent=tr.id_transaction
              JOIN (SELECT gr.id_transaction, gr.id_transaction_parent, wf.id_transaction_workflow, info.info_title, info.info_value, DATEDIFF(now(), info.info_value) AS aging
                    FROM (SELECT id_transaction, id_transaction_parent FROM tr_service WHERE id_transaction_parent IS NOT NULL AND date_deleted IS NULL ) gr 
                    JOIN (SELECT id_transaction, id_transaction_workflow FROM tr_service_workflow WHERE date_deleted IS NULL) wf ON wf.id_transaction=gr.id_transaction
                    JOIN (SELECT id_transaction_workflow, info_title, fn_str_to_date(info_value) AS info_value
                            FROM tr_service_workflow_info 
                            WHERE date_deleted IS NULL AND info_title LIKE 'return date' AND fn_str_to_date(info_value) IS NULL ) info ON info.id_transaction_workflow=wf.id_transaction_workflow
                    ) ret_date ON ret_date.id_transaction_parent=tr.id_transaction
              JOIN (SELECT gr.id_transaction, gr.id_transaction_parent, wf.id_transaction_workflow, info.info_title, info.info_value
                    FROM (SELECT id_transaction, id_transaction_parent FROM tr_service WHERE id_transaction_parent IS NOT NULL AND date_deleted IS NULL ) gr 
                    JOIN (SELECT id_transaction, id_transaction_workflow FROM tr_service_workflow WHERE date_deleted IS NULL) wf ON wf.id_transaction=gr.id_transaction
                    JOIN (SELECT id_transaction_workflow, info_title, info_value 
                            FROM tr_service_workflow_info 
                            WHERE date_deleted IS NULL AND info_title LIKE 'Payment voucher%') info ON info.id_transaction_workflow=wf.id_transaction_workflow
                    ) set_voucher ON set_voucher.id_transaction_parent=tr.id_transaction
              WHERE $where";
              
      return DB::select($query);
  }

  public static function complete_ticket($where){
      $query = "SELECT tr.id_transaction, transaction_code, service_name, description, service_price, glje_no, glje_date, invoice_no, invoice_date, date_finished
            FROM (
                  SELECT id_transaction, transaction_code, service_name, service_price, description, date_finished
                  FROM tr_service
                  WHERE id_status = 5
                  ) tr
            LEFT JOIN (
                  SELECT DISTINCT glje_no, g.date_updated AS glje_date, id_transaction
                  FROM tr_glje g
                  JOIN tr_glje_detail d ON g.id_glje=d.id_glje
                  ) g ON tr.id_transaction=g.id_transaction
            LEFT JOIN (
                  SELECT invoice_no, date_finalized AS invoice_date, id_transaction
                  FROM (SELECT id_billing, invoice_no, date_finalized FROM tr_billing WHERE date_finalized IS NOT NULL) b
                  JOIN tr_billing_detail d ON b.id_billing=d.id_billing
                  ) b ON b.id_transaction=tr.id_transaction
            WHERE $where";
      return DB::select($query);
  }

  public static function search_engine($where, $where2 = 't.id_transaction IS NOT NULL', $keyword = ''){
    $query = "SELECT id_transaction, transaction_code, service_name, agency_code_buyer, t.description, date_authorized, kode, t.id_status, status_name
            FROM (
                SELECT id_transaction, transaction_code, service_name, agency_code_buyer, description, date_authorized, id_status, group_concat(distinct kode order by kode ASC separator ', ' )  AS kode
                FROM (
                    SELECT id_transaction, transaction_code, service_name, agency_code_buyer, CONCAT(service_name, ' - ', description) AS description, 
                            date_authorized, id_status, 'service' AS kode
                    FROM tr_service
                    WHERE date_deleted IS NULL AND service_name LIKE '%$keyword%'
                    UNION
                    SELECT id_transaction, transaction_code, service_name, agency_code_buyer, CONCAT(service_name, ' - ', description) AS description, 
                            date_authorized, id_status, 'buyer' AS kode
                    FROM tr_service
                    WHERE date_deleted IS NULL AND person_name_buyer LIKE '%$keyword%'
                    UNION
                    SELECT id_transaction, transaction_code, service_name, agency_code_buyer, CONCAT(service_name, ' - ', description) AS description, 
                            date_authorized, id_status, 'short_desc'
                    FROM tr_service
                    WHERE date_deleted IS NULL AND description LIKE '%$keyword%'
                    UNION
                    SELECT tr.id_transaction, tr.transaction_code, tr.service_name, tr.agency_code_buyer, CONCAT(tr.service_name, ' - ', tr.description) AS description, 
                            tr.date_authorized, tr.id_status, 'info_value'
                    FROM (SELECT * FROM tr_service WHERE date_deleted IS NULL AND id_transaction_parent IS NULL) tr
                    JOIN (SELECT * FROM tr_service WHERE date_deleted IS NULL AND id_transaction_parent IS NOT NULL) gr ON tr.id_transaction=gr.id_transaction_parent
                    JOIN (SELECT * FROM tr_service_workflow WHERE date_deleted IS NULL) wf ON wf.id_transaction=gr.id_transaction
                    JOIN (SELECT * FROM tr_service_workflow_info WHERE date_deleted IS NULL AND info_value LIKE '%$keyword%') fo ON fo.id_transaction_workflow=wf.id_transaction_workflow
                    UNION
                    SELECT tr.id_transaction, tr.transaction_code, tr.service_name, tr.agency_code_buyer, CONCAT(tr.service_name, ' - ', tr.description) AS description, 
                            tr.date_authorized, tr.id_status, 'workflow'
                    FROM (SELECT * FROM tr_service WHERE date_deleted IS NULL AND id_transaction_parent IS NULL) tr
                    JOIN (SELECT * FROM tr_service WHERE date_deleted IS NULL AND id_transaction_parent IS NOT NULL) gr ON tr.id_transaction=gr.id_transaction_parent
                    JOIN (SELECT * FROM tr_service_workflow WHERE date_deleted IS NULL AND workflow_name LIKE '%$keyword%') wf ON wf.id_transaction=gr.id_transaction
                ) t
                WHERE true $where2
                GROUP BY id_transaction, transaction_code, service_name, agency_code_buyer, description, date_authorized, id_status
            ) t
            JOIN (SELECT * FROM ms_status WHERE date_deleted IS NULL) st ON t.id_status = st.id_status
            WHERE $where";
    return DB::select($query);
  }

  public static function service_workload($where){
    $query = "SELECT id_agency_unit, agency_unit_code, agency_unit_name, ag.id_status, status_name, COALESCE(COUNT(id_transaction), 0) AS cnt
                      FROM (SELECT id_agency_unit, agency_unit_code, agency_unit_name, id_status, status_name 
                            FROM (SELECT id_agency_unit, agency_unit_code, agency_unit_name 
                                  FROM ms_agency_unit 
                                  WHERE date_deleted IS NULL AND id_agency_unit IN (SELECT id_agency_unit FROM ms_agency_unit WHERE date_deleted IS NULL AND id_agency_unit_parent = 1 AND is_service_unit = 1) ) a
                            JOIN ms_status
                            ORDER BY id_status
                            ) ag
                      LEFT JOIN (SELECT * FROM tr_service WHERE date_deleted IS NULL AND $where) tr ON tr.id_status=ag.id_status AND tr.id_agency_unit_service=ag.id_agency_unit
                      GROUP BY id_agency_unit, agency_unit_code, agency_unit_name, ag.id_status, status_name";
    return DB::select($query);
  }

  public static function workload_analysis($where){
    $query = "SELECT u.id_user, u.person_name, a.agency_unit_name as unit_name, SUM(service) AS service, SUM(workflow) AS workflow
          FROM (
            SELECT completed_by AS user_name, COUNT(DISTINCT tr.id_transaction) AS service, COUNT(DISTINCT id_transaction_workflow) AS workflow
            FROM (SELECT id_transaction FROM tr_service WHERE id_transaction_parent IS NULL AND date_deleted IS NULL ) tr  
            JOIN (SELECT id_transaction, id_transaction_parent FROM tr_service WHERE id_transaction_parent IS NOT NULL AND date_deleted IS NULL ) gr ON gr.id_transaction_parent=tr.id_transaction
            JOIN (SELECT id_transaction, id_transaction_workflow, completed_by, date_start_actual, date_end_actual FROM tr_service_workflow WHERE id_agency_unit_pic IS NOT NULL AND date_deleted IS NULL AND id_agency_unit_pic IS NOT NULL AND date_end_actual IS NOT NULL AND $where) wf ON wf.id_transaction=gr.id_transaction
            GROUP BY completed_by
            ) t
           JOIN (SELECT id_user, user_name, person_name, id_agency_unit FROM sec_user WHERE date_deleted IS NULL) u ON u.user_name=t.user_name 
           JOIN (SELECT * FROM ms_agency_unit WHERE is_service_unit = 1 AND date_deleted IS NULL) a ON u.id_agency_unit=a.id_agency_unit
           WHERE t.user_name IS NOT NULL
          GROUP BY  u.id_user, u.person_name, a.agency_unit_name";
    return DB::select($query);
  }

  public static function performance($where){
    $query = "SELECT service_name, agency_unit_code,
                  SUM(if(service_rating IS NULL OR service_rating = 0, 1, 0)) AS rate_0,
                  SUM(if(service_rating=1, 1, 0)) AS rate_1,
                  SUM(if(service_rating=2, 1, 0)) AS rate_2,
                  SUM(if(service_rating=3, 1, 0)) AS rate_3,
                  SUM(if(service_rating=4, 1, 0)) AS rate_4,
                  SUM(if(service_rating=5, 1, 0)) AS rate_5,
                  COUNT(service_name) AS rate_t
          FROM (SELECT trim(service_name) AS service_name, id_agency_unit_service, service_rating, is_finished, id_status, date_finished, date_deleted FROM tr_service) t
          JOIN (SELECT id_agency_unit, agency_unit_code FROM ms_agency_unit WHERE id_agency_unit_parent IS NOT NULL AND date_deleted IS NULL) a ON a.id_agency_unit=t.id_agency_unit_service
          WHERE is_finished IS NOT NULL AND id_status in (5,6,7) AND date_deleted IS NULL AND $where
          GROUP BY service_name, agency_unit_code     ";
    return DB::select($query);
  }

  public static function timeliness_analysis_delay($where){
    $query = "SELECT id_agency_unit, agency_unit_code, agency_unit_name, COALESCE(workflow_name, '-') AS workflow_name, ag.type, id_type
                FROM (SELECT id_agency_unit, agency_unit_code, agency_unit_name, type, id_type
                      FROM ms_agency_unit 
                      JOIN (SELECT 1 AS id_type, 'frequent' AS type UNION SELECT 2, 'longest' UNION SELECT 3, 'ontime') a
                      WHERE date_deleted IS NULL AND id_agency_unit IN (2,3,4,5,6)
                      ) ag
                LEFT JOIN (
                  SELECT * FROM (
                    SELECT 'frequent' AS type, id_agency_unit_pic, workflow_name, SUM(delay) as cnt
                    FROM (
                      SELECT id_agency_unit_pic, wf.workflow_name, fn_get_number_workday(wf.date_end_estimated, wf.date_end_actual,false) > 0 AS delay
                      FROM (SELECT * FROM tr_service WHERE date_deleted IS NULL AND id_transaction_parent IS NULL AND date_authorized IS NOT NULL AND date_finished IS NOT NULL AND $where) tr
                      JOIN (SELECT id_transaction, id_transaction_parent FROM tr_service WHERE date_deleted IS NULL AND id_transaction_parent IS NOT NULL ) gr ON gr.id_transaction_parent=tr.id_transaction
                      JOIN (SELECT id_transaction_workflow, id_transaction, id_agency_unit_pic, workflow_name, date_end_estimated, COALESCE(date_end_actual, now() ) AS date_end_actual FROM tr_service_workflow WHERE id_agency_unit_pic = 2 AND date_deleted IS NULL AND date_start_actual IS NOT NULL) wf ON wf.id_transaction=gr.id_transaction
                      ) t
                    GROUP BY id_agency_unit_pic, workflow_name
                    ORDER BY cnt DESC
                    LIMIT 0, 1
                  ) t
                  UNION
                  SELECT * FROM (
                    SELECT 'longest', id_agency_unit_pic, workflow_name, delay
                    FROM (
                      SELECT id_agency_unit_pic, workflow_name, delay 
                      FROM (
                        SELECT id_agency_unit_pic, wf.workflow_name, fn_get_number_workday(wf.date_end_estimated, wf.date_end_actual,false) AS delay
                        FROM (SELECT * FROM tr_service WHERE date_deleted IS NULL AND id_transaction_parent IS NULL AND date_authorized IS NOT NULL AND date_finished IS NOT NULL AND $where) tr
                        JOIN (SELECT id_transaction, id_transaction_parent FROM tr_service WHERE date_deleted IS NULL AND id_transaction_parent IS NOT NULL ) gr ON gr.id_transaction_parent=tr.id_transaction
                        JOIN (SELECT id_transaction_workflow, id_transaction, id_agency_unit_pic, workflow_name, date_end_estimated, COALESCE(date_end_actual, now() ) AS date_end_actual FROM tr_service_workflow WHERE id_agency_unit_pic = 2 AND date_deleted IS NULL AND date_start_actual IS NOT NULL) wf ON wf.id_transaction=gr.id_transaction
                        ) t
                      ) t WHERE delay > 0
                    ORDER BY delay DESC
                    LIMIT 0, 1
                  ) t
                  UNION
                  SELECT * FROM (
                    SELECT 'ontime', id_agency_unit_pic, workflow_name, SUM(ontime) as cnt
                    FROM (
                      SELECT id_agency_unit_pic, wf.workflow_name, fn_get_number_workday(wf.date_end_estimated, wf.date_end_actual,false) <= 0 AS ontime
                      FROM (SELECT * FROM tr_service WHERE date_deleted IS NULL AND id_transaction_parent IS NULL AND date_authorized IS NOT NULL AND date_finished IS NOT NULL AND $where) tr
                      JOIN (SELECT id_transaction, id_transaction_parent FROM tr_service WHERE date_deleted IS NULL AND id_transaction_parent IS NOT NULL ) gr ON gr.id_transaction_parent=tr.id_transaction
                      JOIN (SELECT id_transaction_workflow, id_transaction, id_agency_unit_pic, workflow_name, date_end_estimated, COALESCE(date_end_actual, now() ) AS date_end_actual FROM tr_service_workflow WHERE id_agency_unit_pic = 2 AND date_deleted IS NULL AND date_start_actual IS NOT NULL AND sequence > 1) wf ON wf.id_transaction=gr.id_transaction
                      ) t
                    GROUP BY id_agency_unit_pic, workflow_name
                    ORDER BY cnt DESC
                    LIMIT 0, 1
                  ) t

                  UNION
                  SELECT * FROM (
                    SELECT 'frequent', id_agency_unit_pic, workflow_name, SUM(delay) as cnt
                    FROM (
                      SELECT id_agency_unit_pic, wf.workflow_name, fn_get_number_workday(wf.date_end_estimated, wf.date_end_actual,false) > 0 AS delay
                      FROM (SELECT * FROM tr_service WHERE date_deleted IS NULL AND id_transaction_parent IS NULL AND date_authorized IS NOT NULL AND date_finished IS NOT NULL AND $where) tr
                      JOIN (SELECT id_transaction, id_transaction_parent FROM tr_service WHERE date_deleted IS NULL AND id_transaction_parent IS NOT NULL ) gr ON gr.id_transaction_parent=tr.id_transaction
                      JOIN (SELECT id_transaction_workflow, id_transaction, id_agency_unit_pic, workflow_name, date_end_estimated, COALESCE(date_end_actual, now() ) AS date_end_actual FROM tr_service_workflow WHERE id_agency_unit_pic = 3 AND date_deleted IS NULL AND date_start_actual IS NOT NULL) wf ON wf.id_transaction=gr.id_transaction
                      ) t
                    GROUP BY id_agency_unit_pic, workflow_name
                    ORDER BY cnt DESC
                    LIMIT 0, 1
                  ) t
                  UNION
                  SELECT * FROM (
                    SELECT 'longest', id_agency_unit_pic, workflow_name, delay
                    FROM (
                      SELECT id_agency_unit_pic, wf.workflow_name, fn_get_number_workday(wf.date_end_estimated, wf.date_end_actual,false) AS delay
                      FROM (SELECT * FROM tr_service WHERE date_deleted IS NULL AND id_transaction_parent IS NULL AND date_authorized IS NOT NULL AND date_finished IS NOT NULL AND $where) tr
                      JOIN (SELECT id_transaction, id_transaction_parent FROM tr_service WHERE date_deleted IS NULL AND id_transaction_parent IS NOT NULL ) gr ON gr.id_transaction_parent=tr.id_transaction
                      JOIN (SELECT id_transaction_workflow, id_transaction, id_agency_unit_pic, workflow_name, date_end_estimated, COALESCE(date_end_actual, now() ) AS date_end_actual FROM tr_service_workflow WHERE id_agency_unit_pic = 3 AND date_deleted IS NULL AND date_start_actual IS NOT NULL) wf ON wf.id_transaction=gr.id_transaction
                      ) t
                    ORDER BY delay DESC
                    LIMIT 0, 1
                  ) t
                  UNION
                  SELECT * FROM (
                    SELECT 'ontime', id_agency_unit_pic, workflow_name, SUM(ontime) as cnt
                    FROM (
                      SELECT id_agency_unit_pic, wf.workflow_name, fn_get_number_workday(wf.date_end_estimated, wf.date_end_actual,false) <= 0 AS ontime
                      FROM (SELECT * FROM tr_service WHERE date_deleted IS NULL AND id_transaction_parent IS NULL AND date_authorized IS NOT NULL AND date_finished IS NOT NULL AND $where) tr
                      JOIN (SELECT id_transaction, id_transaction_parent FROM tr_service WHERE date_deleted IS NULL AND id_transaction_parent IS NOT NULL ) gr ON gr.id_transaction_parent=tr.id_transaction
                      JOIN (SELECT id_transaction_workflow, id_transaction, id_agency_unit_pic, workflow_name, date_end_estimated, COALESCE(date_end_actual, now() ) AS date_end_actual FROM tr_service_workflow WHERE id_agency_unit_pic = 3 AND date_deleted IS NULL AND date_start_actual IS NOT NULL AND sequence > 1) wf ON wf.id_transaction=gr.id_transaction
                      ) t
                    GROUP BY id_agency_unit_pic, workflow_name
                    ORDER BY cnt DESC
                    LIMIT 0, 1
                  ) t

                  UNION
                  SELECT * FROM (
                    SELECT 'frequent', id_agency_unit_pic, workflow_name, SUM(delay) as cnt
                    FROM (
                      SELECT id_agency_unit_pic, wf.workflow_name, fn_get_number_workday(wf.date_end_estimated, wf.date_end_actual,false) > 0 AS delay
                      FROM (SELECT * FROM tr_service WHERE date_deleted IS NULL AND id_transaction_parent IS NULL AND date_authorized IS NOT NULL AND date_finished IS NOT NULL AND $where) tr
                      JOIN (SELECT id_transaction, id_transaction_parent FROM tr_service WHERE date_deleted IS NULL AND id_transaction_parent IS NOT NULL ) gr ON gr.id_transaction_parent=tr.id_transaction
                      JOIN (SELECT id_transaction_workflow, id_transaction, id_agency_unit_pic, workflow_name, date_end_estimated, COALESCE(date_end_actual, now() ) AS date_end_actual FROM tr_service_workflow WHERE id_agency_unit_pic = 4 AND date_deleted IS NULL AND date_start_actual IS NOT NULL) wf ON wf.id_transaction=gr.id_transaction
                      ) t
                    GROUP BY id_agency_unit_pic, workflow_name
                    ORDER BY cnt DESC
                    LIMIT 0, 1
                  ) t
                  UNION
                  SELECT * FROM (
                    SELECT 'longest', id_agency_unit_pic, workflow_name, delay
                    FROM (
                      SELECT id_agency_unit_pic, wf.workflow_name, fn_get_number_workday(wf.date_end_estimated, wf.date_end_actual,false) AS delay
                      FROM (SELECT * FROM tr_service WHERE date_deleted IS NULL AND id_transaction_parent IS NULL AND date_authorized IS NOT NULL AND date_finished IS NOT NULL AND $where) tr
                      JOIN (SELECT id_transaction, id_transaction_parent FROM tr_service WHERE date_deleted IS NULL AND id_transaction_parent IS NOT NULL ) gr ON gr.id_transaction_parent=tr.id_transaction
                      JOIN (SELECT id_transaction_workflow, id_transaction, id_agency_unit_pic, workflow_name, date_end_estimated, COALESCE(date_end_actual, now() ) AS date_end_actual FROM tr_service_workflow WHERE id_agency_unit_pic = 4 AND date_deleted IS NULL AND date_start_actual IS NOT NULL) wf ON wf.id_transaction=gr.id_transaction
                      ) t
                    ORDER BY delay DESC
                    LIMIT 0, 1
                  ) t
                  UNION
                  SELECT * FROM (
                    SELECT 'ontime', id_agency_unit_pic, workflow_name, SUM(ontime) as cnt
                    FROM (
                      SELECT id_agency_unit_pic, wf.workflow_name, fn_get_number_workday(wf.date_end_estimated, wf.date_end_actual,false) <= 0 AS ontime
                      FROM (SELECT * FROM tr_service WHERE date_deleted IS NULL AND id_transaction_parent IS NULL AND date_authorized IS NOT NULL AND date_finished IS NOT NULL AND $where) tr
                      JOIN (SELECT id_transaction, id_transaction_parent FROM tr_service WHERE date_deleted IS NULL AND id_transaction_parent IS NOT NULL ) gr ON gr.id_transaction_parent=tr.id_transaction
                      JOIN (SELECT id_transaction_workflow, id_transaction, id_agency_unit_pic, workflow_name, date_end_estimated, COALESCE(date_end_actual, now() ) AS date_end_actual FROM tr_service_workflow WHERE id_agency_unit_pic = 4 AND date_deleted IS NULL AND date_start_actual IS NOT NULL AND sequence > 1) wf ON wf.id_transaction=gr.id_transaction
                      ) t
                    GROUP BY id_agency_unit_pic, workflow_name
                    ORDER BY cnt DESC
                    LIMIT 0, 1
                  ) t

                  UNION
                  SELECT * FROM (
                    SELECT 'frequent', id_agency_unit_pic, workflow_name, SUM(delay) as cnt
                    FROM (
                      SELECT id_agency_unit_pic, wf.workflow_name, fn_get_number_workday(wf.date_end_estimated, wf.date_end_actual,false) > 0 AS delay
                      FROM (SELECT * FROM tr_service WHERE date_deleted IS NULL AND id_transaction_parent IS NULL AND date_authorized IS NOT NULL AND date_finished IS NOT NULL AND $where) tr
                      JOIN (SELECT id_transaction, id_transaction_parent FROM tr_service WHERE date_deleted IS NULL AND id_transaction_parent IS NOT NULL ) gr ON gr.id_transaction_parent=tr.id_transaction
                      JOIN (SELECT id_transaction_workflow, id_transaction, id_agency_unit_pic, workflow_name, date_end_estimated, COALESCE(date_end_actual, now() ) AS date_end_actual FROM tr_service_workflow WHERE id_agency_unit_pic = 5 AND date_deleted IS NULL AND date_start_actual IS NOT NULL) wf ON wf.id_transaction=gr.id_transaction
                      ) t
                    GROUP BY id_agency_unit_pic, workflow_name
                    ORDER BY cnt DESC
                    LIMIT 0, 1
                  ) t
                  UNION
                  SELECT * FROM (
                    SELECT 'longest', id_agency_unit_pic, workflow_name, delay
                    FROM (
                      SELECT id_agency_unit_pic, wf.workflow_name, fn_get_number_workday(wf.date_end_estimated, wf.date_end_actual,false) AS delay
                      FROM (SELECT * FROM tr_service WHERE date_deleted IS NULL AND id_transaction_parent IS NULL AND date_authorized IS NOT NULL AND date_finished IS NOT NULL AND $where) tr
                      JOIN (SELECT id_transaction, id_transaction_parent FROM tr_service WHERE date_deleted IS NULL AND id_transaction_parent IS NOT NULL ) gr ON gr.id_transaction_parent=tr.id_transaction
                      JOIN (SELECT id_transaction_workflow, id_transaction, id_agency_unit_pic, workflow_name, date_end_estimated, COALESCE(date_end_actual, now() ) AS date_end_actual FROM tr_service_workflow WHERE id_agency_unit_pic = 5 AND date_deleted IS NULL AND date_start_actual IS NOT NULL) wf ON wf.id_transaction=gr.id_transaction
                      ) t
                    ORDER BY delay DESC
                    LIMIT 0, 1
                  ) t
                  UNION
                  SELECT * FROM (
                    SELECT 'ontime', id_agency_unit_pic, workflow_name, SUM(ontime) as cnt
                    FROM (
                      SELECT id_agency_unit_pic, wf.workflow_name, fn_get_number_workday(wf.date_end_estimated, wf.date_end_actual,false) <= 0 AS ontime
                      FROM (SELECT * FROM tr_service WHERE date_deleted IS NULL AND id_transaction_parent IS NULL AND date_authorized IS NOT NULL AND date_finished IS NOT NULL AND $where) tr
                      JOIN (SELECT id_transaction, id_transaction_parent FROM tr_service WHERE date_deleted IS NULL AND id_transaction_parent IS NOT NULL ) gr ON gr.id_transaction_parent=tr.id_transaction
                      JOIN (SELECT id_transaction_workflow, id_transaction, id_agency_unit_pic, workflow_name, date_end_estimated, COALESCE(date_end_actual, now() ) AS date_end_actual FROM tr_service_workflow WHERE id_agency_unit_pic = 5 AND date_deleted IS NULL AND date_start_actual IS NOT NULL AND sequence > 1) wf ON wf.id_transaction=gr.id_transaction
                      ) t
                    GROUP BY id_agency_unit_pic, workflow_name
                    ORDER BY cnt DESC
                    LIMIT 0, 1
                  ) t

                  UNION
                  SELECT * FROM (
                    SELECT 'frequent', id_agency_unit_pic, workflow_name, SUM(delay) as cnt
                    FROM (
                      SELECT id_agency_unit_pic, wf.workflow_name, fn_get_number_workday(wf.date_end_estimated, wf.date_end_actual,false) > 0 AS delay
                      FROM (SELECT * FROM tr_service WHERE date_deleted IS NULL AND id_transaction_parent IS NULL AND date_authorized IS NOT NULL AND date_finished IS NOT NULL AND $where) tr
                      JOIN (SELECT id_transaction, id_transaction_parent FROM tr_service WHERE date_deleted IS NULL AND id_transaction_parent IS NOT NULL ) gr ON gr.id_transaction_parent=tr.id_transaction
                      JOIN (SELECT id_transaction_workflow, id_transaction, id_agency_unit_pic, workflow_name, date_end_estimated, COALESCE(date_end_actual, now() ) AS date_end_actual FROM tr_service_workflow WHERE id_agency_unit_pic = 6 AND date_deleted IS NULL AND date_start_actual IS NOT NULL) wf ON wf.id_transaction=gr.id_transaction
                      ) t
                    GROUP BY id_agency_unit_pic, workflow_name
                    ORDER BY cnt DESC
                    LIMIT 0, 1
                  ) t
                  UNION
                  SELECT * FROM (
                    SELECT 'longest', id_agency_unit_pic, workflow_name, delay
                    FROM (
                      SELECT id_agency_unit_pic, wf.workflow_name, fn_get_number_workday(wf.date_end_estimated, wf.date_end_actual,false) AS delay
                      FROM (SELECT * FROM tr_service WHERE date_deleted IS NULL AND id_transaction_parent IS NULL AND date_authorized IS NOT NULL AND date_finished IS NOT NULL AND $where) tr
                      JOIN (SELECT id_transaction, id_transaction_parent FROM tr_service WHERE date_deleted IS NULL AND id_transaction_parent IS NOT NULL ) gr ON gr.id_transaction_parent=tr.id_transaction
                      JOIN (SELECT id_transaction_workflow, id_transaction, id_agency_unit_pic, workflow_name, date_end_estimated, COALESCE(date_end_actual, now() ) AS date_end_actual FROM tr_service_workflow WHERE id_agency_unit_pic = 6 AND date_deleted IS NULL AND date_start_actual IS NOT NULL) wf ON wf.id_transaction=gr.id_transaction
                      ) t
                    ORDER BY delay DESC
                    LIMIT 0, 1
                  ) t
                  UNION
                  SELECT * FROM (
                    SELECT 'ontime', id_agency_unit_pic, workflow_name, SUM(ontime) as cnt
                    FROM (
                      SELECT id_agency_unit_pic, wf.workflow_name, fn_get_number_workday(wf.date_end_estimated, wf.date_end_actual,false) <= 0 AS ontime
                      FROM (SELECT * FROM tr_service WHERE date_deleted IS NULL AND id_transaction_parent IS NULL AND date_authorized IS NOT NULL AND date_finished IS NOT NULL AND $where) tr
                      JOIN (SELECT id_transaction, id_transaction_parent FROM tr_service WHERE date_deleted IS NULL AND id_transaction_parent IS NOT NULL ) gr ON gr.id_transaction_parent=tr.id_transaction
                      JOIN (SELECT id_transaction_workflow, id_transaction, id_agency_unit_pic, workflow_name, date_end_estimated, COALESCE(date_end_actual, now() ) AS date_end_actual FROM tr_service_workflow WHERE id_agency_unit_pic = 6 AND date_deleted IS NULL AND date_start_actual IS NOT NULL AND sequence > 1) wf ON wf.id_transaction=gr.id_transaction
                      ) t
                    GROUP BY id_agency_unit_pic, workflow_name
                    ORDER BY cnt DESC
                    LIMIT 0, 1
                   ) t
                  ) t ON t.id_agency_unit_pic=ag.id_agency_unit AND t.type=ag.type
                ";

    return DB::select($query);
  }
}