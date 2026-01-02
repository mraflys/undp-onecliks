<?php
namespace App;

use Cache;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkFlow extends Model
{
    use SoftDeletes;
    protected $table      = 'ms_service_workflow';
    protected $primaryKey = 'id_service_workflow';
    protected $hidden     = ['date_created', 'date_updated', 'updated_by', 'created_by', 'deleted_by'];

    const UPDATED_AT = 'date_updated';
    const DELETED_AT = 'date_deleted';

    public static function list_with_cache()
    {
        if (Cache::get('work_flow') != null) {
            return Cache::get('work_flow');
        } else {
            $results = self::all()->get();
            Cache::put('work_flow', $results, 1800);
            return $results;
        }
    }

    public function service()
    {
        return $this->belongsTo('App\ServiceList', 'id_service');
    }

    public function agency()
    {
        return $this->belongsTo('App\AgencyUnit', 'id_agency_unit');
    }

    public static function get_latest_seq($id_service)
    {
        $row = self::select(DB::raw('count(*) as count_sequence'))->where('id_service', $id_service)->first();
        return (! is_null($row)) ? ($row->count_sequence + 1) : 0;
    }

    public function infos()
    {
        return $this->hasMany('App\WorkFlowInfo', 'id_service_workflow');
    }

    public function docs()
    {
        return $this->hasMany('App\WorkFlowDoc', 'id_service_workflow');
    }

    public static function by_service_parent_id_with_price($id_service)
    {
        // Subquery to get the latest price list for each service based on date_end_price
        $latestPriceSubquery = DB::table('ms_service_pricelist as spl2')
            ->select('spl2.id_service', DB::raw('MAX(spl2.date_end_price) as max_date_end_price'))
            ->whereNull('spl2.date_deleted')
            ->whereNull('spl2.deleted_by')
            ->groupBy('spl2.id_service');

        return self::select(DB::raw("ms_service_workflow.*,ms_service.service_name, ms_service_pricelist.price, ms_service_pricelist.date_start_price, ms_service_pricelist.date_end_price, ms_service_pricelist.id_service_pricelist"))
            ->join("ms_service", "ms_service.id_service", "=", "ms_service_workflow.id_service", "INNER")
            ->leftJoin(DB::raw("({$latestPriceSubquery->toSql()}) as latest_price"), function ($join) use ($latestPriceSubquery) {
                $join->on('latest_price.id_service', '=', 'ms_service.id_service');
            })
            ->leftJoin('ms_service_pricelist', function ($join) {
                $join->on('ms_service_pricelist.id_service', '=', 'ms_service.id_service');
                $join->on('ms_service_pricelist.date_end_price', '=', 'latest_price.max_date_end_price');
                $join->whereNull('ms_service_pricelist.date_deleted');
                $join->whereNull('ms_service_pricelist.deleted_by');
            })
            ->mergeBindings($latestPriceSubquery)
            ->whereNull('ms_service.date_deleted')
            ->whereNull('ms_service.deleted_by')
            ->whereRaw("id_service_parent = ?", [$id_service])
            ->orderByRaw('id_service, sequence')->get();
    }

    public static function by_service_parent_id_and_first_sequence($id_service)
    {
        return self::select(DB::raw("ms_service_workflow.*"))
            ->join("ms_service", "ms_service.id_service", "=", "ms_service_workflow.id_service", "INNER")
            ->whereRaw("id_service_parent = ? AND ms_service_workflow.sequence = 1", [$id_service])
            ->orderByRaw('id_service, sequence')->get();
    }

    public static function get_next_workday($date, $n)
    {
        $result = DB::select("SELECT fn_get_next_workday('$date', $n) as result");
        return $result[0]->result;
    }

}
