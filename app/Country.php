<?php
namespace App;

use Cache;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Country extends Model
{
    use SoftDeletes;
    protected $table      = 'ms_country';
    protected $primaryKey = 'id_country';
    const UPDATED_AT      = 'date_updated';
    const DELETED_AT      = 'date_deleted';

    public static function list_with_cache()
    {
        if (Cache::get('countries') != null) {
            return Cache::get('countries');
        } else {
            $results = self::orderBy('country_name')->get();
            Cache::put('countries', $results, 1800);
            return $results;
        }
    }

    public static function get_last_sequence_by_code($code)
    {
        $code = strtolower($code);

        // Use database lock to prevent race condition
        DB::select('SELECT GET_LOCK(?, 10)', ["ticket_gen_" . $code]);

        try {
            // Get current sequence with FOR UPDATE lock (atomic read)
            $res = self::select(DB::raw("sequence"))
                ->whereRaw("LOWER(country_code) = ?", [$code])
                ->lockForUpdate()
                ->first();

            if (! $res) {
                throw new \Exception("Country code '{$code}' not found");
            }

            $next_sequence = $res->sequence + 1;

            // Update sequence atomically using parameterized query
            DB::update("UPDATE ms_country SET sequence = sequence + 1 WHERE LOWER(country_code) = ?", [$code]);

            return $next_sequence;

        } finally {
            // Always release the lock
            DB::select('SELECT RELEASE_LOCK(?)', ["ticket_gen_" . $code]);
        }
    }
}
