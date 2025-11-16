@extends('admin.index')
@section('content')

<form class="form-horizontal m-b-10" role="form">
    <div class="form-group">
        <label class="col-sm-2">Name</label>
        <div class="col-sm-10">
            <input class="form-control" type="text" id="name">
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2">Status</label>
        <div class="col-sm-10">
            <select id="status" class="form-control">
                <option value="all">All</option>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
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
        <p class="text text-center"><center><h3>User Registration</h3></center></p>
    </div>
    
    <div class="col-sm-12">
        <br>
        <table id="mytable" class="table table-striped table-bordered" colspan="0" width="100%">
            <thead>
                <tr>
                    <th style="width: 40px;">No</th>
                    <th class="sort" field="person_name" title="Order by Person Name">Person Name</th>
                    <th class="sort" field="user_name" title="Order by Email">Email</th>
                    <th class="sort" field="agency_code" title="Order by Agency Code">Agency</th>
                    <th class="sort" field="unit_name" title="Order by Unit Name" style="width: 199px">Unit</th>
                    <th class="sort" field="is_active" title="Order by Unit Name">Status</th>
                    <th class="sort" field="date_created" title="Order by Registered Date">Registered Date</th>
                </tr>
            </thead>
           <tbody></tbody>
        </table>
    </div>
</div>

<script type="text/javascript">
    function showTable(){
      var name = $("#name").val();
      var status = $("#status").val();

      $("#mytable").DataTable().destroy();
      var oTable = $('#mytable').DataTable({
        dom: generalDTOptions,
        buttons: generalDTButtons(),
        lengthMenu: generalDTLengths,
        "processing": true,
        "serverSide": true,
        ajax: {
          "url": "<?=route('myreport.user_registration_list');?>",
          "type": "GET",
          "data": function(dt){
            dt.name = name;
            dt.status = status;
          }
        },
        "columns": [
          { data: 'person_name', name: 'person_name' },
          { data: 'user_name', name: 'user_name' },
          { data: 'email', name: 'email' },
          { data: 'agency_unit_code', name: 'agency_unit_code' },
          { data: 'agency_unit_name', name: 'agency_unit_name' },
          { data: 'is_active', name: 'sec_user.is_active' },
          { data: 'date_created', name: 'sec_user.date_created' },
        ],
      });
    }
    $(document).ready(function() {
        showTable();
        $("#btnSearch").click(function(){
          showTable();
        })
    });

</script>


@endsection