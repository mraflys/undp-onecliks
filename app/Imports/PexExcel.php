<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\TrPEX;
use App\TrPexSetting;
use App\SecUser;

class PexExcel implements ToCollection
{
    protected $date1,$date2;

    function __construct($date1,$date2) {
            $this->date1 = $date1;
            $this->date2 = $date2;
    }
    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        $a = 0;
        foreach ($rows as $row) 
        {   
            
            $TIDNO = isset($row[0]) ? $row[0] : null;
            if($a != 0){
                if(is_int($TIDNO)){
                    
                        $Calendar = isset($row[1]) ? $row[1] : null;
                        $Index = isset($row[2]) ? $row[2] : null;
                        $Name = isset($row[3]) ? $row[3] : null;
                        $PositionDescr = isset($row[4]) ? $row[4] : null;
                        $Journal = isset($row[5]) ? $row[5] : null;
                        $Date = isset($row[6]) ? $row[6] : null;
                        $Fund = isset($row[7]) ? $row[7] : null;
                        $OperatingUnit = isset($row[8]) ? $row[8] : null;
                        $ImplementingAg = isset($row[9]) ? $row[9] : null;
                        $Donor = isset($row[10]) ? $row[10] : null;
                        $DeptID = isset($row[11]) ? $row[11] : null;
                        $Project = isset($row[12]) ? $row[12] : null;
                        $ProjAct = isset($row[13]) ? $row[13] : null;
                        $BankAccount = isset($row[14]) ? $row[14] : null;
                        $PCBusUnit = isset($row[15]) ? $row[15] : null;
                        $Position = isset($row[16]) ? $row[16] : null;
                        $GLUnit = isset($row[17]) ? $row[17] : null;
                        $ErnDedCd = isset($row[18]) ? $row[18] : null;
                        $ErnDedAcc = isset($row[19]) ? $row[19] : null;
                        $BaseAmount = isset($row[20]) ? $row[20] : null;
                        $Currency = isset($row[21]) ? $row[21] : null;
                        $NumericValue = isset($row[22]) ? $row[22] : null;
                        $UNIXDATE = ($Date - 25569) * 86400;
                        $EXCELDATE = 25569 + ($UNIXDATE / 86400);
                        $UNIXDATE = ($EXCELDATE - 25569) * 86400;
                        $realDate = gmdate("Y-m-d", $UNIXDATE);
        
                        $word_count = str_word_count($Name);
        
                        if($word_count >= 2){
                            $namearr = (explode(" ",$Name));
        
                            $SecUser = SecUser::where('person_name','like',"%".$namearr[0]."%")->where('person_name','like',"%".$namearr[1]."%")->first();
                        }else{
                            $SecUser = SecUser::where('person_name','like',"%".$Name."%")->first();
                        }
                        
                        if($SecUser){
                            $email = $SecUser->email;
                            $fullName = $SecUser->person_name;
                        }else{
                            $email = null;
                            $fullName = null;
                        }
                        if (($realDate >= $this->date1) && ($realDate <= $this->date2)) {
                            
                            $TrPEX = TrPEX::where('TIDNO', $TIDNO)->first();
                            if($TrPEX){
                                TrPEX::where('TIDNO', $TIDNO)->update([
                                    'TIDNO' => isset($TIDNO) ? $TIDNO : null,
                                    'CalendarGroup' => isset($Calendar) ? $Calendar : null,
                                    'Index_' => isset($Index) ? $Index : null,
                                    'Name' => isset($Name) ? $Name : null,
                                    'Email' => $email,
                                    'PositionDescr' => isset($PositionDescr) ? $PositionDescr : null,
                                    'Journal' => isset($Journal) ? $Journal : null,
                                    'Date' => isset($realDate) ? $realDate : null,
                                    'Fund' => isset($Fund) ? $Fund : null,
                                    'OperatingUnit' => isset($OperatingUnit) ? $OperatingUnit : null,
                                    'ImplementingAg' => isset($ImplementingAg) ? $ImplementingAg : null,
                                    'Donor' => isset($Donor) ? $Donor : null,
                                    'DeptID' => isset($DeptID) ? $DeptID : null,
                                    'Project' => isset($Project) ? $Project : null,
                                    'ProjAct' => isset($ProjAct) ? $ProjAct : null,
                                    'BankAccount' => isset($BankAccount) ? $BankAccount : null,
                                    'PCBusUnit' => isset($PCBusUnit) ? $PCBusUnit : null,
                                    'Position' => isset($Position) ? $Position : null,
                                    'GLUnit' => isset($GLUnit) ? $GLUnit : null,
                                    'ErnDedCd' => isset($ErnDedCd) ? $ErnDedCd : null,
                                    'ErnDedAcc' => isset($ErnDedAcc) ? $ErnDedAcc : null,
                                    'BaseAmount' => isset($BaseAmount) ? $BaseAmount : null,
                                    'Currency' => isset($Currency) ? $Currency : null,
                                    'NumericValue' => isset($NumericValue) ? $NumericValue : null,
                                ]);
                            }else{
                                TrPEX::create([
                                    'TIDNO' => isset($TIDNO) ? $TIDNO : null,
                                    'CalendarGroup' => isset($Calendar) ? $Calendar : null,
                                    'Index_' => isset($Index) ? $Index : null,
                                    'Name' => isset($Name) ? $Name : null,
                                    'Email' => $email,
                                    'PositionDescr' => isset($PositionDescr) ? $PositionDescr : null,
                                    'Journal' => isset($Journal) ? $Journal : null,
                                    'Date' => isset($realDate) ? $realDate : null,
                                    'Fund' => isset($Fund) ? $Fund : null,
                                    'OperatingUnit' => isset($OperatingUnit) ? $OperatingUnit : null,
                                    'ImplementingAg' => isset($ImplementingAg) ? $ImplementingAg : null,
                                    'Donor' => isset($Donor) ? $Donor : null,
                                    'DeptID' => isset($DeptID) ? $DeptID : null,
                                    'Project' => isset($Project) ? $Project : null,
                                    'ProjAct' => isset($ProjAct) ? $ProjAct : null,
                                    'BankAccount' => isset($BankAccount) ? $BankAccount : null,
                                    'PCBusUnit' => isset($PCBusUnit) ? $PCBusUnit : null,
                                    'Position' => isset($Position) ? $Position : null,
                                    'GLUnit' => isset($GLUnit) ? $GLUnit : null,
                                    'ErnDedCd' => isset($ErnDedCd) ? $ErnDedCd : null,
                                    'ErnDedAcc' => isset($ErnDedAcc) ? $ErnDedAcc : null,
                                    'BaseAmount' => isset($BaseAmount) ? $BaseAmount : null,
                                    'Currency' => isset($Currency) ? $Currency : null,
                                    'NumericValue' => isset($NumericValue) ? $NumericValue : null,
                                ]);
                            }
                            // TrPexSetting::create([
                            //     'TIDNO' => isset($TIDNO) ? $TIDNO : null,
                            //     'is_active' => false
                            // ]);
                        }
                
                }else{
                    
                    $TrPEX = TrPEX::orderBy('TIDNO','DESC')->first();
                    if($TrPEX){
                        $TIDNO = $TrPEX->TIDNO + 1;
                    }else{
                        $TIDNO = 1;
                    }
                    $Calendar = isset($row[0]) ? $row[0] : null;
                    $Index = isset($row[1]) ? $row[1] : null;
                    $Name = isset($row[2]) ? $row[2] : null;
                    $PositionDescr = isset($row[3]) ? $row[3] : null;
                    $Journal = isset($row[4]) ? $row[4] : null;
                    $Date = isset($row[5]) ? $row[5] : null;
                    $Fund = isset($row[6]) ? $row[6] : null;
                    $OperatingUnit = isset($row[7]) ? $row[7] : null;
                    $ImplementingAg = isset($row[8]) ? $row[8] : null;
                    $Donor = isset($row[9]) ? $row[9] : null;
                    $DeptID = isset($row[10]) ? $row[10] : null;
                    $Project = isset($row[11]) ? $row[11] : null;
                    $ProjAct = isset($row[12]) ? $row[12] : null;
                    $BankAccount = isset($row[13]) ? $row[13] : null;
                    $PCBusUnit = isset($row[14]) ? $row[14] : null;
                    $Position = isset($row[15]) ? $row[15] : null;
                    $GLUnit = isset($row[16]) ? $row[16] : null;
                    $ErnDedCd = isset($row[17]) ? $row[17] : null;
                    $ErnDedAcc = isset($row[18]) ? $row[18] : null;
                    $BaseAmount = isset($row[19]) ? $row[19] : null;
                    $Currency = isset($row[20]) ? $row[20] : null;
                    $NumericValue = isset($row[21]) ? $row[21] : null;
                    $UNIXDATE = ($Date - 25569) * 86400;
                    $EXCELDATE = 25569 + ($UNIXDATE / 86400);
                    $UNIXDATE = ($EXCELDATE - 25569) * 86400;
                    $realDate = gmdate("Y-m-d", $UNIXDATE);
                    
                    $word_count = str_word_count($Name);
                    
                    if($word_count >= 2){
                        $namearr = (explode(" ",$Name));

                        $SecUser = SecUser::where('person_name','like',"%".$namearr[0]."%")->where('person_name','like',"%".$namearr[1]."%")->first();
                    }else{
                        $SecUser = SecUser::where('person_name','like',"%".$Name."%")->first();
                    }
                    
                    if($SecUser){
                        $email = $SecUser->email;
                        $fullName = $SecUser->person_name;
                    }else{
                        $email = null;
                        $fullName = null;
                    }
                    if (($realDate >= $this->date1) && ($realDate <= $this->date2)) {
                        
                        $TrPEX = TrPEX::where('TIDNO', $TIDNO)->first();
                        if($TrPEX){

                            TrPEX::where('TIDNO', $TIDNO)->update([
                                'TIDNO' => isset($TIDNO) ? $TIDNO : null,
                                'CalendarGroup' => isset($Calendar) ? $Calendar : null,
                                'Index_' => isset($Index) ? $Index : null,
                                'Name' => isset($Name) ? $Name : null,
                                'Email' => $email,
                                'PositionDescr' => isset($PositionDescr) ? $PositionDescr : null,
                                'Journal' => isset($Journal) ? $Journal : null,
                                'Date' => isset($realDate) ? $realDate : null,
                                'Fund' => isset($Fund) ? $Fund : null,
                                'OperatingUnit' => isset($OperatingUnit) ? $OperatingUnit : null,
                                'ImplementingAg' => isset($ImplementingAg) ? $ImplementingAg : null,
                                'Donor' => isset($Donor) ? $Donor : null,
                                'DeptID' => isset($DeptID) ? $DeptID : null,
                                'Project' => isset($Project) ? $Project : null,
                                'ProjAct' => isset($ProjAct) ? $ProjAct : null,
                                'BankAccount' => isset($BankAccount) ? $BankAccount : null,
                                'PCBusUnit' => isset($PCBusUnit) ? $PCBusUnit : null,
                                'Position' => isset($Position) ? $Position : null,
                                'GLUnit' => isset($GLUnit) ? $GLUnit : null,
                                'ErnDedCd' => isset($ErnDedCd) ? $ErnDedCd : null,
                                'ErnDedAcc' => isset($ErnDedAcc) ? $ErnDedAcc : null,
                                'BaseAmount' => isset($BaseAmount) ? $BaseAmount : null,
                                'Currency' => isset($Currency) ? $Currency : null,
                                'NumericValue' => isset($NumericValue) ? $NumericValue : null,
                            ]);
                        }else{
                            TrPEX::create([
                                'TIDNO' => isset($TIDNO) ? $TIDNO : null,
                                'CalendarGroup' => isset($Calendar) ? $Calendar : null,
                                'Index_' => isset($Index) ? $Index : null,
                                'Name' => isset($Name) ? $Name : null,
                                'Email' => $email,
                                'PositionDescr' => isset($PositionDescr) ? $PositionDescr : null,
                                'Journal' => isset($Journal) ? $Journal : null,
                                'Date' => isset($realDate) ? $realDate : null,
                                'Fund' => isset($Fund) ? $Fund : null,
                                'OperatingUnit' => isset($OperatingUnit) ? $OperatingUnit : null,
                                'ImplementingAg' => isset($ImplementingAg) ? $ImplementingAg : null,
                                'Donor' => isset($Donor) ? $Donor : null,
                                'DeptID' => isset($DeptID) ? $DeptID : null,
                                'Project' => isset($Project) ? $Project : null,
                                'ProjAct' => isset($ProjAct) ? $ProjAct : null,
                                'BankAccount' => isset($BankAccount) ? $BankAccount : null,
                                'PCBusUnit' => isset($PCBusUnit) ? $PCBusUnit : null,
                                'Position' => isset($Position) ? $Position : null,
                                'GLUnit' => isset($GLUnit) ? $GLUnit : null,
                                'ErnDedCd' => isset($ErnDedCd) ? $ErnDedCd : null,
                                'ErnDedAcc' => isset($ErnDedAcc) ? $ErnDedAcc : null,
                                'BaseAmount' => isset($BaseAmount) ? $BaseAmount : null,
                                'Currency' => isset($Currency) ? $Currency : null,
                                'NumericValue' => isset($NumericValue) ? $NumericValue : null,
                            ]);
                        }
                        // TrPexSetting::create([
                        //     'TIDNO' => isset($TIDNO) ? $TIDNO : null,
                        //     'is_active' => false
                        // ]);
                    }

                    
                }
            }

            $a++;

        }


        
    }

    public function batchSize(): int
    {
        return 500000;
    }
    
    public function chunkSize(): int
    {
        return 500000;
    }
}
