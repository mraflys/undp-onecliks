@extends((!$print) ? 'admin.index' : 'admin.print_template')

@section('css')

<style>

  body {

    font-size:14px;

  }

</style>

@endsection

@section('content')



@include('admin.messages')

<style>

</style>

<div class=""><h5 class="alert alert-primary">United Nations Development Programme</h5></div>

<div style="float:right;">

  <img src="{{ public_path('/assets/images/undp-logo.png') }}" style="height:3.5rem;width:auto;">

  <table class="table">

    <tbody>

      <tr>

        <td class="td-judul" style="width:100px;">Invoice No </td>

        <td>: &nbsp; <?=$detail->invoice_no; ?></td>

      </tr>

      <tr>

        <td class="td-judul" style="width:100px;">To </td>

        <td>: &nbsp; <?=$detail->agency_name;?></td>

      </tr>

      <tr>

        <td class="td-judul" style="width:100px;">Date </td>

        <td>: &nbsp; <?=$detail->date_finalized;?></td>

      </tr>

      <tr>

        <td class="td-judul" style="width:100px;">Issued by </td>

        <td>: &nbsp; <?=$detail->creator->person_name;?></td>

      </tr>

      <tr>

        <td class="td-judul" style="width:100px;">Due Date </td>

        <td>: &nbsp; <?=($detail->date_due_payment);?></td>

      </tr>

    </tbody>

  </table>

</div>

<div style="float:left;margin-top:60px">

  To: <br> {{$detail->agency_name}}

</div>

<?php

    $total_amount_local_table = 0;

    $total_amount_table = 0;

    if(!empty($details)) {

      $no = 1;

      foreach($details as $row) {

        $total_amount_local_table += $row->amount_billing_local;

        $total_amount_table += $row->amount_billing;

      }

    }

  ?>

<div style="text-align:center;margin-top:180px">

  <h4>INVOICE / (CREDIT ADVICE)</h4>

  <div style="width:100%">

    <table style="padding-left:20px;margin-right:50px;width:100%; text-align:center;border-collapse: collapse;border: 1px solid;">

      <thead>

        <tr >

          <th style="border: 1px solid;padding:10px">Description</th>

          <th style="border: 1px solid;padding:10px">USD Amount</th>

          <th style="border: 1px solid;padding:10px">Equivalent amount in IDR</th>

        </tr>

      </thead>

      <tbody>

        <tr>

          <td style="border: 1px solid;">{{$details[0]->invoice_description}}</td>

          <td style="border: 1px solid;">US$ {{number_format($total_amount_table, 2)}}</td>

          <td style="border: 1px solid;">IDR {{number_format($total_amount_local_table, 2)}}</td>

        </tr>

      </tbody>

    </table>

    <div style="padding-left:20pxwidth:90%;text-align:right;margin-top:3px;margin-bottom:3px;">UNORE 1 USD = IDR {{number_format($detail->unore, 2)}}</div>

    <hr style="padding-left:20px;margin-right:10px;width:95%;">

    <div style="padding-left:50px;margin-right:50px;width:100%;margin-top:3px;margin-bottom:3px;">

      <table>

        <tbody>

          <tr>

            <td style="vertical-align: top;">a.</td>

            <td>For payment made by GLJE, please review and confirm attached Chart of Account or ULO no., Agency ref. no., and project/account no., or</td>

          </tr>

          <tr>

            <td style="vertical-align: top;">b.</td>

            <td>For payment made by bank transfer, please quote the invoice no. and send the payment advice to frmu.id@undp.org</td>

          </tr>

        </tbody>

      </table>

    </div>

    <br>

    <div style="text-align:left;padding-left:80px;margin-right:80px;width:100%;margin-top:3px;margin-bottom:3px;">

      Payment in Indonesian Rupiah to:

      <table>

        <tbody>

          <tr>

            <td style="width:70px"><b>Account Number</b></td>

            <td style="width:4px"><b>:</b></td>

            <td style="width:170px"><b>306-006068-45</b></td>

          </tr>

          <tr>

            <td style="width:70px"><b>Account Name</b></td>

            <td style="width:4px"><b>:</b></td>

            <td style="width:170px"><b>UNDP Representative in Indonesia (Rupiah) Account</b></td>

          </tr>

          <tr>

            <td style="width:70px"><b>Bank Name</b></td>

            <td style="width:4px"><b>:</b></td>

            <td style="width:170px"><b>Standard Chartered Bank</b></td>

          </tr>

          <tr>

            <td style="width:70px"><b>Bank Address</b></td>

            <td style="width:4px"><b>:</b></td>

            <td style="width:170px"><b>Menara Standard Chartered Bank</b></td>

          </tr>

        </tbody>

      </table>

    </div>

    <div style="margin-left:100px;margin-right:100px;width:100%;text-align:left;margin-top:60px;margin-bottom:3px;">Thank you for using our services.

    </div>

    <div style="margin-left:100px;margin-right:100px;width:100%;text-align:left;margin-top:5px;margin-bottom:3px;">

      <span style="font-size:12px;"><i>This document is computer generated, therefore no signature is required</i>

      </span>

      <br>

      <span style="font-size:12px;">Menara Thamrin Building 8th Floor, Jalan MH. Thamrin kav. 3, Jakarta 10250. P.O Box 2338, Jakarta 10250. Indonesia

        Tel +62 (21) 2980 2300. Fax +62 (21) 314 5251. E-mail: registry.id@undp.org. http://www.undp.or.id

      </span>

    </div>

  </div>

</div>



  

<br />

<form id="frm" method="post" action="{{ route('mybillings.pay') }}">

  {{ csrf_field() }}

  <input type="hidden" name="id_billing" value="<?=$detail->id_billing; ?>"/>

  <div class="table-generator">

  <table id="billing-data" class="table table-bordered table-striped" <?=$print ? "border='1' cellpadding='3' cellspacing='0' " : '';?> style="font-size:12px">

    <thead>

      <tr>

        <th valign="top"><input type="checkbox" id="select_all" checked="checked"/></th>

        <th valign="top">Ticket</th>

        <th valign="top">Service Name</th>

        <th valign="top">Description</th>

        <th valign="top">Requester Name</th>

        <th valign="top">Agency</th>

        <th valign="top">QTY</th>

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

        <td><?=$row->person_name_buyer;?></td>

        <td><?=$row->agency_unit_code;?></td>

        <td><?=$row->qty;?></td>

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

        <tr><td colspan="9">No Billing yet.</td></tr>

      <?php } ?>

      <tr>

        <td colspan="7">Total</td>

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

