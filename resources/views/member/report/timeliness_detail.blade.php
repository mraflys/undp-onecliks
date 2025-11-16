@extends('admin.index')
@section('content')


<form class="form-horizontal m-b-10" role="form" method="POST" action="{{ route('myreport.timeliness_detail_post') }}">
    {{ csrf_field() }}
    <div class="form-group">
        <label class="col-sm-3">Search For</label>
        <div class="col-sm-7">
            <input class="form-control" type="text" id="search">
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3">Finished Date Started from</label>
        <div class="col-sm-7">
            <div class="input-group input-daterange">
                <input type="text" id="date1" class="form-control" value="<?=Date('Y-m-d', strtotime('first day of this month'));?>"> &nbsp;&nbsp;  
                <input type="text" id="date2" class="form-control" value="<?=Date('Y-m-d', strtotime('last day of this month'));?>">
                &nbsp; <button type="button" class="btn btn-primary" id="btnSearch">Show Report</button>
            </div>
        </div>
        <div class="col-sm-2">
        </div>
    </div>
</form>

<div class="col-sm-12">
    <div id="print"></div>
    <table id="mytable" class="table table-striped table-bordered" colspan="0" width="100%">
        <thead>
            <tr>
                <th>Ticket</th>
                <th>Requester</th>
                <th>Service</th>
                <th>Workflow Name</th>
                <th>Service Unit</th>
                <th>Date End</th>
                <th>PIC</th>
                <th>SLA</th>
                <th>Actual</th>
                <th>Delay</th>
            </tr>
        </thead>
    </table>
</div>

<script type="text/javascript">
    function showTable(){
      var date1 = $("#date1").val();
      var date2 = $("#date2").val();
      var search = $("#search").val();

      $("#mytable").DataTable().destroy();
      var oTable = $('#mytable').DataTable({
        dom: generalDTOptions,
        buttons: generalDTButtons(),
        lengthMenu: generalDTLengths,
        "processing": true,
        "serverSide": true,
        ajax: {
          "url": "<?=URL::to('member-area/report/timeliness_detail/data');?>",
          "type": "GET",
          "data": function(dt){
            dt.date1 = date1;
            dt.date2 = date2;
            dt.search = search;
          }
        },
        "columns": [
          { data: 'transaction_code', name: 'transaction_code' },
          { data: 'agency_code_buyer', name: 'agency_code_buyer' },
          { data: 'service_name', name: 'service_name' },
          { data: 'workflow_name', name: 'workflow_name' },
          { data: 'agency_unit_code', name: 'agency_unit_code' },
          { data: 'date_end_actual', name: 'date_end_actual' },
          { data: 'person_name', name: 'person_name' },
          { data: 'sla', name: 'sla', orderable: false, searchable: false },
          { data: 'actual', name: 'actual', orderable: false, searchable: false },
          { data: 'delay', name: 'delay', orderable: false, searchable: false },
        ],
        // rowCallback: function( row, data, index ) {
        //   var api = this.api();
        //   $('td:eq(0)', row).html( index + (api.page() * api.page.len()) + 1);
        // },
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