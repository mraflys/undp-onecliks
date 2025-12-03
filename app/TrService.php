<?php
namespace App;

use App\Country;
use App\TrServiceWorkFlow;
use App\TrServiceWorkFlowDoc;
use App\TrServiceWorkFlowInfo;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrService extends Model
{
    use SoftDeletes;
    protected $table      = 'tr_service';
    protected $primaryKey = 'id_transaction';
    const UPDATED_AT      = 'date_updated';
    const DELETED_AT      = 'date_deleted';
    const CREATED_AT      = 'date_created';

    public function currency()
    {
        return $this->belongsTo('App\Currency', 'id_currency');
    }

    public function parent()
    {
        return $this->belongsTo('App\TrService', 'id_transaction_parent');
    }

    public static function parent_service($id)
    {
        // $idservice = self::where('id_transaction_parent',$id)->pluck('id_service')->toArray();
        $idservice = ServiceList::where('id_service_parent', $id)->pluck('id_service')->toArray();
        return $idservice;
    }

    public static function get_ticket_number_by_country_id($country_id, $maxRetries = 3)
    {
        $attempt = 0;

        do {
            $attempt++;

            try {
                $country_code     = Country::find($country_id)->country_code;
                $counter          = Country::get_last_sequence_by_code($country_code);
                $transaction_code = "T" . strtoupper($country_code) . str_pad($counter, 10, "0", STR_PAD_LEFT);

                // Double check uniqueness to prevent extreme race conditions
                $exists = self::where('transaction_code', $transaction_code)->exists();

                if (! $exists) {
                    return $transaction_code;
                }

                // Log duplicate attempt for monitoring
                \Log::warning("Duplicate transaction code detected: {$transaction_code}, attempt: {$attempt}");

            } catch (\Exception $e) {
                \Log::error("Error generating transaction code: " . $e->getMessage());

                if ($attempt >= $maxRetries) {
                    throw new \Exception("Failed to generate unique transaction code after {$maxRetries} attempts: " . $e->getMessage());
                }
            }

            // Wait before retry with exponential backoff + random jitter
            if ($attempt < $maxRetries) {
                $waitTime = pow(2, $attempt - 1) * 100000; // 100ms, 200ms, 400ms
                usleep($waitTime + rand(0, 100000));       // Add random jitter
            }

        } while ($attempt < $maxRetries);

        throw new \Exception("Failed to generate unique transaction code after {$maxRetries} attempts");
    }

    public static function basic_mapping_data($custom_select = null)
    {
        if (empty($custom_select)) {
            $select = "DISTINCT(tr_service.id_transaction), tr_service.service_name, tr_service.description, tr_service.person_name_buyer, tr_service.service_rating, tr_service.transaction_code, tr_service.id_project,tr_service.date_authorized, tr_service.date_finished, tr_service.id_status, tr_service.comment, tr_service.agency_name_buyer,agency_parent.agency_unit_name as agency_parent_name, status_name, service_category.agency_unit_name as service_category_name, country_name, fn_get_number_workday(date_end_estimated, now(), false) AS delay_duration, tsw.workflow_name";
        } else {
            $select = $custom_select;
        }

        return self::select(DB::raw($select))
            ->join("ms_agency_unit as agency_parent", "agency_parent.id_agency_unit", "=", "tr_service.id_agency_unit_service", "INNER")
            ->join("ms_agency_unit as service_category", "service_category.id_agency_unit", "=", "agency_parent.id_agency_unit_parent", "INNER")
            ->join("ms_country", "ms_country.id_country", "=", "agency_parent.id_country", "INNER")
            ->join("ms_status", "ms_status.id_status", "=", "tr_service.id_status", "INNER")
            ->join("tr_service as ts_child", "ts_child.id_transaction_parent", "=", "tr_service.id_transaction", "INNER")
            ->join("tr_service_workflow as tsw", "ts_child.id_transaction", "=", "tsw.id_transaction", "INNER");
    }

    public static function ongoing_search($filters, $additional_where = null)
    {
        $where = "tsw.date_start_actual IS NOT NULL AND tr_service.id_transaction_parent IS NULL";
        if (isset($filters['with_workflow_begin_and_end'])) {
            $where .= " AND tsw.date_start_actual IS NOT NULL AND tsw.date_end_actual IS NULL";
        }
        if (isset($filters['id_agency_unit_service'])) {
            $where .= " AND tr_service.id_agency_unit_service IN (" . $filters['id_agency_unit_service'] . ")";
        }
        if (isset($filters['id_agency_unit_buyer'])) {
            $filter_by_agency_and_username = " tr_service.id_agency_unit_buyer IN (" . $filters['id_agency_unit_buyer'] . ")";
            if (isset($filters['user_name_buyer'])) {
                $filter_by_agency_and_username .= " OR tr_service.user_name_buyer IN ('" . $filters['user_name_buyer'] . "')";
            }
            $where .= " AND (" . $filter_by_agency_and_username . ")";
        }
        if (isset($filters['id_status'])) {
            $where .= " AND tr_service.id_status IN (" . $filters['id_status'] . ")";
        }
        if (isset($filters['with_rating_only'])) {
            $where .= " AND (tr_service.date_rating IS NOT NULL OR tr_service.id_status=3)";
        }
        // echo $where;exit;

        return self::basic_mapping_data()->whereRaw($where);
    }

    public static function history_search($filters, $additional_where = null)
    {
        $where = "WHERE tr_service.date_deleted IS NULL";

        if (isset($filters['id_agency_unit_buyer'])) {
            $filter_by_agency_and_username = " tr_service.id_agency_unit_buyer IN (" . $filters['id_agency_unit_buyer'] . ")";
            if (isset($filters['user_name_buyer'])) {
                $filter_by_agency_and_username .= " OR tr_service.user_name_buyer IN ('" . $filters['user_name_buyer'] . "')";
            }
            $where .= " AND (" . $filter_by_agency_and_username . ")";
        }
        $start_date = isset($filters['start_date']) ? $filters['start_date'] : null;
        $end_date   = isset($filters['end_date']) ? $filters['end_date'] : null;

        if (! is_null($start_date) && ! is_null($end_date)) {
            $where .= " AND (DATE(tr_service.date_authorized) BETWEEN '" . $start_date . "' AND '" . $end_date . "')";
        }

        if (isset($filters['id_service_unit'])) {
            $where .= " AND tr_service.id_agency_unit_service =" . $filters['id_service_unit'];
        }

        if (isset($filters['id_agency_unit_service'])) {
            $where .= " AND tr_service.id_agency_unit_service IN (" . $filters['id_agency_unit_service'] . ")";
        }

        if (isset($filters['id_status'])) {
            $where .= " AND tr_service.id_status IN (" . $filters['id_status'] . ")";
        }

        if (isset($filters['rating']) && $filters['rating'] > 0) {
            $where .= " AND tr_service.service_rating IN (" . $filters['rating'] . ")";
        }

        if (isset($filters['with_rating_only'])) {
            $where .= " AND (tr_service.date_rating IS NOT NULL OR tr_service.id_status=5)";
        }

        $query = "
            SELECT  tr.id_transaction, tr.transaction_code, tr.agency_name_buyer, tr.person_name_buyer, tr.service_name, tr.date_authorized,
                    tr.id_agency_unit_service, su.agency_unit_name AS agency_name_service, tr.id_status, tr.date_transaction, tr.date_finished,
                    tr.qty, IF (tr.is_free_of_charge = 1, 0, tr.service_price) AS service_price, tr.description, tr.date_rating,
                    IF (tr.id_status = 3, 'Rejected', IF (tr.id_status = 6, 'Cancelled', COALESCE(status_name,0))) AS status_name,
                    IF (tr.id_status = 3, -3, IF (tr.id_status = 6, -6, COALESCE(tr.service_rating,0))) AS service_rating,
                    a.id_agency_unit, a.agency_unit_name AS parent_agency_name, c.country_image_path, c.country_name,
                    date_end_estimated,
                    IF(tr.date_finished > date_end_estimated, 1, 0) AS is_delay,
                    fn_get_number_workday(date_end_estimated, tr.date_finished, false) AS delay_duration
            FROM (SELECT * FROM tr_service $where) tr
            JOIN (SELECT * FROM ms_agency_unit WHERE date_deleted IS NULL) su ON tr.id_agency_unit_service = su.id_agency_unit
            JOIN (SELECT * FROM ms_agency_unit WHERE date_deleted IS NULL) a ON su.id_agency_unit_parent=a.id_agency_unit
            JOIN (SELECT * FROM ms_country WHERE date_deleted IS NULL) c ON a.id_country=c.id_country
            LEFT JOIN (SELECT * FROM ms_status WHERE date_deleted IS NULL) st ON st.id_status=tr.id_status
            JOIN (
                  SELECT g.id_transaction_parent, MAX(date_end_estimated) AS date_end_estimated
                  FROM (SELECT * FROM tr_service_workflow WHERE date_deleted IS NULL) wf
                  JOIN (SELECT * FROM tr_service WHERE transaction_code IS NULL AND date_deleted IS NULL) g ON g.id_transaction=wf.id_transaction
                  GROUP BY g.id_transaction_parent
                  ) g ON g.id_transaction_parent=tr.id_transaction
            ";
        return DB::select($query);
    }

    public static function tracking($filters = [])
    {
        $where = "WHERE date_deleted IS NULL";
        if (isset($filters['id_agency_unit_service'])) {
            $where .= " AND id_agency_unit_service IN (" . $filters['id_agency_unit_service'] . ")";
        }
        if (isset($filters['id_agency_unit_buyer']) && $filters['id_agency_unit_buyer'] > 0) {
            $filter_by_agency_and_username = " id_agency_unit_buyer IN (" . $filters['id_agency_unit_buyer'] . ")";
            if (isset($filters['user_name_buyer'])) {
                $filter_by_agency_and_username .= " OR user_name_buyer IN ('" . $filters['user_name_buyer'] . "')";
            }
            $where .= " AND (" . $filter_by_agency_and_username . ")";
        }
        if (isset($filters['id_status'])) {
            $where .= " AND id_status IN (" . $filters['id_status'] . ")";
        }

        if (isset($filters['transaction_code']) && ! empty($filters['transaction_code'])) {
            $where .= " AND transaction_code LIKE '%" . $filters['transaction_code'] . "%'";
        }

        $query = "SELECT  tr.id_transaction, tr.transaction_code, tr.agency_name_buyer, tr.person_name_buyer, tr.service_name, tr.date_authorized, tr.id_status, tr.date_transaction, status_name, tr.comment,
            tr.description, wf.workflow_name, date_end_estimated, date_start_actual, date_end_actual, wf.sequence, tr.id_agency_unit_service,
            a.id_agency_unit, a.agency_unit_name AS parent_agency_name, su.agency_unit_name AS agency_name_service, c.country_image_path,
            tr.id_agency_unit_buyer, IF(date_end_actual IS NULL, IF(date_start_actual IS NULL, 0, fn_get_number_workday(date_end_estimated, now(), false)), fn_get_number_workday(date_end_estimated, date_end_actual, false)) AS delay
            FROM (SELECT * FROM tr_service $where) tr
            JOIN (SELECT * FROM tr_service WHERE date_deleted IS NULL AND id_transaction_parent IS NOT NULL) gr ON gr.id_transaction_parent=tr.id_transaction
            JOIN ms_agency_unit su ON tr.id_agency_unit_service = su.id_agency_unit
            JOIN ms_agency_unit a ON su.id_agency_unit_parent=a.id_agency_unit
            JOIN ms_country c ON a.id_country=c.id_country
            JOIN tr_service_workflow wf ON wf.id_transaction=gr.id_transaction
            JOIN ms_status st ON st.id_status=tr.id_status";

        return DB::select($query);
    }

    public function workflows()
    {
        return $this->hasMany("App\TrServiceWorkFlow", "id_transaction");
    }

    public function workflow()
    {
        return $this->hasOne("App\TrServiceWorkFlow", "id_transaction");
    }

    public function childs()
    {
        return $this->hasMany("App\TrService", "id_transaction_parent");
    }

    public function comments()
    {
        $id_transactions = DB::table('tr_service')->select('id_transaction')->where('id_transaction_parent', $this->id_transaction)->get()->pluck('id_transaction');
        return DB::table("tr_comment")->select(DB::raw("comment, workflow_name, tr_comment.created_by, tr_comment.date_created, tr_comment.type"))
            ->join('tr_service_workflow', 'tr_service_workflow.id_transaction_workflow', '=', 'tr_comment.id_transaction_workflow')
            ->whereIn('tr_comment.id_transaction', $id_transactions)
            ->orderBy('tr_comment.date_created')
            ->get();
    }

    public function required_docs()
    {
        return TrServiceWorkFlowDoc::select(DB::raw("tr_service_workflow_doc.*"))
            ->join("tr_service_workflow", "tr_service_workflow.id_transaction_workflow", "=", "tr_service_workflow_doc.id_transaction_workflow")
            ->join('tr_service as tr_service_child', 'tr_service_child.id_transaction', '=', 'tr_service_workflow.id_transaction')
            ->join('tr_service', 'tr_service.id_transaction', '=', 'tr_service_child.id_transaction_parent')
            ->where('tr_service.id_transaction', $this->id_transaction)->orderBy('id_transaction_workflow_doc', 'ASC')->get();
    }

    public function required_infos()
    {
        return TrServiceWorkFlowInfo::select(DB::raw("tr_service_workflow_info.*"))
            ->join("tr_service_workflow", "tr_service_workflow.id_transaction_workflow", "=", "tr_service_workflow_info.id_transaction_workflow")
            ->join('tr_service as tr_service_child', 'tr_service_child.id_transaction', '=', 'tr_service_workflow.id_transaction')
            ->join('tr_service', 'tr_service.id_transaction', '=', 'tr_service_child.id_transaction_parent')
            ->where('tr_service.id_transaction', $this->id_transaction)->orderBy('id_transaction_workflow_info', 'ASC')->get();
    }

    public function service_workflows()
    {
        return TrServiceWorkFlow::select("tr_service_workflow.*")
            ->join("tr_service as tr_service_child", "tr_service_child.id_transaction", "=", "tr_service_workflow.id_transaction")
            ->join("tr_service", "tr_service.id_transaction", "=", "tr_service_child.id_transaction_parent")->with('docs', 'primary_pic', 'alternate_pic')
            ->where('tr_service.id_transaction', $this->id_transaction)->orderBy('sequence')->get();
    }

    public function service_workflows_history()
    {
        return TrServiceWorkFlow::select("tr_service_workflow.*")
            ->join("tr_service as tr_service_child", "tr_service_child.id_transaction", "=", "tr_service_workflow.id_transaction")
            ->join("tr_service", "tr_service.id_transaction", "=", "tr_service_child.id_transaction_parent")->with('docs', 'primary_pic', 'alternate_pic')
            ->where('tr_service.id_transaction', $this->id_transaction)
            ->whereNotNull('tr_service_workflow.date_start_actual')
            ->whereNotNull('tr_service_workflow.date_end_actual')
            ->orderBy('sequence')->get();
    }

    public function payments()
    {
        if ($this->payment_method == 'atlas') {
            return DB::table("tr_service_coa_atlas")
                ->leftJoin('ms_exp_type', 'tr_service_coa_atlas.id_exptype', 'ms_exp_type.id_exptype')
                ->where('tr_service_coa_atlas.id_transaction', $this->id_transaction)->orderBy('percentage')->get();
        } elseif ($this->payment_method == 'non_atlas') {
            return DB::table("tr_service_coa_other")->where('id_transaction', $this->id_transaction)->orderBy('percentage')->get();
        } else {
            return 'Bank Transfer';
        }
    }

    public function payment_atlas_presentage()
    {
        $arrsum = DB::select(DB::raw("SELECT sum(`percentage`) as sum_presentage FROM `tr_service_coa_atlas` WHERE `id_transaction` = $this->id_transaction"));
        return $arrsum[0]->sum_presentage;
    }

    /*-- RAW --*/

}
