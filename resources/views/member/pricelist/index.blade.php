@extends('admin.index')
@section('content')
<?php
  $has_detail = isset($detail) && ($detail != null);
?>
<?php
  $agencies = App\AgencyUnit::whereRaw("is_service_unit = 1 AND id_agency_unit_parent IS NULL")->orderBy('agency_unit_name')->get();
?>
<?php 
    if ($has_detail) {
      
      if (isset($source) && $source == 'tr_service') {
        $row_id = $detail->id_transaction ;
        $route = 'myrequests.update';
      }else {
        $row_id = $detail->id_draft;
        $route = 'myrequests.draft_update';
      }

      $url = route($route, [$row_id]);
      $method = 'POST';
      $id_agency_unit_service = $detail->id_agency_unit_service;
    }else{
      $url = route('myrequests.store');
      $method = 'POST';
      $id_agency_unit_service = old('id_agency_unit_service') ? old('id_agency_unit_service') : 0;
    }
  ?>
<hr>
<div class="col-lg-6">
  <form>
    <div class="form-group">
      <label>Period Date</label>
      <input type="text" id="period" class="form-control datepicker" placeholder="YYYY-mm-dd" value="{{ date('Y-m-d') }}">
    </div>
    <div class="form-group">
      <label>Agency</label>
      <select class="form-control select2" id="id_agency_unit">
        <option>-- Select Provider --</option>
        @foreach($agencies as $c)
          <option value="{{ $c->id_agency_unit }}">{{ $c->agency_unit_name }}</option>
        @endforeach
      </select>
    </div>
    <div class="form-group">
      <label for="exampleSelect1">Unit</label>
      <select class="form-control select2" required id="id_service_unit" name="id_agency_unit_service" <?=$has_detail ? 'disabled' : '';?>></select>
      <!-- <select class="form-control select2" id="id_service_unit" name="id_agency_unit_service"></select> -->
    </div>
    <div class="form-group">
      <label for="exampleSelect1">Service Name</label>
      <input type="text" id="service_name" class="form-control">
    </div>
    <div class="form-group">
      <label></label>
      <button type="button" class="btn btn-success" onclick="showTable()"><i class="fa fa-search"></i></button>
    </div>
  </form>
</div>
<table class="table table-striped " id="mytable"> 
	<thead>
		<tr>
			<th>#</th>
			<th>Service</th>
			<th>Agency</th>
			<th>Unit</th>
			<th>Price (USD)</th>
		</tr>
	</thead>
	<tbody></tbody>
</table>
<script type="text/javascript">
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
          url: "<?=route('mypricelist.list');?>",
          type: "GET",
          data: function(d){
            d.period = $("#period").val();
            d.id_service_unit = $("#id_service_unit").val();
            d.service_name = $("#service_name").val();
          }
        },
        "columns": [
          { data: 'service_name', name: 'ms_service.service_name' },
          { data: 'service_name', name: 'ms_service.service_name' },
          { data: 'agency_parent_code', name: 'agency_parent.agency_unit_code' },
          { data: 'unit_name', name: 'ms_agency_unit.agency_unit_name' },
          { data: 'price', name: 'ms_service_pricelist.price' },
        ],
        rowCallback: function( row, data, index ) {
          var api = this.api();
          $('td:eq(0)', row).html( index + (api.page() * api.page.len()) + 1);
        },
      });
    }

	$(function(){
		showTable();
    $(".select2").select2();
    $("#id_agency_unit").change(function(){
      $.ajax({
        url: "<?=route('api-list-agency-units-search-by');?>" + "?all=1&id_parent=" +$("#id_agency_unit").val(),
        dataType: 'json',
        beforeSend: function(){
          $("#loadingStatus").html("Loading ....");
        },
        success: function(data){
          $("#loadingStatus").html("");
          $("#id_service_unit").html("");
          $.each(data.data, function(k, value){
            $("#id_service_unit").append("<option value='"+value.id_agency_unit+"'>"+ value.agency_unit_name+"</option>");
          });
        }
      })
    });
	});
</script>
@endsection