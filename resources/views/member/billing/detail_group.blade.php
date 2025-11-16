@extends('admin.index')
@section('content')

@include('admin.messages')

<div class=""><h5 class="alert alert-primary">Billing Detail</h5></div>
  <table class="table table-striped">
    <tbody>
      <tr>
        <td class="td-judul" style="width:50px;">To :</td>
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
        <td class="td-judul">Invoice Description :</td>
        <td><?php echo $billing[0]->invoice_description;?></td>
      </tr>
      <tr>
        <td class="td-judul">UNORE :</td>
        <td><?php echo number_format($billing[0]->unore, 2);?></td>
      </tr>
      <tr>
        <td class="td-judul">IDR Amount</td>
        <td><?php echo number_format($billing[0]->total_amount_billing_local, 2);?></td>
      </tr>
      <tr>
        <td class="td-judul">USD Amount</td>
        <td><?php echo number_format($billing[0]->total_amount_billing, 2);?></td>
      </tr>
    </tbody>
  </table>
<br />
<div class="bar-blue"><h6 class="alert alert-primary">Transaction Detail</h6></div>

<div class="table-generator" style="overflow-x: auto;">
<table width="100%" id="billing-data" class="table table-striped table-bordered">
  <thead>
    <tr>
      <th>Ticket</th>
      <th>Service Unit</th>
      <th>Service Name</th>
      <th>Short Description</th>
      <th>Requester Name</th>
      <th>Agency</th>
      <th>Price</th>
      <th>Qty</th>
      <th>Total Cost</th>
      <th>ACC</th>
      <th>OPU</th>
      <th>Fund</th>
      <th>Dept</th>
      <th>Impl. Agent</th>
      <th>Donor</th>
      <th>PCBU</th>
      <th>Project</th>
      <th>Activities</th>
      <th>Agt. Ref</th>
      <th>UOL Code</th>
      <th>Acc. Project</th>
    </tr>
    </thead>
  <tbody>
    <?php
      if(!empty($billing)) {
        foreach($billing as $row) {
    ?>
    <tr>
      <td><?php echo $row->transaction_code;?></td>
      <td><?php echo $row->unit_code_service;?></td>
      <td><?php echo $row->service_name;?></td>
      <td><?php echo $row->description;?></td>
      <td><?php echo $row->person_name_buyer;?></td>
      <td><?php echo $row->agency_code;?></td>
      @if($row->qty==0)
        <td style="text-align: right;">0</td>
      @else
        <td style="text-align: right;"><?php echo number_format($row->amount_billing/$row->qty, 2);?></td>
      @endif
      <td style="text-align: right;"><?php echo $row->qty;?></td>
      <td style="text-align: right;"><?php echo number_format($row->amount_billing, 2);?></td>
      <td><?php echo $row->acc;?></td>
      <td><?php echo $row->opu;?></td>
      <td><?php echo $row->fund;?></td>
      <td><?php echo $row->dept;?></td>
      <td><?php echo $row->imp_agent;?></td>
      <td><?php echo $row->donor;?></td>
      <td><?php echo $row->pcbu;?></td>
      <td><?php echo $row->project;?></td>
      <td><?php echo $row->activities;?></td>
      <td><?php echo $row->arn;?></td>
      <td><?php echo $row->ulo;?></td>
      <td><?php echo $row->project_no;?></td>

    </tr>
    <?php }} else { ?>
      <tr><td colspan="6">No Billing yet.</td></tr>
    <?php } ?>
  </tbody>
</table>
</div>
<br />
<div>
  <div style="float: left;"><a id="btn_back" class="action-content btn btn-primary" href="" value="<?php echo URL::to("billing/billing");?>">Back to Invoice List</a></div>
  <div style="float: left;">&nbsp;&nbsp; 
    <a id="btn_edit" class="action-content btn btn-primary" href="{{ route('mybillings.edit_group', [$billing[0]->id_billing]) }}">Edit</a></div>
  <div style="float: right;">&nbsp;&nbsp; 
    <a id="btn_finalized" class="action-content btn btn-primary" href="{{ route('mybillings.finalize', [$billing[0]->id_billing]) }}">Finalized</a></div>
  <div style="float: right;">
    <a id="btn_print" class="btn btn-default" href="{{ route('mybillings.edit_group', [$billing[0]->id_billing]) }}">PDF</a></div>
</div>

<script>
  $(".date").datepicker( { 
    dateFormat: 'dd-M-yy',
          maxDate: "0D"
  });
</script>
@endsection