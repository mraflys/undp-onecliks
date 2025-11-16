@extends('admin.index')
@section('content')
  <p><a href="{{ route('holidays.create') }}" class="btn btn-success"><i class='fa fa-plus'></i> Add Data</a></p>
  <hr>
  <table class="table table-striped" id="mytable">
    <thead>
      <tr>
        <th style="width: 3%">#</th>
        <th>Name</th>
        <th>Country</th>
        <th>Start</th>
        <th>End</th>
        <th>Description</th>
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
            url: "<?=URL::to('admin/holidays');?>" + '/' + id,
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
      var oTable = $('#mytable').DataTable({
        dom: 'lBfrtip',
        buttons: [
            { extend: 'excel', text: "<i class='fa fa-download'> Excel</i>" }
        ],
        lengthMenu: [[ 10, 25, 50, -1 ],['10', '25', '50', 'All']],
        "processing": true,
        "serverSide": true,
        "ajax": "<?=URL::to('api/data/holidays');?>",
        "columns": [
          { data: 'holiday_name', name: 'holiday_name' },
          { data: 'holiday_name', name: 'holiday_name' },
          { data: 'country_name', name: 'ms_country.country_name' },
          { data: 'date_holiday_start', name: 'date_holiday_start' },
          { data: 'date_holiday_end', name: 'date_holiday_end' },
          { data: 'description', name: 'description' },
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
