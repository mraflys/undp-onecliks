@extends('admin.index')
@section('content')

<form class="form-horizontal m-b-10" role="form">
    <div class="form-group">
        <label class="col-sm-2">Search</label>
        <div class="col-sm-10">
            <input class="form-control" type="text" id="search">
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2">Search In</label>
        <div class="col-sm-10">
            <select id="search_in" class="form-control">
                <option value="all">All</option>
                <option value="service">Service Name</option>
                <option value="short_desc">Short Description</option>
                <option value="info_value">Info Value</option>
                <option value="buyer">Person Buyer Name</option>
            </select>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label" style="padding-top: 0px;">View Date Period from</label>
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
        <p class="text text-center"><center><h3>Search Engine</h3></center></p>
    </div>
    
    <div class="col-sm-12">
        <br>
        <table id="mytable" class="table table-striped table-bordered" colspan="0" width="100%">
            <thead>
              <tr>
                <th class="sort" field="transaction_code" title="Order by Ticket ID">Ticket ID</th>
                <th class="sort" field="description" title="Order by Service Name">Service Name</th>
                <th class="sort" field="kode" title="Order by Service Name">Found At</th>
                <th class="sort" field="status_name" title="Order by Status">Status</th>
              </tr>
            </thead>
            </thead>
           <tbody></tbody>
        </table>
    </div>
</div>

<script type="text/javascript">
    function showTable(){
      var search = $("#search").val();
      var searchIn = $("#search_in").val();
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
          "url": "<?=route('myreport.search_engine_list');?>",
          "type": "GET",
          "data": function(dt){
            dt.search = search;
            dt.searchIn = searchIn;
            dt.date1 = date1;
            dt.date2 = date2;
          }
        },
        "columns": [
          { data: 'transaction_code', name: 'transaction_code' },
          { data: 'description', name: 'description' },
          { data: 'kode', name: 'kode' },
          { data: 'status_name', name: 'status_name' },
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