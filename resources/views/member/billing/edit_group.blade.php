@extends('admin.index')
@section('content')
@include('admin.messages')

<div class=""><h5 class="alert alert-primary">Billing Detail</h5></div>
<form id="frm" method="post" action="{{ route('mybillings.update_group', [$billing[0]->id_billing]) }}">
  <input type="hidden" name="id_billing" value="{{ $billing[0]->id_billing }}">
  {{ csrf_field() }}
  <table class="table table-striped table-bordered">
    <tbody>
      <tr>
        <td class="td-judul" style="width:90px;">To :</td>
        <td><?php echo $billing[0]->agency_name?></td>
      </tr>
      <tr>
        <td class="td-judul">Date :</td>
        <td><?php echo date('Y-m-d', strtotime($billing[0]->date_created));?></td>
      </tr>
      <tr>
        <td class="td-judul">Issued by :</td>
        <td><?php echo $billing[0]->issued_by;?></td>
      </tr>
      <tr>
        <td class="td-judul">Due Date :</td>
        <td><?php echo date('Y-m-d', strtotime($billing[0]->date_due_payment));?></td>
      </tr>
        <tr>
          <td class="td-judul">Description : </td>
          <td><textarea cols="100" rows="5" name="description" class="validate[required] form-control"><?php echo $billing[0]->invoice_description;?></textarea></td>
          </tr>
        <tr>
          <td class="td-judul">UNORE USD to IDR : </td>
          <td>
            <input type="text" name="unore-formatted" id="unore-formatted" class="validate[required] form-control" value="<?php echo $billing[0]->unore;?>"/>
            <input type="hidden" name="unore" id="unore"/>
            </td>
          </tr>
    </tbody>
  </table>
<br />
<div class="table-generator">
<table width="100%" id="billing-data" class="table table-striped table-bordered">
	<thead>
		<tr>
      <th><input type="checkbox" id="select_all"/></th>
      <th>Ticket</th>
			<th>Service Name</th>
			<th>Description</th>
      <th>IDR Amount</th>
      <th>USD Amount</th>
		</tr>
    </thead>
	<tbody>
    @foreach($billing as $row)
    <tr>
      <td>
        <input type="checkbox" class="ids" name="ids[<?php echo $row->id_transaction;?>]" checked="checked" 
        value="<?=$row->id_transaction;?>"/>
      </td>
      <td><?php echo $row->transaction_code;?></td>
      <td><?php echo $row->service_name;?></td>
      <td><?php echo $row->description;?></td>
      <td style="text-align: right;"><?php echo number_format($row->amount_billing_local, 2);?></td>
      <td style="text-align: right;"><?php echo number_format($row->amount_billing, 2);?>
      <input type="hidden" name="prices[<?=$row->id_transaction;?>]" value="<?=$row->service_price;?>"/></td>
    </tr>
    @endforeach
    
    @foreach($transaction as $row)
    <tr>
      <td>
        <input type="checkbox" class="ids" name="ids[<?php echo $row->id_transaction;?>]"
        value="<?=$row->id_transaction;?>"/>
      </td>
      <td><?php echo $row->transaction_code;?></td>
      <td><?php echo $row->service_name;?></td>
      <td><?php echo $row->description;?></td>
      <td style="text-align: right;">-</td>
      <td style="text-align: right;"><?php echo number_format($row->service_price, 2);?>
      <input type="hidden" name="prices[<?=$row->id_transaction;?>]" value="<?=$row->service_price;?>"/></td>
    </tr>
    @endforeach

    <tr>
      <td colspan="4">Total</td>
      <td style="text-align: right;"><?php echo number_format($billing[0]->total_amount_local, 2)?></td>
      <td style="text-align: right;"><?php echo number_format($billing[0]->total_amount, 2)?></td>
    </tr>
	</tbody>
</table>
</div>
<br />
<div>
  <div style="float: left;">
    <a id="btn_back" class="btn btn-primary" href="{{ route('mybillings.index') }}">Back to Invoice List</a>
  </div>
  <div id="btn_save" style="float: right;"><button class="btn btn-primary">Save</button></div>
</div>
</form>
<script>
  $(function(){
    $("#unore").val($("#unore-formatted").val());
    $("#select_all").change(function(){
      var status = $(this).is(":checked") ? true : false;
      $(".ids").prop("checked",status);
    });
  })
  // $("#unore-formatted").autoNumeric('init', {});
  $("#unore-formatted").change(function () {
    // $("#unore").val($(this).autoNumeric('get'));
    $("#unore").val($(this).val());
  });
</script>

@endsection