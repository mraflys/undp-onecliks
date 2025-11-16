@extends('admin.index')
@section('content')
  <div class="col-sm-6">
    <h5 class="text-justify"><span class="text-primary">{{ $service->parent->service_name }}</span></h5>
    <table class="table">
      <tr><th class="text-right">Code</th><td>{{ $service->parent->service_code }}</td></tr>
      <tr><th class="text-right">Agency Unit</th><td>{{ $service->parent->agency->parent->agency_unit_name }}</td></tr>
      <tr><th class="text-right">Service Unit</th><td>{{ $service->parent->agency->agency_unit_name }}</td></tr>
      <tr><th class="text-right">Description</th><td>{{ $service->parent->description }}</td></tr>
      <tr><th class="text-right">Group Name</th><td>{{ $service->service_name }}</td></tr>
    </table>
  </div>

  <p>
    <a href="{{ route('pricelist.add',  ['id_service' => $service->id_service]) }}" class="btn btn-success"><i class='fa fa-plus'></i> Add Data</a>
    <a href="{{ route('service_list.workflow', [$service->parent->id_service]) }}" class="btn btn-default"><i class='fa fa-arrow-left'></i> Back </a>
  </p>
  <hr>
  <table class="table table-striped" id="mytable">
    <thead>
      <tr>
        <th style="width: 3%">#</th>
        <th>Currency</th>
        <th>Price/Unit</th>
        <th>Start Date</th>
        <th>End Date</th>
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
            url: "<?=URL::to('admin/pricelist');?>" + '/' + id,
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
        "ajax": "<?=URL::to('api/data/pricelist');?>" + "?id_service=" + <?=$service->id_service;?>,
        "columns": [
          { data: 'currency_name', name: 'currency_name', orderable: false, searchable: false },
          { data: 'currency_name', name: 'currency_name', orderable: false, searchable: false },
          { data: 'price', name: 'price' },
          { data: 'date_start_price', name: 'date_start_price' },
          { data: 'date_end_price', name: 'date_end_price' },
          { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        "order": [[ 3, "desc" ]],
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
