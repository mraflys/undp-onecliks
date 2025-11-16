@extends('admin.index')
@section('content')

<form class="form-horizontal m-b-10" role="form">
    <div class="form-group">
        <label class="col-sm-2">Search</label>
        <div class="col-sm-7">
            <input class="form-control" type="text" id="search">
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label" style="padding-top: 0px;">Ticket Completion Date From</label>
        <div class="col-sm-7">
            <div class="input-group input-daterange">
                <input type="text" id="date1" class="form-control" value="<?=Date('Y-m-d', strtotime('first day of this month'));?>"> &nbsp;&nbsp;  
                <input type="text" id="date2" class="form-control" value="<?=Date('Y-m-d', strtotime('last day of this month'));?>">
            </div>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2"></label>
        <div class="col-sm-10">
            <button type="button" class="btn btn-primary" id="btnSearch">Show Report</button>
        </div>
    </div>
</form>

<div class="row">
    <div class="col-sm-12">
        <p class="text text-center"><center><h3>List of Completed Tickets*</h3></center></p>
    </div>
    
    <div class="col-sm-12">
        <br>
        <table id="mytable" class="table table-striped table-bordered nowrap" colspan="0" width="100%">
        <thead>
            <tr>
                <!-- <th style="width: 40px;">No</th> -->
                <th class="sort" field="transaction_code" title="Order by Transaction">Ticket ID</th>
                <th class="sort" field="service_name" title="Order by Service Name">Service Name</th>
                <th class="sort" field="glje_no" title="Order by GLJE Number">GLJE Number</th>
                <th class="sort" field="glje_date" title="Order by GLJE Date">GLJE Date</th>
                <th class="sort" field="invoice_no" title="Order by Invoice Number">Invoice Number</th>
                <th class="sort" field="invoice_date" title="Order by GLJE Number">Invoice Date</th>
                <th class="sort" field="service_price" title="Order by GLJE Number">Price</th>
            </tr>
        </thead>
    </table>
    </div>
</div>

<script type="text/javascript">
    function showTable(){
      var search = $("#search").val();
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
          "url": "<?=route('myreport.complete_ticket_list');?>",
          "type": "GET",
          "data": function(dt){
            dt.search = search;
            dt.date1 = date1;
            dt.date2 = date2;
          }
        },
        "columns": [
          { data: 'transaction_code', name: 'transaction_code' },
          { data: 'service_name', name: 'service_name' },
          { data: 'glje_no', name: 'glje_no' },
          { data: 'glje_date', name: 'glje_date' },
          { data: 'invoice_no', name: 'invoice_no' },
          { data: 'invoice_date', name: 'invoice_date' },
          { data: 'service_price', name: 'service_price' },
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