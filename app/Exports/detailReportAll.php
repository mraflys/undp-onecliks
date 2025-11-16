<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\ReportQuery;
class detailReportAll implements FromCollection, WithHeadings
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
        $now = date(DATE_ONLY);
        $where = [];

        if ($this->date1 != "")
        {
            $where[] = "DATE_FORMAT(tr.date_authorized , '%Y-%m-%d') >= '" . date(DATE_ONLY, strtotime($this->date1)) . "'";
        }

        if ($this->date2 != "")
        {
            $where[] = "DATE_FORMAT(tr.date_authorized , '%Y-%m-%d') <= '" . date(DATE_ONLY, strtotime($this->date2)) . "'";
        }

        if ($where != ""){
            $where = implode(" AND ", $where);
        }
        
        $results = ReportQuery::detail($where);
        return collect($results);
    }
    
    public function headings(): array {
        return [
            "id_transaction","transaction_code","service_name","description","date_finished","person_name_buyer","authorized_by","agency_code_buyer","date_authorized","id_agency_unit_service","service_rating","service_price","sequence","id_transaction_workflow","workflow_name","workflow_day","completed_by","timeliness","id_transaction_workflow_info","info_title","info_value","invoice_no","invoice_date","glje_no","glje_date","amount_of"
        ];
    }
}
