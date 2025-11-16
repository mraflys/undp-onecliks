@extends('admin.index')
@section('content')
<form class="form-horizontal m-b-10" role="form">
    <div class="form-group">
        <label class="col-sm-3 control-label" style="padding-top: 0px;">View Invoice Date Started from</label>
        <div class="col-sm-7">
            <div class="input-group input-daterange">
                <input type="text" id="date1" class="form-control" value="<?=Date('Y-m-d', strtotime('first day of this month'));?>"> &nbsp;&nbsp;  
                <input type="text" id="date2" class="form-control" value="<?=Date('Y-m-d', strtotime('last day of this month'));?>">
                &nbsp; <button type="button" class="btn btn-primary" id="btnSearch">Show Report</button>
            </div>
        </div>
    </div>
</form>

<div class="col-sm-12">
    <p><h3>Invoice Issued</h3></p>

    <div id="print"></div>
    <table class="table table-striped table-bordered" id="mytable" colspan="0" width="100%">
        <thead>
            <tr>
                <!-- <th rowspan="2">No</th> -->
                <th rowspan="2">Invoice Number</th>
                <th rowspan="2">Invoice Date</th>
                <th rowspan="2">Agency Name</th>
                <th colspan="2">Total Service Fee</th>
                <th rowspan="2">Payment Date</th>
                <th rowspan="2">Due Date</th>
                <th colspan="2">Amount Paid</th>
                <th colspan="4">Aging of Receivables (days)</th>
            </tr>
            <tr>
                <th>USD</th>
                <th>IDR</th>
                <th>USD</th>
                <th>IDR</th>
                <th>0-30</th>
                <th>31-60</th>
                <th>61-90</th>
                <th>&gt;90</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<script type="text/javascript">
    function showTable(){
      var date1 = $("#date1").val();
      var date2 = $("#date2").val();

      $("#mytable").DataTable().destroy();
      var oTable = $('#mytable').DataTable({
        dom: generalDTOptions,
        buttons: generalDTButtons(),
        lengthMenu: generalDTLengths,
        "processing": true,
        "serverSide": true,
        ajax: {
          "url": "<?=route('myreport.invoice_issue_list');?>",
          "type": "GET",
          "data": function(dt){
            dt.date1 = date1;
            dt.date2 = date2;
          }
        },
        "columns": [
          { data: 'id_billing', name: 'id_billing' },
          { data: 'date_created', name: 'date_created' },
          { data: 'agency_name', name: 'agency_name' },
          { data: 'amount_billing', name: 'amount_billing' },
          { data: 'amount_billing_local', name: 'amount_billing_local' },
          { data: 'date_payment', name: 'date_payment' },
          { data: 'date_due_payment', name: 'date_due_payment' },
          { data: 'amount_paid', name: 'amount_paid' },
          { data: 'amount_paid_local', name: 'amount_paid_local' },
          { data: 'aging30', name: 'aging', orderable: true, searchable: false },
          { data: 'aging60', name: 'aging', orderable: true, searchable: false },
          { data: 'aging90', name: 'aging', orderable: true, searchable: false },
          { data: 'aging100', name: 'aging', orderable: true, searchable: false },
        ],
      });
    }
    $(document).ready(function() {
        showTable();
        $('.input-daterange').datepicker({
          format: 'yyyy-mm-dd'
        });
        $("#btnSearch").click(function(){
          showTable();
        })
    });

</script>
@endsection