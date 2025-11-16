@extends('admin.index')
@section('content')
<form class="form-horizontal m-b-10" role="form">
    <div class="form-group">
        <label class="col-sm-3 control-label" style="padding-top: 0px;">Search Service Name or Unit Code by</label>
        <div class="col-sm-9">
            <input class="form-control" type="text" id="search">
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label" style="padding-top: 0px;">View Completion Date Started from</label>
        <div class="col-sm-9">
            <div class="input-group input-daterange">
                <input type="text" id="date1" class="form-control" value="<?=Date('Y-m-d', strtotime('first day of this month'));?>"> &nbsp;&nbsp;  
                <input type="text" id="date2" class="form-control" value="<?=Date('Y-m-d', strtotime('last day of this month'));?>">
                &nbsp; <button type="button" class="btn btn-primary" id="btnSearch">Show Report</button>
            </div>
        </div>
    </div>
</form>

<div class="row">
    <div class="col-sm-12">
        <p class="text text-center"><center><h3>Performance report based on rating from client *</h3></center></p>
        <br>
    </div>
    
    <table id="mytable" class="table table-striped table-bordered" colspan="0" width="100%">
        <thead>
            <tr>
                <!-- <th rowspan="2" style="width: 40px;">No</th> -->
                <th rowspan="2" class="sort" field="service_name" title="Order by Service Name">Service Name</th>
                <th rowspan="2" class="sort" field="agency_unit_code" title="Order by Service Unit">Service Unit</th>
                <th colspan="6" class="text-center">Rating</th>
            </tr>
            <tr>
                <th style="width: 75px;" class="sort" field="rate_0">Not rated yet</th>
                <th style="width: 75px;" class="sort" field="rate_1">Very Unsatisfied</th>
                <th style="width: 75px;" class="sort" field="rate_2">Unsatisfied</th>
                <th style="width: 75px;" class="sort" field="rate_3">Normal</th>
                <th style="width: 75px;" class="sort desc" field="rate_4">Satisfied</th>
                <th style="width: 75px;" class="sort" field="rate_5">Very Satisfied</th>
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
          "url": "<?=route('myreport.performance_list');?>",
          "type": "GET",
          "data": function(dt){
            dt.date1 = date1;
            dt.date2 = date2;
            dt.search = search;
          }
        },
        "columns": [
          { data: 'service_name', name: 'service_name' },
          { data: 'agency_unit_code', name: 'agency_unit_code' },
          { data: 'rate_0', name: 'rate_0', orderable: true },
          { data: 'rate_1', name: 'rate_1', orderable: true },
          { data: 'rate_2', name: 'rate_2', orderable: true },
          { data: 'rate_3', name: 'rate_3', orderable: true },
          { data: 'rate_4', name: 'rate_4', orderable: true },
          { data: 'rate_5', name: 'rate_5', orderable: true },
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