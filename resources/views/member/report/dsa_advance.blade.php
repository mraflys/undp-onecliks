@extends('admin.index')
@section('content')

<form class="form-horizontal m-b-10" role="form">
    <div class="form-group">
        <label class="col-sm-3 control-label" style="padding-top: 0px;">View Return Date from</label>
        <div class="col-sm-7">
            <div class="input-group input-daterange">
                <input type="text" id="date1" class="form-control" value="<?=Date('Y-m-d', strtotime('first day of this month'));?>"> &nbsp;&nbsp;  
                <input type="text" id="date2" class="form-control" value="<?=Date('Y-m-d', strtotime('last day of this month'));?>">
            </div>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2">Status</label>
        <div class="col-sm-7">
            <select id="status" class="form-control">
                <option value="0">All</option>
                <option value="2">Ongoing</option>
                <option value="5">Done</option>
            </select>
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
        <p class="text text-center"><center><h3>DSA Advance</h3></center></p>
    </div>
    
    <div class="col-sm-12">
        <br>
        <table id="mytable" class="table table-striped table-bordered" colspan="0" width="100%">
        <thead>
            <tr>
                <!-- <th rowspan="2">No</th> -->
                <th rowspan="2" class="sort" field="name" title="Order by Person Name">Name</th>
                <th rowspan="2" class="sort" field="voucher" title="Order by Advanced Voucher No">Adv Voucher No</th>
                <th rowspan="2" class="sort" field="departure_date" title="Order by Departure Date">Departure Date</th>
                <th rowspan="2" class="sort asc" field="return_date" title="Order by Return Date">Return Date</th>
                <th rowspan="2" class="sort" field="settlement_date" title="Order by Settlement Date">Settlement Date</th>
                <th rowspan="2" class="sort" field="settlement_voucher" title="Order by Settlement Voucher">Settlement Voucher
                </th>
                <th rowspan="2" class="sort" field="transaction_code" title="Order by Ticket ID">Ticket No</th>
                <th colspan="4">Aging of Outstanding Advance (days)</th>
            </tr>
            <tr>
                <th>0-30</th>
                <th>31-60</th>
                <th>61-90</th>
                <th>&gt;90</th>
            </tr>
        </thead>
    </table>
    </div>
</div>
<div class="row">
    <div class="col-sm-12">
      <p class="text text-center"><center><h3>DSA Advance With Unidentified Return Date</h3></center></p>
    </div>
    <div class="col-sm-12">
      <br>
      <table id="unidentified" class="table table-striped table-bordered" colspan="0" width="100%">
          <thead>
              <tr>
                  <th>No</th>
                  <th title="Order by Person Name">Name</th>
                  <th title="Order by Advanced Voucher No">Adv Voucher No</th>
                  <th title="Order by Departure Date">Departure Date</th>
                  <th title="Order by Return Date">Return Date</th>
                  <th title="Order by Settlement Date">Settlement Date</th>
                  <th title="Order by Settlement Voucher">Settlement Voucher</th>
                  <th title="Order by Ticket ID">Ticket No</th>
              </tr>
          </thead>
          <tbody></tbody>
      </table>
    </div>
</div>

<script type="text/javascript">
    function showTable(){
      $("#mytable").DataTable().destroy();
      var oTable = $('#mytable').DataTable({
        dom: generalDTOptions,
        buttons: generalDTButtons(),
        lengthMenu: generalDTLengths,
        "processing": true,
        "serverSide": true,
        ajax: {
          "url": "<?=route('myreport.dsa_advance_list');?>",
          "type": "GET",
          "data": function(dt){
            dt.date1 = $("#date1").val();
            dt.date2 = $("#date2").val();
            dt.status = $("#status").val();
          }
        },
        "columns": [
          { data: 'name', name: 'name' },
          { data: 'voucher', name: 'voucher' },
          { data: 'departure_date', name: 'departure_date' },
          { data: 'return_date', name: 'return_date' },
          { data: 'settlement_date', name: 'settlement_date' },
          { data: 'settlement_voucher', name: 'settlement_voucher' },
          { data: 'transaction_code', name: 'transaction_code' },
          { data: 'aging30', name: 'aging', orderable: true, searchable: false },
          { data: 'aging60', name: 'aging', orderable: true, searchable: false },
          { data: 'aging90', name: 'aging', orderable: true, searchable: false },
          { data: 'aging100', name: 'aging', orderable: true, searchable: false },
        ],
      });
    }

    function showTableNull(){
      $("#unidentified").DataTable().destroy();
      var oTable = $('#unidentified').DataTable({
        dom: generalDTOptions,
        buttons: generalDTButtons(),
        lengthMenu: generalDTLengths,
        "processing": true,
        "serverSide": true,
        ajax: {
          "url": "<?=route('myreport.dsa_advance_null_list');?>",
          "type": "GET",
          "data": function(dt){
            dt.status = $("#status").val();
          }
        },
        "columns": [
          { data: 'name', name: 'name' },
          { data: 'voucher', name: 'voucher' },
          { data: 'departure_date', name: 'departure_date' },
          { data: 'return_date', name: 'return_date' },
          { data: 'settlement_date', name: 'settlement_date' },
          { data: 'settlement_voucher', name: 'settlement_voucher' },
          { data: 'transaction_code', name: 'transaction_code' },
        ]
      });
    }

    $(document).ready(function() {
        showTable();
        showTableNull();
        $('.input-daterange').datepicker({
          format: 'yyyy-mm-dd'
        });
        $("#btnSearch").click(function(){
          showTable();
          showTableNull();
        })
    });

</script>


@endsection