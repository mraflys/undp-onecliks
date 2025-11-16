@extends('admin.index')
@section('content')
  <p><a href="{{ route('users.create') }}" class="btn btn-success"><i class='fa fa-plus'></i> Add Data</a></p>
  <hr>
  <table class="table table-striped" id="mytable">
    <thead>
      <tr>
        <th style="width: 3%">#</th>
        <th>Username</th>
        <th>Person Name</th>
        <th>Role</th>
        <th>Country</th>
        <th>Unit</th>
        <th>Status</th>
        <th>Last Login</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>

  <script type="text/javascript">

    function deleteRow(id){
      swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes'
      }).then(function(result) {
        if (result.value) { 
          $.ajax({
            url: "<?=URL::to('admin/users');?>" + '/' + id,
            type: 'POST',  // user.destroy
            data: {
              "_token": "{{ csrf_token() }}",
              "_method" : 'DELETE',
            },
            success: function(result) {
              swal.fire('Deleted!','Your file has been deleted.','success'); 
              showTable();
            },error: function(){
              swal.fire('ERROR!','DATA can not be removed.','error'); 
            }
          });
        }
      });
    }

    function showTable(){
      $("#mytable").DataTable().destroy();
      var url = "<?=URL::to('api/data/users');?>";
      @if (isset($with_deleted) && $with_deleted)
        url += "?with_deleted=true";
      @endif
      var oTable = $('#mytable').DataTable({
        dom: 'lBfrtip',
        buttons: [
            { extend: 'excel', text: "<i class='fa fa-download'> Excel</i>" }
        ],
        lengthMenu: [[ 10, 25, 50, -1 ],['10', '25', '50', 'All']],
        "processing": true,
        "serverSide": true,
        "ajax": url,
        "columns": [
          { data: 'user_name', name: 'user_name' },
          { data: 'user_name', name: 'user_name' },
          { data: 'person_name', name: 'person_name' },
          { data: 'role_name', name: 'sec_role.role_name' },
          { data: 'country_name', name: 'ms_country.country_name' },
          { data: 'agency_unit_name', name: 'ms_agency_unit.agency_unit_name' },
          { data: 'is_active', name: 'sec_user.is_active' },
          { data: 'date_last_login', name: 'sec_user.date_last_login' },
          { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        rowCallback: function( row, data, index ) {
          var api = this.api();
          $('td:eq(0)', row).html( index + (api.page() * api.page.len()) + 1);
        },
      });
    }

    $(document).ready(function(){
      showTable();
    });
  </script>
@endsection
