@extends('admin.index')
@section('content')
  <p><a href="{{ route('currencies.create') }}" class="btn btn-success"><i class='fa fa-plus'></i> Add Data</a></p>
  <hr>
  <table class="table table-striped" id="mytable">
    <thead>
      <tr>
        <th style="width: 3%">#</th>
        <th>Name</th>
        <th>Code</th>
        <th>Description</th>
        <th>Status</th>
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
            url: "<?=URL::to('admin/currencies');?>" + '/' + id,
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
        "processing": true,
        "serverSide": true,
        "ajax": "<?=URL::to('api/data/currencies');?>",
        "columns": [
          { data: 'currency_name', name: 'currency_name' },
          { data: 'currency_name', name: 'currency_name' },
          { data: 'currency_code', name: 'currency_code' },
          { data: 'description', name: 'description' },
          { data: 'is_active', name: 'is_active' },
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
