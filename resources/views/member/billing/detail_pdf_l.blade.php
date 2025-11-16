@extends((!$print) ? 'admin.index' : 'admin.print_template')
@section('content')

@include('admin.messages')
<br />
<form id="frm" method="post" action="{{ route('mybillings.pay') }}">
  {{ csrf_field() }}
  <input type="hidden" name="id_billing" value="<?=$detail->id_billing; ?>"/>
  <div class="table-generator">
  <table id="billing-data" class="table table-bordered table-striped" <?=$print ? "border='1' cellpadding='3' cellspacing='0' " : '';?>>
    <thead>
      <tr>
        <th valign="top"><input type="checkbox" id="select_all" checked="checked"/></th>
        <th valign="top">Ticket</th>
        <th valign="top">Service Name</th>
        <th valign="top">Description</th>
        <th valign="top">IDR Amount</th>
        <th valign="top">USD Amount</th>
        <th valign="top">Payment Date</th>
      </tr>
      </thead>
    <tbody>
      <?php
        $total_amount_local = 0;
        $total_amount = 0;
        if(!empty($details)) {
          $no = 1;
          foreach($details as $row) {
            $total_amount_local += $row->amount_billing_local;
            $total_amount += $row->amount_billing;
      ?>
      <tr>
        <td><?=$no++;?></td>
        <td><?=$row->transaction_code;?></td>
        <td><?=$row->service_name;?></td>
        <td><?=$row->description;?></td>
        <td style="text-align: right;"><?=number_format($row->amount_billing_local, 2);?></td>
        <td style="text-align: right;"><?=number_format($row->amount_billing, 2);?></td>
        <td>
          @if (!$print)
            <input type="text" class="date" id="date_payments[<?=$row->id_billing_detail;?>]" name="date_payments[<?=$row->id_billing_detail?>]" readonly="roweadonly" value="<?=$row->date_payment == "-" ? "" : ($row->date_payment);?>"/>
          @else
            <?=$row->date_payment == "-" ? "" : ($row->date_payment);?>
          @endif
        </td>
      </tr>
      <?php }} else { ?>
        <tr><td colspan="7">No Billing yet.</td></tr>
      <?php } ?>
      <tr>
        <td colspan="4">Total</td>
        <td style="text-align: right;"><?=number_format($total_amount_local, 2)?></td>
        <td style="text-align: right;"><?=number_format($total_amount, 2)?></td>
        <td></td>
      </tr>
    </tbody>
  </table>
  </div>
  <br />
  @if (!$print)
    <div>
      <div style="float: left;"><a id="btn_back" class="btn btn-primary" href="<?=route("mybillings.index");?>">Back to Invoice List</a></div>
      <div id="btn_pay" style="float: right;"><button class="btn btn-success">Pay</button></div>
      <div style="float: right;"><a id="btn_print" class="btn btn-default" href="<?=route("mybillings.print", [$detail->id_billing]);?>" target='_blank'>PDF</a></div>
    </div>
  @endif
</form>

<script>
  $(".date").datepicker( { 
    dateFormat: 'dd-M-yy',
          maxDate: "0D"
  });
</script>
@endsection
