<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TrPEX extends Model
{
    public $timestamps = false;
    protected $table = 'tr_PEX';
    protected $fillable = ['TIDNO','CalendarGroup','Index_','Name','Email','PositionDescr','Journal','Date','Fund','OperatingUnit','ImplementingAg','Donor','DeptID','Project','ProjAct','BankAccount','PCBusUnit','Position','GLUnit','ErnDedCd','ErnDedAcc','BaseAmount','Currency','NumericValue'];
}
