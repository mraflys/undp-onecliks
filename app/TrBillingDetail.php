<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TrBillingDetail extends Model
{
  protected $table = 'tr_billing_detail';
  protected $primaryKey = 'id_billing_detail';
  const UPDATED_AT = 'date_updated';
  const DELETED_AT = 'date_deleted';
  const CREATED_AT = 'date_created';

  public function billing(){
  	return $this->belongsTo('App\TrBilling', 'id_billing');
  }

  public function transaction(){
  	return $this->belongsTo('App\TrService', 'id_transaction');
  }
}
