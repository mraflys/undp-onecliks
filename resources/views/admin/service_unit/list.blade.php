@extends('admin.index')
@section('content')
  <p><a href="{{ route('service_units.create') }}" class="btn btn-success"><i class='fa fa-plus'></i> Add Data</a></p>
  <hr>
  @include('member.filter._agency_by_country')
  <table class="table table-striped" id="mytable">
    <thead>
      <tr>
        <th style="width: 3%">#</th>
        <th>Name</th>
        <th>Code</th>
        <th>Parent</th>
        <th>Country</th>
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
            url: "<?=URL::to('admin/service_units');?>" + '/' + id,
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
        "ajax": {
          url: "<?=URL::to('api/data/service_units');?>",
          data: function(d){
            d.status = $("#status").val();
            d.id_country = $("#id_country").val();
            d.id_agency_unit = $("#id_agency_unit").val();
          },
        },
        "columns": [
          { data: 'agency_unit_name', name: 'agency_unit_name' },
          { data: 'agency_unit_name', name: 'agency_unit_name' },
          { data: 'agency_unit_code', name: 'agency_unit_code' },
          { data: 'parent_name', name: 'agency_unit_name' },
          { data: 'country_name', name: 'ms_country.country_name' },
          { data: 'is_active', name: 'ms_agency_unit.is_active' },
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
      $("#btnSearch").click(function(){ showTable(); });
    });
  </script>
@endsection
