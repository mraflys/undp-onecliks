<?php
namespace App;

use DB;

class RequestQuery
{
    public static function ongoing($where = null)
    {
        $where = is_null($where) ? "date_deleted IS NULL" : $where;

        $query = "SELECT  tr.id_transaction, tr.transaction_code, tr.agency_name_buyer, tr.id_user_buyer, tr.person_name_buyer, tr.service_name, tr.date_authorized, tr.id_agency_unit_service, tr.comment, su.agency_unit_name AS agency_name_service, tr.id_status, tr.date_transaction, tr.service_price, tr.description, status_name,
                    '-' AS date_finished, '-' AS workflow_name, '-' AS date_end_estimated, '-' AS date_start_actual, 0 AS id_transaction_workflow, '-' AS id_agency_unit_pic,
                    a.id_agency_unit, a.agency_unit_name AS parent_agency_name, c.country_name, 0 AS delay, 0 AS id_user_pic_primary, 0 AS id_user_pic_alternate,
                    '-' AS person_name_primary, '-' AS person_name_alternate, 0 AS sequence, '-' AS date_rating, p.id_project, p.project_code, p.project_name
            FROM (SELECT * FROM tr_service WHERE date_deleted IS NULL AND id_status = -1 AND $where) tr
            JOIN (SELECT * FROM ms_agency_unit WHERE date_deleted IS NULL) su ON tr.id_agency_unit_service = su.id_agency_unit
            JOIN (SELECT * FROM ms_agency_unit WHERE date_deleted IS NULL) a ON su.id_agency_unit_parent=a.id_agency_unit
            JOIN (SELECT id_country, country_name FROM ms_country WHERE date_deleted IS NULL) c ON a.id_country=c.id_country
            JOIN ms_status st ON st.id_status=tr.id_status
            LEFT JOIN (SELECT id_project, project_code, project_name FROM ms_project) p ON tr.id_project=p.id_project
            UNION
            SELECT  tr.id_transaction, tr.transaction_code, tr.agency_name_buyer, tr.id_user_buyer, tr.person_name_buyer, tr.service_name, tr.date_authorized,
                    tr.id_agency_unit_service, tr.comment, su.agency_unit_name AS agency_name_service, tr.id_status, tr.date_transaction, tr.service_price, tr.description, status_name, tr.date_finished, '-' AS workflow_name, '-' AS date_end_estimated, '-' AS date_start_actual, 0 AS id_transaction_workflow, '-' AS id_agency_unit_pic, a.id_agency_unit, a.agency_unit_name AS parent_agency_name, c.country_name, fn_get_number_workday( date_end_estimated, tr.date_finished, false) AS is_delay, 0 AS id_user_pic_primary, 0 AS id_user_pic_alternate, '-' AS person_name_primary, '-' AS person_name_alternate, 0 AS sequence,
                    tr.date_rating, p.id_project, p.project_code, p.project_name
            FROM (SELECT * FROM tr_service WHERE date_deleted IS NULL AND id_status IN (5, 6, 7) AND date_rating IS NULL AND $where) tr
            JOIN (SELECT * FROM ms_agency_unit WHERE date_deleted IS NULL) su ON tr.id_agency_unit_service = su.id_agency_unit
            JOIN (SELECT * FROM ms_agency_unit WHERE date_deleted IS NULL) a ON su.id_agency_unit_parent=a.id_agency_unit
            JOIN (SELECT id_country, country_name FROM ms_country WHERE date_deleted IS NULL) c ON a.id_country=c.id_country
            LEFT JOIN (SELECT * FROM ms_status WHERE date_deleted IS NULL) st ON st.id_status=tr.id_status
            JOIN (
                  SELECT g.id_transaction_parent, MAX(date_end_estimated) AS date_end_estimated
                  FROM (SELECT * FROM tr_service_workflow WHERE date_deleted IS NULL) wf
                  JOIN (SELECT * FROM tr_service WHERE transaction_code IS NULL AND date_deleted IS NULL) g ON g.id_transaction=wf.id_transaction
                  GROUP BY g.id_transaction_parent
                  ) g ON g.id_transaction_parent=tr.id_transaction
            LEFT JOIN (SELECT id_project, project_code, project_name FROM ms_project) p ON tr.id_project=p.id_project
            UNION
            SELECT  tr.id_transaction, tr.transaction_code, tr.agency_name_buyer, tr.id_user_buyer, tr.person_name_buyer, tr.service_name, tr.date_authorized,
                    tr.id_agency_unit_service, tr.comment, su.agency_unit_name AS agency_name_service, tr.id_status, tr.date_transaction, tr.service_price, tr.description, status_name, '-' AS date_finished, wf.workflow_name, date_end_estimated, date_start_actual, id_transaction_workflow, wf.id_agency_unit_pic,
                    a.id_agency_unit, a.agency_unit_name AS parent_agency_name, c.country_name, fn_get_number_workday(date_end_estimated, now(), false) AS delay,
                    wf.id_user_pic_primary, wf.id_user_pic_alternate, COALESCE(u1.person_name, '-') AS person_name_primary, COALESCE(u2.person_name, '-')  AS person_name_alternate, wf.sequence, '-' AS date_rating, p.id_project, p.project_code, p.project_name
            FROM (SELECT * FROM tr_service WHERE date_deleted IS NULL AND id_status IN (1, 2) AND $where) tr
            JOIN (SELECT * FROM ms_agency_unit WHERE date_deleted IS NULL) su ON tr.id_agency_unit_service = su.id_agency_unit
            JOIN (SELECT * FROM ms_agency_unit WHERE date_deleted IS NULL) a ON su.id_agency_unit_parent=a.id_agency_unit
            JOIN (SELECT id_country, country_name FROM ms_country WHERE date_deleted IS NULL) c ON a.id_country=c.id_country
            JOIN (
                  SELECT id_transaction_parent, workflow_name, date_end_estimated, date_start_actual, id_transaction_workflow, id_agency_unit_pic,
                  id_user_pic_primary, id_user_pic_alternate, wf.sequence
                  FROM (SELECT * FROM tr_service_workflow WHERE date_start_actual IS NOT NULL AND date_end_actual IS NULL AND date_deleted IS NULL) wf
                  JOIN (SELECT * FROM tr_service WHERE transaction_code IS NULL AND date_deleted IS NULL) g ON g.id_transaction=wf.id_transaction
                  ) wf ON wf.id_transaction_parent=tr.id_transaction
            JOIN ms_status st ON st.id_status=tr.id_status
            LEFT JOIN (SELECT * FROM sec_user WHERE date_deleted IS NULL) u1 ON u1.id_user=wf.id_user_pic_primary
            LEFT JOIN (SELECT * FROM sec_user WHERE date_deleted IS NULL) u2 ON u2.id_user=wf.id_user_pic_alternate
            LEFT JOIN (SELECT id_project, project_code, project_name FROM ms_project) p ON tr.id_project=p.id_project";

        return DB::select($query);
    }

    public static function restore($where = null)
    {

        $query = "SELECT ts.id_transaction,ts.description, ts.transaction_code, ts.date_created, ts.person_name_buyer, ts.service_name, st.status_name, mau.agency_unit_name FROM tr_service ts JOIN ms_agency_unit mau ON mau.id_agency_unit = ts.id_agency_unit_service JOIN ms_status st ON st.id_status=ts.id_status WHERE ((ts.id_status in (-1,3,5,6) and (ts.date_deleted IS NOT NULL or ts.date_deleted IS NULL))) AND ts.id_transaction_parent IS NULL ORDER BY ts.date_created DESC";

        return DB::select($query);
    }

    public static function ongoing_home($where = null, $additional_where = null)
    {
        $where            = $where == null ? "id_status = 2" : $where;
        $additional_where = $additional_where == null ? " id_transaction IS NOT NULL " : $additional_where;

        $query = "SELECT id_transaction, transaction_code, agency_name_buyer, id_user_buyer, user_name_buyer, person_name_buyer, service_name, date_authorized, id_agency_unit_service,
                                agency_name_service, email, id_status, date_transaction, service_price, description, all_notif_email, CONCAT(person_name_buyer, '-' , agency_name_buyer) AS requester,
                                workflow_name, date_end_estimated, date_start_actual, IF(delay <= 0, 'On Track', 'Delayed') AS status_name, id_transaction_workflow, id_agency_unit_pic,
                                id_agency_unit, parent_agency_name, country_image_path, IF(delay <= 0, 0, 1) AS is_delay, IF(delay <= 0, '', 'delay') AS class,
                                delay,
                                id_user_pic_primary, id_user_pic_alternate,
                                user_name_primary, user_name_alternate,
                                person_name_primary, person_name_alternate
                        FROM (
                        SELECT  tr.id_transaction, tr.transaction_code, tr.agency_name_buyer, tr.id_user_buyer, tr.user_name_buyer, tr.person_name_buyer, tr.service_name, tr.date_authorized, tr.id_agency_unit_service,
                                su.agency_unit_name AS agency_name_service, su.email, tr.id_status, tr.date_transaction, IF (tr.is_free_of_charge = 1, 0, tr.service_price) AS service_price, tr.description, tr.all_notif_email,
                                wf.workflow_name, date_end_estimated, date_start_actual, id_transaction_workflow, wf.id_agency_unit_pic,
                                a.id_agency_unit, a.agency_unit_name AS parent_agency_name, c.country_image_path,
                                fn_get_number_workday(date_end_estimated, now(), false) AS delay,
                                wf.id_user_pic_primary, wf.id_user_pic_alternate,
                                COALESCE(u1.person_name, '-') AS person_name_primary, COALESCE(u2.person_name, '-') AS person_name_alternate,
                                COALESCE(u1.user_name, '-') AS user_name_primary, COALESCE(u2.user_name, '-') AS user_name_alternate
                        FROM (SELECT * FROM tr_service WHERE $where) tr
                        JOIN (SELECT * FROM ms_agency_unit WHERE date_deleted IS NULL) su ON tr.id_agency_unit_service = su.id_agency_unit
                        JOIN (SELECT * FROM ms_agency_unit WHERE date_deleted IS NULL) a ON su.id_agency_unit_parent=a.id_agency_unit
                        JOIN (SELECT * FROM ms_country WHERE date_deleted IS NULL) c ON a.id_country=c.id_country
                        JOIN (
                                SELECT id_transaction_parent, workflow_name, date_end_estimated, date_start_actual, id_transaction_workflow, id_agency_unit_pic,
                                id_user_pic_primary, id_user_pic_alternate
                                FROM (SELECT * FROM tr_service_workflow WHERE date_start_actual IS NOT NULL AND date_end_actual IS NULL AND date_deleted IS NULL) wf
                                JOIN (SELECT * FROM tr_service WHERE transaction_code IS NULL AND date_deleted IS NULL) g ON g.id_transaction=wf.id_transaction
                                ) wf ON wf.id_transaction_parent=tr.id_transaction
                        JOIN ms_status st ON st.id_status=tr.id_status
                        LEFT JOIN (SELECT * FROM sec_user WHERE date_deleted IS NULL) u1 ON u1.id_user=wf.id_user_pic_primary
                        LEFT JOIN (SELECT * FROM sec_user WHERE date_deleted IS NULL) u2 ON u2.id_user=wf.id_user_pic_alternate
                        ) t WHERE $additional_where";

        return DB::select($query);
    }

    public static function document_list_old($where = null, $additional_where = null)
    {
        $where            = $where == null ? "id_status = 2" : $where;
        $additional_where = $additional_where == null ? " id_transaction IS NOT NULL " : $additional_where;

        $query = "SELECT id_transaction, transaction_code, agency_name_buyer, id_user_buyer, user_name_buyer, person_name_buyer, service_name, date_authorized, id_agency_unit_service,
                                agency_name_service, email, id_status, date_transaction, service_price, description, all_notif_email, CONCAT(person_name_buyer, '-' , agency_name_buyer) AS requester,
                                workflow_name, date_end_estimated, date_start_actual, IF(delay <= 0, 'On Track', 'Delayed') AS status_name, id_transaction_workflow, id_agency_unit_pic,
                                id_agency_unit, parent_agency_name, country_image_path, IF(delay <= 0, 0, 1) AS is_delay, IF(delay <= 0, '', 'delay') AS class,
                                delay,
                                id_user_pic_primary, id_user_pic_alternate,
                                user_name_primary, user_name_alternate,
                                person_name_primary, person_name_alternate
                        FROM (
                        SELECT  tr.id_transaction, tr.transaction_code, tr.agency_name_buyer, tr.id_user_buyer, tr.user_name_buyer, tr.person_name_buyer, tr.service_name, tr.date_authorized, tr.id_agency_unit_service,
                                su.agency_unit_name AS agency_name_service, su.email, tr.id_status, tr.date_transaction, IF (tr.is_free_of_charge = 1, 0, tr.service_price) AS service_price, tr.description, tr.all_notif_email,
                                wf.workflow_name, date_end_estimated, date_start_actual, id_transaction_workflow, wf.id_agency_unit_pic,
                                a.id_agency_unit, a.agency_unit_name AS parent_agency_name, c.country_image_path,
                                fn_get_number_workday(date_end_estimated, now(), false) AS delay,
                                wf.id_user_pic_primary, wf.id_user_pic_alternate,
                                COALESCE(u1.person_name, '-') AS person_name_primary, COALESCE(u2.person_name, '-') AS person_name_alternate,
                                COALESCE(u1.user_name, '-') AS user_name_primary, COALESCE(u2.user_name, '-') AS user_name_alternate
                        FROM (SELECT * FROM tr_service WHERE $where) tr
                        JOIN (SELECT * FROM ms_agency_unit WHERE date_deleted IS NULL) su ON tr.id_agency_unit_service = su.id_agency_unit
                        JOIN (SELECT * FROM ms_agency_unit WHERE date_deleted IS NULL) a ON su.id_agency_unit_parent=a.id_agency_unit
                        JOIN (SELECT * FROM ms_country WHERE date_deleted IS NULL) c ON a.id_country=c.id_country
                        JOIN (
                                SELECT id_transaction_parent, workflow_name, date_end_estimated, date_start_actual, id_transaction_workflow, id_agency_unit_pic,
                                id_user_pic_primary, id_user_pic_alternate
                                FROM (SELECT * FROM tr_service_workflow WHERE date_start_actual IS NOT NULL AND date_end_actual IS NULL AND date_deleted IS NULL) wf
                                JOIN (SELECT * FROM tr_service WHERE transaction_code IS NULL AND date_deleted IS NULL) g ON g.id_transaction=wf.id_transaction
                                ) wf ON wf.id_transaction_parent=tr.id_transaction
                        JOIN ms_status st ON st.id_status=tr.id_status
                        LEFT JOIN (SELECT * FROM sec_user WHERE date_deleted IS NULL) u1 ON u1.id_user=wf.id_user_pic_primary
                        LEFT JOIN (SELECT * FROM sec_user WHERE date_deleted IS NULL) u2 ON u2.id_user=wf.id_user_pic_alternate
                        ) t WHERE $additional_where";

        return DB::select($query);
    }

    public static function document_list($where = null, $additional_where = null)
    {

        $query = "SELECT ts.id_transaction,ts.description, ts.transaction_code, ts.date_created, ts.person_name_buyer, ts.service_name, st.status_name, mau.agency_unit_name FROM tr_service ts JOIN ms_agency_unit mau ON mau.id_agency_unit = ts.id_agency_unit_service JOIN ms_status st ON st.id_status=ts.id_status WHERE $additional_where ((ts.id_status in (-1,0,1,2,3,5,6) and (ts.date_deleted IS NOT NULL or ts.date_deleted IS NULL))) AND ts.id_transaction_parent IS NULL ORDER BY ts.date_created DESC";

        return DB::select($query);
    }
}
