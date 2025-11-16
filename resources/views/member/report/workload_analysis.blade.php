@extends('admin.index')
@section('content')
<form class="form-horizontal m-b-10" role="form">
    <div class="form-group">
        <label class="col-sm-3 control-label" style="padding-top: 0px;">View Date Started from</label>
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
    <div class="col-sm-12">
        <p class="text text-center"><center><h3>Workload Analysis</h3></center></p>
        <br>
    </div>

    <table id="mytable" class="table table-striped table-bordered nowrap" colspan="0" width="100%">
        <thead>
            <tr>
                <th class="sort" field="person_name" title="Order by Person Name">Person Name</th>
                <th class="sort" field="unit_name" title="Order by Service Unit Name">Service Unit</th>
                <th class="sort" field="service" >Number of Ticket</th>
                <th class="sort" field="workflow" >Number of Workflow</th>
            </tr>
        </thead>
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
          "url": "<?=route('myreport.workload_analysis_list');?>",
          "type": "GET",
          "data": function(dt){
            dt.date1 = date1;
            dt.date2 = date2;
          }
        },
        "columns": [
          { data: 'person_name', name: 'person_name' },
          { data: 'unit_name', name: 'unit_name' },
          { data: 'service', name: 'service', orderable: true, searchable: false },
          { data: 'workflow', name: 'workflow', orderable: true, searchable: false },
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