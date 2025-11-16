<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\TrPEX;
use App\TrPexSetting;

class projectExcel implements FromCollection, WithHeadings, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        if(\Auth::user()->id_role == 3){
            $TrPEX = TrPEX::whereNotNull('Name')->orderBy('TIDNO')->get(); 

        }else{
            $TrPexSetting = TrPexSetting::where('id_user',\Auth::user()->id_user)->get();
            $TrPexSettingId = $TrPexSetting->pluck('TIDNO')->toArray();
            $TrPEXs = TrPEX::whereIn('TIDNO',$TrPexSettingId)->orderBy('TIDNO')->get();
            $person_list = [];
            foreach($TrPEXs as $pex){
                $list_TrPEX = TrPEX::where('CalendarGroup',$pex->CalendarGroup)->where('Project',$pex->Project)->where('ErnDedCd',$pex->ErnDedCd)->where('ProjAct',$pex->ProjAct)->get();
                array_push($person_list, $pex->TIDNO);
                foreach($list_TrPEX as $list){
                    $list_child_pex = TrPEX::where('Name','!=',$pex->Name)->where('Name',$list->Name)->get();
                    foreach($list_child_pex as $hild_pex){
                        if($hild_pex->TIDNO){
                            array_push($person_list, $hild_pex->TIDNO);
                        }
                    }
                    
                }
            }
            $TrPEX = TrPEX::whereIn('TIDNO',$person_list)->orderBy('TIDNO')->get();
            
        }

        return collect($TrPEX);

    }

    public function headings(): array {
        return [
            "TIDNO","CalendarGroup","Index_","Name","Email","PositionDescr","Journal","Date","Fund","OperatingUnit","ImplementingAg","Donor","DeptID","Project","ProjAct","BankAccount","PCBusUnit","Position","GLUnit","ErnDedCd","ErnDedAcc","BaseAmount","Currency","NumericValue"
        ];
    }
}
