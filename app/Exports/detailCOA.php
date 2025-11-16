<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\ReportQuery;
use DateTime;

class detailCOA implements FromView, WithDrawings, ShouldAutoSize
{
    protected $date1,$date2;

    function __construct($date1,$date2,$viewBy) {
            $this->date1 = $date1;
            $this->date2 = $date2;
            $this->viewBy = $viewBy;
    }

    public function view(): View
    {
        $default_date1 = new DateTime('first day of this month');
        $default_date2 = new DateTime('last day of this month');
        $date1 = ($this->date1) ? date(DATE_ONLY, strtotime($this->date1)) : $default_date1->format(DATE_ONLY);
        $date2 = ($this->date2) ? date(DATE_ONLY, strtotime($this->date2)) : $default_date2->format(DATE_ONLY);
        $where = [];

        if($this->viewBy == 'date_finished'){
            if (!is_null($date1)) $where[] = "DATE_FORMAT(".$this->viewBy.", '%Y-%m-%d') >= '".$date1."'";
            if (!is_null($date2)) $where[] = "DATE_FORMAT(".$this->viewBy.", '%Y-%m-%d')  <= '".$date2."' AND date_authorized IS NOT NULL";
        }elseif($this->viewBy == 'date_authorized'){
            if (!is_null($date1)) $where[] = "DATE_FORMAT(".$this->viewBy.", '%Y-%m-%d') >= '".$date1."'";
            if (!is_null($date2)) $where[] = "DATE_FORMAT(".$this->viewBy.", '%Y-%m-%d')  <= '".$date2."' AND date_finished IS NULL";
        }
        $where = (count($where) > 0) ? implode(" AND ", $where) : $this->viewBy." IS NOT NULL";
        $ReportQuery = ReportQuery::coa_detail_excel($where);

        foreach($ReportQuery as $report){

            $report->service_price = number_format($report->service_price * $report->percentage/100, 2);
            $report->dateperiod = $this->date1. " to " .$this->date2;
            if($report->percentage == 0){
                $report->percentage = "0";
            }
            $report->exp_type = $report->exp_type_code . ' - ' . $report->exp_type_name;

        }
        // dd($ReportQuery);
        return view('exports.coa', [
            'coa' => collect($ReportQuery)
        ]);
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('This is my logo');
        $drawing->setPath(public_path('assets/images/undp-logo.png'));
        $drawing->setHeight(100);
        $drawing->setCoordinates('A1');
        $drawing->setOffsetX(38.8);
        $drawing->setOffsetY(50);
        return $drawing;
    }
}
class detailCOA_test implements FromCollection, WithHeadings, WithDrawings, ShouldAutoSize
{
    protected $date1,$date2;

    function __construct($date1,$date2) {
            $this->date1 = $date1;
            $this->date2 = $date2;
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {

        $default_date1 = new DateTime('first day of this month');
        $default_date2 = new DateTime('last day of this month');
        $date1 = ($this->date1) ? date(DATE_ONLY, strtotime($this->date1)) : $default_date1->format(DATE_ONLY);
        $date2 = ($this->date2) ? date(DATE_ONLY, strtotime($this->date2)) : $default_date2->format(DATE_ONLY);
        $where = [];

        if (!is_null($date1)) $where[] = "DATE_FORMAT(date_authorized, '%Y-%m-%d') >= '".$date1."'";
        if (!is_null($date2)) $where[] = "DATE_FORMAT(date_authorized, '%Y-%m-%d')  <= '".$date2."'";
        $where = (count($where) > 0) ? implode(" AND ", $where) : "date_authorized IS NOT NULL";
        $ReportQuery = ReportQuery::coa_detail_excel($where);

        foreach($ReportQuery as $report){

            $report->service_price = number_format($report->service_price * $report->percentage/100, 2);
            if($report->percentage == 0){
                $report->percentage == "0";
            }
            // dd($report);
        }

        return collect($ReportQuery);
    }
    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('This is my logo');
        $drawing->setPath(public_path('assets\images\undp-logo.png'));
        $drawing->setHeight(90);
        $drawing->setCoordinates('A1');
        return $drawing;
    }
    public function headings(): array {
        return [["","My Report - Coa"],
            ["Ticket","Service Name","Service Unit","Description","Unit Name","PCBU","Project","Activities","OPU","FUND","Department","Agent","Donor","(%)","Value","Status"]
        ];
    }


}
