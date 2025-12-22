<?php
namespace App\Http\Controllers;

use App\AgencyUnit;
use App\ReportQuery;
use App\SecUser;
use App\TrPEX;
use App\TrPexSetting;
use Datatables;
use DateInterval;
use DateTime;
use DB;
use Illuminate\Http\Request;

ini_set('memory_limit', '5048M');

date_default_timezone_set('Asia/Jakarta');
define('DATE_ONLY', 'Y-m-d');
// define('DATE_TIME', 'Y-m-d H:i:s');
// define('SERVICE_ERROR_MESSAGE', "Sorry, You aren't allowed to see the Request!");
class MemberReportController extends Controller
{
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

    public function get_critical($data)
    {
        $where   = [];
        $where[] = "id_status = -1
            AND DATE_FORMAT(date_updated, '%Y-%m-%d') >= '" . $data["start_date"] . "'
            AND DATE_FORMAT(date_updated, '%Y-%m-%d')  <= '" . $data["end_date"] . "'";

        $where[] = "id_status = 1
            AND DATE_FORMAT(date_created, '%Y-%m-%d') >= '" . $data["start_date"] . "'
            AND DATE_FORMAT(date_created, '%Y-%m-%d')  <= '" . $data["end_date"] . "'";

        $where[] = "id_status = 2
            AND DATE_FORMAT(date_authorized, '%Y-%m-%d') >= '" . $data["start_date"] . "'
            AND DATE_FORMAT(date_authorized, '%Y-%m-%d')  <= '" . $data["end_date"] . "'";

        $where[] = "id_status = 3
            AND DATE_FORMAT(date_finished, '%Y-%m-%d') >= '" . $data["start_date"] . "'
            AND DATE_FORMAT(date_finished, '%Y-%m-%d')  <= '" . $data["end_date"] . "'";

        $where[] = "id_status = 4
            AND DATE_FORMAT(date_finished, '%Y-%m-%d') >= '" . $data["start_date"] . "'
            AND DATE_FORMAT(date_finished, '%Y-%m-%d')  <= '" . $data["end_date"] . "'";

        $where[] = "id_status = 5
            AND DATE_FORMAT(date_finished, '%Y-%m-%d') >= '" . $data["start_date"] . "'
            AND DATE_FORMAT(date_finished, '%Y-%m-%d')  <= '" . $data["end_date"] . "'";

        $where[] = "id_status = 6
            AND DATE_FORMAT(date_finished, '%Y-%m-%d') >= '" . $data["start_date"] . "'
            AND DATE_FORMAT(date_finished, '%Y-%m-%d')  <= '" . $data["end_date"] . "'";

        if (count($where) > 0) {
            $where = implode(" OR ", $where);
        }

        return ReportQuery::critical($where);
    }

    public function get_formatted_critical_data($results, $page = 1, $per_page = 5)
    {
        $label           = [];
        $categories      = [];
        $categories_all  = [];
        $table           = [];
        $total_by_agency = [];

        // Define required statuses that must appear in the chart
        $required_statuses = ['Completed', 'On-going', 'Cancelled', 'Rejected', 'Returned', 'New Request'];

        if (! is_null($results) && count($results) > 0) {
            // Build dynamic labels and initialize totals from results
            foreach ($results as $g) {
                if (! isset($label[$g->id_agency_unit])) {
                    $label[$g->id_agency_unit]           = strtolower($g->agency_unit_name);
                    $total_by_agency[$g->id_agency_unit] = 0;
                }
            }

            // Initialize all required statuses with zero values for all agencies
            foreach ($required_statuses as $status) {
                foreach (array_keys($label) as $agency_id) {
                    $val[$status][$agency_id]["x"]     = 0;
                    $val[$status][$agency_id]["val"]   = 0;
                    $val[$status][$agency_id]["total"] = 0;
                }
            }

            // Populate actual data from results
            foreach ($results as $g) {
                $total_by_agency[$g->id_agency_unit] += $g->cnt;
                $val[$g->status_name][$g->id_agency_unit]["x"]   = $g->cnt;
                $val[$g->status_name][$g->id_agency_unit]["val"] = $g->value;
                $categories_all[$g->id_agency_unit]              = "\"" . $g->agency_unit_code . "<br/>( " . $label[$g->id_agency_unit] . " )\"";

                if ($g->status_name != "Input Feedback") {
                    $table[$g->status_name][$g->agency_unit_code] = $g->value;
                }
            }

            // Update totals for all statuses
            foreach ($total_by_agency as $key => $agency) {
                foreach ($required_statuses as $status) {
                    if (isset($val[$status][$key])) {
                        $val[$status][$key]["total"] = $agency;
                    }
                }
            }

            // Pagination logic
            $agency_ids         = array_keys($label);
            $total_agencies     = count($agency_ids);
            $total_pages        = ceil($total_agencies / $per_page);
            $page               = max(1, min($page, $total_pages)); // Ensure page is within valid range
            $offset             = ($page - 1) * $per_page;
            $paginated_agencies = array_slice($agency_ids, $offset, $per_page);

            // Format data for Highcharts (paginated)
            foreach ($required_statuses as $st) {
                $value[$st]["name"] = $st;
                $d                  = [];
                if (isset($val[$st])) {
                    foreach ($paginated_agencies as $agency_id) {
                        if (isset($val[$st][$agency_id])) {
                            $x   = $val[$st][$agency_id];
                            $d[] = "{'y': {$x["x"]}, 'val' : {$x["val"]}, 'total' : {$x["total"]} }";
                        }
                    }
                }
                $value[$st]["data"] = $d;
            }

            // Get paginated categories
            foreach ($paginated_agencies as $agency_id) {
                if (isset($categories_all[$agency_id])) {
                    $categories[] = $categories_all[$agency_id];
                }
            }

            $data["categories"] = implode(", ", $categories);
            $data["value"]      = $value;
            $data["table"]      = $table;
            $data["pagination"] = [
                'current_page' => $page,
                'total_pages'  => $total_pages,
                'per_page'     => $per_page,
                'total_items'  => $total_agencies,
            ];

        } else {
            // Empty data fallback
            $value = [];
            foreach ($required_statuses as $status) {
                $value[$status]["name"] = $status;
                $value[$status]["data"] = [];
            }
            $data['categories'] = '';
            $data['value']      = $value;
            $data['table']      = [];
            $data['pagination'] = [
                'current_page' => 1,
                'total_pages'  => 1,
                'per_page'     => $per_page,
                'total_items'  => 0,
            ];
        }
        return $data;
    }

    public function index(Request $req)
    {
        // Check if start_date and end_date are provided in query parameters
        if ($req->has('start_date') && $req->has('end_date')) {
            $data["start_date"] = date(DATE_ONLY, strtotime($req->start_date));
            $data["end_date"]   = date(DATE_ONLY, strtotime($req->end_date));
        } else {
            // Default: current month
            $curr_date  = new DateTime("now");
            $interval   = new DateInterval('P1M');
            $start_date = new DateTime("now");
            $start_date->sub($interval);

            $data["start_date"] = $start_date->format(DATE_ONLY);
            $data["end_date"]   = $curr_date->format(DATE_ONLY);
        }

        $page    = $req->get('page', 1);
        $results = $this->get_critical($data);
        $data    = array_merge($data, $this->get_formatted_critical_data($results, $page, 5));
        // dd($data["value"]);
        // foreach ($data["value"] as $key => $value)
        // {
        //   dd($value);
        // }
        $data['title']       = 'My Report';
        $data['breadcrumps'] = ['Member Area', $data['title']];
        return view('member.report.index', $data);
    }

    public function my_project()
    {
        $data['title']         = 'My Payroll Expenditure';
        $data['breadcrumps']   = ['Master', 'Project'];
        $data['list']          = TrPEX::orderBy('TIDNO')->where('Name', 'like', "%" . \Auth::user()->person_name . "%")->get();
        $TrPexSetting          = TrPexSetting::where('id_user', \Auth::user()->id_user)->get();
        $TrPexSettingId        = $TrPexSetting->pluck('TIDNO')->toArray();
        $data['distinctTrPEX'] = TrPEX::whereIn('TIDNO', $TrPexSettingId)->select('Project')->distinct()->get();
        return view('admin.project.list', $data);
    }

    public function detail()
    {
        $data['title']       = 'My Report';
        $data['agency']      = AgencyUnit::where(['is_service_unit' => 1])->whereRaw("id_agency_unit_parent IS NULL")->orderBy('agency_unit_code')->get();
        $data['breadcrumps'] = ['Member Area', $data['title']];
        return view('member.report.detail', $data);
    }

    public function detail_post(Request $req)
    {

        if (is_null($req->all_report_submit)) {
            $id_agency_unit  = $req->agency_unit;
            $id_service_unit = $req->service_unit;
            $id_service      = $req->service;
            $search_for      = $req->search;

            $now   = date(DATE_ONLY);
            $where = [];

            if (! empty($id_service_unit)) {
                $where[] = 'tr.id_agency_unit_service = ' . $id_service_unit;
            }

            if (! empty($id_service)) {
                $id_service = '"' . implode('", "', $id_service) . '"';
                $where[]    = 'tr.id_service in (' . $id_service . ')';
            }

            if (! empty($search_for)) {
                $txt     = $search_for;
                $tmp[]   = "tr.transaction_code LIKE '%$txt%'";
                $tmp[]   = "tr.service_name LIKE '%$txt%'";
                $tmp[]   = "tr.description LIKE '%$txt%'";
                $tmp[]   = "tr.person_name_buyer LIKE '%$txt%'";
                $tmp[]   = "tr.agency_code_buyer LIKE '%$txt%'";
                $where[] = "( " . implode(" OR ", $tmp) . " )";
            }

            if ($req->date1 != "") {
                $where[] = "DATE_FORMAT(tr.date_authorized , '%Y-%m-%d') >= '" . date(DATE_ONLY, strtotime($req->date1)) . "'";
            }

            if ($req->date2 != "") {
                $where[] = "DATE_FORMAT(tr.date_authorized , '%Y-%m-%d') <= '" . date(DATE_ONLY, strtotime($req->date2)) . "'";
            }

            if ($where != "") {
                $where = implode(" AND ", $where);
            }
            $results = ReportQuery::detail($where);
            return \Excel::download(new \App\Exports\detailReport($id_agency_unit, $id_service_unit, $id_service, $search_for, $req->date1, $req->date2), 'My Report.xlsx');
            $data['title']       = 'My Report';
            $data['agency']      = AgencyUnit::where(['is_service_unit' => 1])->whereRaw("id_agency_unit_parent IS NULL")->orderBy('agency_unit_code')->get();
            $data['breadcrumps'] = ['Member Area', $data['title']];
            return view('member.report.detail', $data);
        } else {
            return \Excel::download(new \App\Exports\detailReportAll($req->date1, $req->date2), 'My Report.xlsx');
            $data['title']       = 'My Report';
            $data['agency']      = AgencyUnit::where(['is_service_unit' => 1])->whereRaw("id_agency_unit_parent IS NULL")->orderBy('agency_unit_code')->get();
            $data['breadcrumps'] = ['Member Area', $data['title']];
            return view('member.report.detail', $data);
        }

    }

    public function timeliness()
    {
        $data['title']       = 'My Report - Timeliness';
        $data['breadcrumps'] = ['Member Area', $data['title']];
        return view('member.report.timeliness', $data);
    }

    public function timeliness_post(Request $req)
    {
        $date1   = date(DATE_ONLY, strtotime($req->date1));
        $date2   = date(DATE_ONLY, strtotime($req->date2));
        $where   = [];
        $where[] = "DATE_FORMAT(date_finished, '%Y-%m-%d') >= '" . $date1 . "'";
        $where[] = "DATE_FORMAT(date_finished, '%Y-%m-%d')  <= '" . $date2 . "'";
        $where   = implode(" AND ", $where);

        $workload = ReportQuery::timeliness_analysis($where);

        $i = 0;
        foreach ($workload as $g) {
            $val["ontime"][$g->id_agency_unit] = $g->ontime;
            $val["delay"][$g->id_agency_unit]  = $g->delay;
            $categories[$g->id_agency_unit]    = "'" . $g->agency_unit_code . "'";
        }

        foreach ($val as $st => $v) {
            $value[$st]["name"] = $st;
            $value[$st]["data"] = "[" . implode(", ", $v) . "]";
        }

        $data["categories"] = implode(", ", $categories);
        $data["value"]      = $value;
        $data['period']     = date('d-F-Y', strtotime($req->date1)) . ' - ' . date('d-F-Y', strtotime($req->date2));

        // TABLE
        $delay = ReportQuery::timeliness_analysis_delay($where);

        foreach ($delay as $row) {
            $data["table"][$row->type][$row->agency_unit_code] = $row->workflow_name;
        }

        $data['title']       = 'My Report - Timeliness';
        $data['breadcrumps'] = ['Member Area', $data['title']];
        return view('member.report.timeliness', $data);
    }

    public function timeliness_detail()
    {
        $data['title']       = 'My Report - Timeliness Detail';
        $data['breadcrumps'] = ['Member Area', $data['title']];
        return view('member.report.timeliness_detail', $data);
    }

    public function coa()
    {
        $image = public_path('assets/images/undp-logo.png');

        // Read image path, convert to base64 encoding

        $contents = file_get_contents($image);

        $imgData = base64_encode($contents);
        // Format the image SRC:  data:{mime};base64,{data};
        $src = 'data:image/png;base64,' . $imgData;

        $data['src']         = $src;
        $data['title']       = 'My Report - Coa';
        $data['breadcrumps'] = ['Member Area', $data['title']];
        return view('member.report.coa', $data);
    }

    public function coa_to_excel(Request $req)
    {

        return \Excel::download(new \App\Exports\detailCOA($req->date1, $req->date2, $req->viewBy), 'My Report - Coa.xlsx');
    }

    public function user_registration()
    {
        $data['title']       = 'My Report - User Registration';
        $data['breadcrumps'] = ['Member Area', $data['title']];
        return view('member.report.user_registration', $data);
    }

    public function workload_analysis()
    {
        $data['title']       = 'My Report - Workload Analysis';
        $data['breadcrumps'] = ['Member Area', $data['title']];
        return view('member.report.workload_analysis', $data);
    }

    public function performance()
    {
        $data['title']       = 'My Report - Performance';
        $data['breadcrumps'] = ['Member Area', $data['title']];
        return view('member.report.performance', $data);
    }

    public function critical_service(Request $req)
    {
        $data["start_date"]  = DATE(DATE_ONLY, strtotime('first day of this month'));
        $data["end_date"]    = DATE(DATE_ONLY, strtotime('last day of this month'));
        $data['period']      = DATE('d-M-Y', strtotime('first day of this month')) . ' to ' . DATE('d-M-Y', strtotime('last day of this month'));
        $page                = $req->get('page', 1);
        $results             = $this->get_critical($data);
        $data                = array_merge($data, $this->get_formatted_critical_data($results, $page, 5));
        $data['title']       = 'My Report - Critical Service';
        $data['breadcrumps'] = ['Member Area', $data['title']];
        // dd($data);
        return view('member.report.critical_service', $data);
    }

    public function critical_service_post(Request $req)
    {
        $data["start_date"]  = DATE(DATE_ONLY, strtotime($req->date1));
        $data["end_date"]    = DATE(DATE_ONLY, strtotime($req->date2));
        $data['period']      = DATE('d-M-Y', strtotime($req->date1)) . ' to ' . DATE('d-M-Y', strtotime($req->date2));
        $page                = $req->get('page', 1);
        $results             = $this->get_critical($data);
        $data                = array_merge($data, $this->get_formatted_critical_data($results, $page, 5));
        $data['title']       = 'My Report - Critical Service';
        $data['breadcrumps'] = ['Member Area', $data['title']];
        // dd($data);
        return view('member.report.critical_service', $data);
    }

    public function service_workload()
    {
        $data['title']       = 'My Report - Service Workload';
        $data['breadcrumps'] = ['Member Area', $data['title']];
        return view('member.report.service_workload', $data);
    }

    public function service_workload_post(Request $req)
    {
        $date1          = date(DATE_ONLY, strtotime($req->date1));
        $date2          = date(DATE_ONLY, strtotime($req->date2));
        $data['period'] = date('d-F-Y', strtotime($req->date1)) . ' - ' . date('d-F-Y', strtotime($req->date2));
        //filtering
        $where   = [];
        $where[] = "id_status = -1
                AND DATE_FORMAT(date_updated, '%Y-%m-%d') >= '" . $date1 . "'
                AND DATE_FORMAT(date_updated, '%Y-%m-%d')  <= '" . $date2 . "'";

        $where[] = "id_status = 1
                AND DATE_FORMAT(date_created, '%Y-%m-%d') >= '" . $date1 . "'
                AND DATE_FORMAT(date_created, '%Y-%m-%d')  <= '" . $date2 . "'";

        $where[] = "id_status = 2
                AND DATE_FORMAT(date_authorized, '%Y-%m-%d') >= '" . $date1 . "'
                AND DATE_FORMAT(date_authorized, '%Y-%m-%d')  <= '" . $date2 . "'";

        $where[] = "id_status = 3
                AND DATE_FORMAT(date_finished, '%Y-%m-%d') >= '" . $date1 . "'
                AND DATE_FORMAT(date_finished, '%Y-%m-%d')  <= '" . $date2 . "'";

        $where[] = "id_status = 4
                AND DATE_FORMAT(date_finished, '%Y-%m-%d') >= '" . $date1 . "'
                AND DATE_FORMAT(date_finished, '%Y-%m-%d')  <= '" . $date2 . "'";

        $where[] = "id_status = 5
                AND DATE_FORMAT(date_finished, '%Y-%m-%d') >= '" . $date1 . "'
                AND DATE_FORMAT(date_finished, '%Y-%m-%d')  <= '" . $date2 . "'";

        $where[] = "id_status = 6
                AND DATE_FORMAT(date_finished, '%Y-%m-%d') >= '" . $date1 . "'
                AND DATE_FORMAT(date_finished, '%Y-%m-%d')  <= '" . $date2 . "'";

        $where = implode(" AND ", $where);

        $workload = ReportQuery::service_workload($where);

        $i = 0;
        foreach ($workload as $g) {
            $val[$g->status_name][$g->id_agency_unit] = $g->cnt;
            $categories[$g->id_agency_unit]           = "'" . $g->agency_unit_code . "'";
        }

        foreach ($val as $st => $v) {
            $value[$st]["name"] = $st;
            $value[$st]["data"] = "[" . implode(", ", $v) . "]";
        }

        $data["categories"] = implode(", ", $categories);
        $data["value"]      = $value;

        $data['title']       = 'My Report - Service Workload';
        $data['breadcrumps'] = ['Member Area', $data['title']];
        return view('member.report.service_workload', $data);
    }

    public function tracking()
    {
        $data['title']       = 'My Report - Tracking';
        $data['agency']      = AgencyUnit::where(['is_service_unit' => 1])->whereRaw("id_agency_unit_parent IS NULL")->orderBy('agency_unit_code')->get();
        $data['breadcrumps'] = ['Member Area', $data['title']];
        return view('member.report.tracking', $data);
    }

    public function search_engine()
    {
        $data['title']       = 'My Report - Search Engine';
        $data['breadcrumps'] = ['Member Area', $data['title']];
        return view('member.report.search_engine', $data);
    }

    public function invoice_issue()
    {
        $data['title']       = 'My Report - Invoice Issue';
        $data['breadcrumps'] = ['Member Area', $data['title']];
        return view('member.report.invoice_issue', $data);
    }

    public function dsa_advance()
    {
        $data['title']       = 'My Report - DSA Advance';
        $data['breadcrumps'] = ['Member Area', $data['title']];
        return view('member.report.dsa_advance', $data);
    }

    public function complete_ticket()
    {
        $data['title']       = 'My Report - Complete Ticket';
        $data['breadcrumps'] = ['Member Area', $data['title']];
        return view('member.report.complete_ticket', $data);
    }

    public function service_cost()
    {
        $data['title']       = 'My Report - Service Cost';
        $data['breadcrumps'] = ['Member Area', $data['title']];
        return view('member.report.service_cost', $data);
    }

    public function service_cost_post(Request $req)
    {
        $date1          = date(DATE_ONLY, strtotime($req->date1));
        $date2          = date(DATE_ONLY, strtotime($req->date2));
        $data['period'] = date('d-F-Y', strtotime($req->date1)) . ' - ' . date('d-F-Y', strtotime($req->date2));
        //filtering
        $where   = [];
        $where[] = "DATE_FORMAT(t.date_payment, '%Y-%m-%d') >= '" . $date1 . "'";
        $where[] = "DATE_FORMAT(t.date_payment, '%Y-%m-%d')  <= '" . $date2 . "'";
        $where   = implode(" AND ", $where);
        $color   = ["#FF0000", "#0000FF", "#00FF00", "#FFFF00", "#FF00FF", "#00FFFF", "pink", "bluesky", "lightgreen"];

        $income = ReportQuery::monthly_income($where);

        if (! is_null($income)) {

            foreach ($income as $row) {
                if (! isset($total_income_workday[$row->id_transaction])) {
                    $total_income_workday[$row->id_transaction] = 0;
                }

                $total_income_workday[$row->id_transaction] += $row->workflow_day;
            }
            foreach ($income as $row) {
                if (! isset($group[$row->id_agency_unit])) {
                    $group[$row->id_agency_unit]["income"] = 0;
                }
                if ($total_income_workday[$row->id_transaction] == 0) {
                    $group[$row->id_agency_unit]["income"] = 0;
                } else {
                    $group[$row->id_agency_unit]["income"] += $row->workflow_day * $row->service_price / $total_income_workday[$row->id_transaction];
                }
                $group[$row->id_agency_unit]["agency_unit_name"] = $row->agency_unit_name;
                $group[$row->id_agency_unit]["agency_unit_code"] = $row->agency_unit_code;
                $group[$row->id_agency_unit]["color"]            = $row->color;
            }

            $i = 0;
            foreach ($group as $g) {
                $data_income[$i]["name"]  = "'" . $g["agency_unit_code"] . "'";
                $data_income[$i]["y"]     = round($g["income"], 2);
                $data_income[$i]["color"] = $g["color"];
                $i++;
            }
            $data["income"] = $data_income;
        }

        /**********   Unit EXPENDITURE  ******/
        $expenditure = ReportQuery::monthly_expenditure($where);
        $i           = 0;
        if (! empty($expenditure)) {
            foreach ($expenditure as $g) {
                $radiant_color                 = isset($color[$i]) ? $color[$i] : $color[count($color) - 5];
                $data_expenditure[$i]["name"]  = $g->agency_unit_code;
                $data_expenditure[$i]["y"]     = round($g->expenditure, 2);
                $data_expenditure[$i]["color"] = '#' . $this->radianGrad($radiant_color) . "#";
                $i++;
            }
            $data["expenditure"] = str_replace(["\"#", "#\""], "", json_encode($data_expenditure));
        }

        /**********   Agency EXPENDITURE  ******/
        $agency_expenditure = ReportQuery::monthly_agency_expenditure($where);
        $i                  = 0;
        if (! empty($agency_expenditure)) {
            foreach ($agency_expenditure as $g) {
                $radiant_color                        = isset($color[$i]) ? $color[$i] : $color[count($color) - 5];
                $data_agency_expenditure[$i]["name"]  = $g->agency_unit_code;
                $data_agency_expenditure[$i]["y"]     = round($g->expenditure, 2);
                $data_agency_expenditure[$i]["color"] = '#' . $this->radianGrad($radiant_color) . "#";
                $i++;
            }

            $data["agency_expenditure"] = str_replace(["\"#", "#\""], "", json_encode($data_agency_expenditure));
        }

        $data['title']       = 'My Report - Service Cost';
        $data['breadcrumps'] = ['Member Area', $data['title']];
        return view('member.report.service_cost', $data);
    }

    public function radianGrad($color)
    {
        return '{radialGradient: {cx: 0.5, cy: 0.5, r: 0.9}, stops: [[0, \'' . $color . '\'],[1, Highcharts.Color(\'' . $color . '\').brighten(-0.5).get(\'rgb\')]]}';
    }

    # datatables

    public function complete_ticket_list(Request $req)
    {
        $default_date1 = new DateTime('first day of this month');
        $default_date2 = new DateTime('last day of this month');
        $date1         = ($req->date1) ? date(DATE_ONLY, strtotime($req->date1)) : $default_date1->format(DATE_ONLY);
        $date2         = ($req->date2) ? date(DATE_ONLY, strtotime($req->date2)) : $default_date2->format(DATE_ONLY);
        $where         = [];

        if (! is_null($date1)) {
            $where[] = "DATE_FORMAT(tr.date_finished, '%Y-%m-%d') >= '" . $date1 . "'";
        }

        if (! is_null($date2)) {
            $where[] = "DATE_FORMAT(tr.date_finished, '%Y-%m-%d')  <= '" . $date2 . "'";
        }

        $where = (count($where) > 0) ? implode(" AND ", $where) : "tr.date_finished IS NOT NULL";
        if (! empty($req->search)) {
            $search = strtolower($req->search);
            $where .= " AND (lower(tr.transaction_code) LIKE '%" . $search . "%' OR lower(tr.service_name) LIKE '%" . $search . "%'
      OR lower(g.glje_no) LIKE '%" . $search . "%')";
        }
        return Datatables::of(ReportQuery::complete_ticket($where))
            ->editColumn('service_price', function ($l) {
                return number_format($l->service_price, 2);
            })->make(true);

    }

    public function coa_list(Request $req)
    {
        $default_date1 = new DateTime('first day of this month');
        $default_date2 = new DateTime('last day of this month');
        $date1         = ($req->date1) ? date(DATE_ONLY, strtotime($req->date1)) : $default_date1->format(DATE_ONLY);
        $date2         = ($req->date2) ? date(DATE_ONLY, strtotime($req->date2)) : $default_date2->format(DATE_ONLY);
        $where         = [];

        if ($req->viewBy == 'date_finished') {
            if (! is_null($date1)) {
                $where[] = "DATE_FORMAT(" . $req->viewBy . ", '%Y-%m-%d') >= '" . $date1 . "'";
            }

            if (! is_null($date2)) {
                $where[] = "DATE_FORMAT(" . $req->viewBy . ", '%Y-%m-%d')  <= '" . $date2 . "' AND date_authorized IS NOT NULL";
            }

        } elseif ($req->viewBy == 'date_authorized') {
            if (! is_null($date1)) {
                $where[] = "DATE_FORMAT(" . $req->viewBy . ", '%Y-%m-%d') >= '" . $date1 . "'";
            }

            if (! is_null($date2)) {
                $where[] = "DATE_FORMAT(" . $req->viewBy . ", '%Y-%m-%d')  <= '" . $date2 . "' AND date_finished IS NULL";
            }

        }
        $where = (count($where) > 0) ? implode(" AND ", $where) : $req->viewBy . " IS NOT NULL";

        return Datatables::of(ReportQuery::coa_detail($where))->editColumn('service_price', function ($l) {
            return number_format($l->service_price * $l->percentage / 100, 2);
        })->editColumn('exp_type', function ($l) {
            return $l->exp_type_code . ' - ' . $l->exp_type_name;
        })->make(true);

    }

    public function workload_analysis_list(Request $req)
    {
        $default_date1 = new DateTime('first day of this month');
        $default_date2 = new DateTime('last day of this month');
        $date1         = ($req->date1) ? date(DATE_ONLY, strtotime($req->date1)) : $default_date1->format(DATE_ONLY);
        $date2         = ($req->date2) ? date(DATE_ONLY, strtotime($req->date2)) : $default_date2->format(DATE_ONLY);
        $where         = [];

        if (! is_null($date1)) {
            $where[] = "DATE_FORMAT(date_start_actual, '%Y-%m-%d') >= '" . $date1 . "'";
        }

        if (! is_null($date2)) {
            $where[] = "DATE_FORMAT(date_start_actual, '%Y-%m-%d')  <= '" . $date2 . "'";
        }

        $where = (count($where) > 0) ? implode(" AND ", $where) : "date_start_actual IS NOT NULL";

        return Datatables::of(ReportQuery::workload_analysis($where))->make(true);

    }

    public function dsa_advance_list(Request $req)
    {
        $default_date1 = new DateTime('first day of this month');
        $default_date2 = new DateTime('last day of this month');
        $date1         = ($req->date1) ? date(DATE_ONLY, strtotime($req->date1)) : $default_date1->format(DATE_ONLY);
        $date2         = ($req->date2) ? date(DATE_ONLY, strtotime($req->date2)) : $default_date2->format(DATE_ONLY);
        $where         = [];

        if ($req->status > 0) {
            $where[] = "tr.id_status = " . $req->status;
        }

        if (! is_null($date1)) {
            $where[] = "DATE_FORMAT(STR_TO_DATE(ret_date.info_value, '%Y-%m-%d'), '%Y-%m-%d') >= '" . $date1 . "'";
        }

        if (! is_null($date2)) {
            $where[] = "DATE_FORMAT(STR_TO_DATE(ret_date.info_value, '%Y-%m-%d'), '%Y-%m-%d')  <= '" . $date2 . "'";
        }

        $where = (count($where) > 0) ? implode(" AND ", $where) : "ret_date.info_value IS NOT NULL";

        return Datatables::of(ReportQuery::dsa_advance($where))
            ->addColumn('aging100', function ($l) {
                return ($l->aging > 90) ? number_format($l->aging) : '-';
            })
            ->addColumn('aging90', function ($l) {
                return ($l->aging > 60 && $l->aging <= 90) ? number_format($l->aging) : '-';
            })
            ->addColumn('aging60', function ($l) {
                return ($l->aging > 30 && $l->aging <= 60) ? number_format($l->aging) : '-';
            })
            ->addColumn('aging30', function ($l) {
                return ($l->aging <= 30) ? number_format($l->aging) : '-';
            })
            ->make(true);

    }

    public function dsa_advance_null_list(Request $req)
    {
        $where   = [];
        $where[] = "ret_date.info_value IS NULL";
        if ($req->status > 0) {
            $where[] = "tr.id_status = " . $req->status;
        }

        $where = (count($where) > 0) ? implode(" AND ", $where) : "";

        return Datatables::of(ReportQuery::dsa_advance_null($where))
            ->addColumn('aging100', function ($l) {
                return ($l->aging > 90) ? number_format($l->aging) : '-';
            })
            ->addColumn('aging90', function ($l) {
                return ($l->aging > 60 && $l->aging <= 90) ? number_format($l->aging) : '-';
            })
            ->addColumn('aging60', function ($l) {
                return ($l->aging > 30 && $l->aging <= 60) ? number_format($l->aging) : '-';
            })
            ->addColumn('aging30', function ($l) {
                return ($l->aging <= 30) ? number_format($l->aging) : '-';
            })
            ->make(true);

    }

    public function invoice_issue_list(Request $req)
    {
        $default_date1 = new DateTime('first day of this month');
        $default_date2 = new DateTime('last day of this month');
        $date1         = ($req->date1) ? date(DATE_ONLY, strtotime($req->date1)) : $default_date1->format(DATE_ONLY);
        $date2         = ($req->date2) ? date(DATE_ONLY, strtotime($req->date2)) : $default_date2->format(DATE_ONLY);
        $where         = [];

        if (! is_null($date1)) {
            $where[] = "DATE_FORMAT(date_created, '%Y-%m-%d') >= '" . $date1 . "'";
        }

        if (! is_null($date2)) {
            $where[] = "DATE_FORMAT(date_created, '%Y-%m-%d')  <= '" . $date2 . "'";
        }

        $where = (count($where) > 0) ? implode(" AND ", $where) : "date_created IS NOT NULL";

        return Datatables::of(ReportQuery::invoice_issue($where))
            ->editColumn('amount_paid', function ($l) {
                return number_format($l->amount_paid, 2);
            })
            ->editColumn('amount_paid_local', function ($l) {
                return number_format($l->amount_paid_local, 2);
            })
            ->editColumn('amount_billing', function ($l) {
                return number_format($l->amount_billing, 2);
            })
            ->editColumn('amount_billing_local', function ($l) {
                return number_format($l->amount_billing_local, 2);
            })
            ->addColumn('aging100', function ($l) {
                return ($l->aging > 90) ? number_format($l->aging) : '-';
            })
            ->addColumn('aging90', function ($l) {
                return ($l->aging > 60 && $l->aging <= 90) ? number_format($l->aging) : '-';
            })
            ->addColumn('aging60', function ($l) {
                return ($l->aging > 30 && $l->aging <= 60) ? number_format($l->aging) : '-';
            })
            ->addColumn('aging30', function ($l) {
                return ($l->aging <= 30) ? number_format($l->aging) : '-';
            })
            ->make(true);

    }

    public function user_registration_list(Request $req)
    {
        $name   = strtolower($req->name);
        $status = strtolower($req->status);
        $where  = [];
        if (! empty($name)) {
            $where[] = "person_name LIKE '%" . $name . "%'";
        }

        if (! is_null($status) && $status != 'all') {
            $where[] = "sec_user.is_active =" . $status;
        }

        $where = (count($where) > 0) ? implode(" AND ", $where) : "sec_user.date_deleted IS NULL";
        $list  = SecUser::select(DB::raw("sec_user.*, (CASE WHEN sec_user.is_active = 1 THEN 'Active' ELSE 'inActive' END) as is_active,
      agency_unit_code, agency_unit_name"))
            ->join("ms_agency_unit", "ms_agency_unit.id_agency_unit", "=", "sec_user.id_agency_unit")
            ->whereRaw($where);
        return Datatables::of($list)->make(true);

    }

    public function timeliness_detail_list(Request $req)
    {
        $default_date1 = new DateTime('first day of this month');
        $default_date2 = new DateTime('last day of this month');
        $date1         = ($req->date1) ? date(DATE_ONLY, strtotime($req->date1)) : $default_date1->format(DATE_ONLY);
        $date2         = ($req->date2) ? date(DATE_ONLY, strtotime($req->date2)) : $default_date2->format(DATE_ONLY);
        $where         = [];
        if (! is_null($date1)) {
            $where[] = "DATE_FORMAT(date_finished, '%Y-%m-%d') >= '" . $date1 . "'";
        }

        if (! is_null($date2)) {
            $where[] = "DATE_FORMAT(date_finished, '%Y-%m-%d')  <= '" . $date2 . "'";
        }

        $where = (count($where) > 0) ? implode(" AND ", $where) : "date_finished IS NOT NULL";
        if (! empty($req->search)) {
            $search = strtolower($req->search);
            $where .= " AND (lower(transaction_code) LIKE '%" . $search . "%' OR lower(service_name) LIKE '%" . $search . "%'
      OR lower(person_name_buyer) LIKE '%" . $search . "%')";
        }
        return Datatables::of(ReportQuery::timeliness_detail($where))->make(true);
    }

    public function search_engine_list(Request $req)
    {
        $default_date1 = new DateTime('first day of this month');
        $default_date2 = new DateTime('last day of this month');
        $date1         = ($req->date1) ? date(DATE_ONLY, strtotime($req->date1)) : $default_date1->format(DATE_ONLY);
        $date2         = ($req->date2) ? date(DATE_ONLY, strtotime($req->date2)) : $default_date2->format(DATE_ONLY);
        $search        = $req->search;
        $search_in     = $req->search_in;
        $where         = [];

        //filtering
        $filter = "";
        $where2 = null;

        if (! is_null($date1)) {
            $where[] = "DATE_FORMAT(date_authorized, '%Y-%m-%d') >= '" . $date1 . "'";
        }

        if (! is_null($date2)) {
            $where[] = "DATE_FORMAT(date_authorized, '%Y-%m-%d')  <= '" . $date2 . "'";
        }

        $where = (count($where) > 0) ? implode(" AND ", $where) : "date_authorized IS NOT NULL";

        if (! empty($search)) {
            $filter = $search;
        }

        if (! empty($search_in)) {
            if ($search_in != "all") {
                $where2 = " AND kode = '" . $search_in . "'";
            }

        }

        return Datatables::of(ReportQuery::search_engine($where, $where2, $filter))->make(true);
    }

    public function performance_list(Request $req)
    {
        $default_date1 = new DateTime('first day of this month');
        $default_date2 = new DateTime('last day of this month');
        $date1         = ($req->date1) ? date(DATE_ONLY, strtotime($req->date1)) : $default_date1->format(DATE_ONLY);
        $date2         = ($req->date2) ? date(DATE_ONLY, strtotime($req->date2)) : $default_date2->format(DATE_ONLY);
        $where         = [];
        if (! is_null($date1)) {
            $where[] = "DATE_FORMAT(date_finished, '%Y-%m-%d') >= '" . $date1 . "'";
        }

        if (! is_null($date2)) {
            $where[] = "DATE_FORMAT(date_finished, '%Y-%m-%d')  <= '" . $date2 . "'";
        }

        $where = (count($where) > 0) ? implode(" AND ", $where) : "date_finished IS NOT NULL";
        if (! empty($req->search)) {
            $search = strtolower($req->search);
            $where .= " AND lower(service_name) LIKE '%" . $search . "%'";
        }
        return Datatables::of(ReportQuery::performance($where))->make(true);
    }
}
