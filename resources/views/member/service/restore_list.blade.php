<hr>
<?php
  $agencies = App\AgencyUnit::whereRaw("is_service_unit = 1 AND id_agency_unit_parent IS NULL")->orderBy('agency_unit_name')->get();
?>
<style type="text/css">
  .rate-selector [type=radio] { 
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
  }

  /* IMAGE STYLES */
  .rate-selector [type=radio] + img {
    cursor: pointer;
  }

  /* CHECKED STYLES */
  .rate-selector [type=radio]:checked + img {
    outline: 2px solid #f00;
  }
</style>
<div class="row" style="padding-left: 11px">
  <div class="col-sm-6">
    
    <!-- <div class="hiddenradio">
      <label>
        <input type="radio" name="test" value="small" checked>
        <img src="http://placehold.it/40x60/0bf/fff&text=A">
      </label>

      <label>
        <input type="radio" name="test" value="big">
        <img src="http://placehold.it/40x60/b0f/fff&text=B">
      </label>
    </div> -->
    
    <div id="loadingStatus"></div>
    <div class="form-group">
      <label>Agency</label>
      <select class="form-control select2" id="id_agency_unit">
        <option>&nbsp;</option>
        @foreach($agencies as $c)
          <option value="{{ $c->id_agency_unit }}">{{ $c->agency_unit_name }}</option>
        @endforeach
      </select>
    </div>
    <div class="form-group">
      <label for="exampleSelect1">Service Unit</label>
      <select class="form-control select2" id="id_service_unit" name="id_agency_unit_service"></select>
    </div>
    <div class="form-group">
      <label>Date</label>
      <table class="table">
        <tr>
          <td>
            From: <input type="date" name="start_date" id="start_date" class="form-control" placeholder="YYYY-mm-dd">
          </td>
          <td>
            To: <input type="date" name="end_date" id="end_date" class="form-control"  placeholder="YYYY-mm-dd">
          </td>
        </tr>
      </table>
    </div>
    <div class="form-group">
      <button class="btn btn-primary" id="btnSearch" type="button">Search</button>
      <p>&nbsp;</p>
    </div> 
  </div>
  <div class="col-sm-4">
    <div class="form-group">
      <label>Status</label>
      <select class="form-control" id="status">
        <option value=""></option>
        <option value="1">New Request</option>
        <option value="2">On Going</option>
        <option value="5">Complete</option>
      </select>
    </div>
    <div class="form-group">
      <label>View</label>
      <select class="form-control" id="mine">
        <option value=""></option>
        <option value="1">My Request Only</option>
      </select>
    </div>
  </div>
</div>

<table width="100%" class="table table-striped " id="mytableRestore">
	<thead>
		<tr>
      <th>#</th>
			<th valign="top" field="agency_service_name" title="Order by Agency">Agency</th>
			<th valign="top" field="transaction_code" title="Order by Ticket">Ticket No</th>
			<th valign="top" field="date_authorized" title="Order by Start Date">Create Date</th>
			<th valign="top" field="person_name_buyer" title="Order by Requester">Requester</th>
			<th valign="top" field="service_name" title="Order by Service Name">Service Name</th>
      <th valign="top" field="status_name" title="Order by Service Status">Service Status</th>
			<th valign="top" field="Action" title="Action">Action</th>
		</tr>
	</thead>	
	<tbody></tbody>
</table>

<script type="text/javascript">
       function showTable(){
      $("#mytableRestore").DataTable().destroy();
      var oTable = $('#mytableRestore').DataTable({
        dom: generalDTOptions,
        buttons: generalExcelDTButtons,
        lengthMenu: generalDTLengths,
        "processing": true,
        language: { processing: "<?=\App\GeneralHelper::dt_loading_component();?>" },
        "serverSide": true,
        "ajax": {
          url: "<?=route('myservices.restore_request_search');?>",
          data: function(d){
            d.status = $("#status").val();
            d.is_mine = $("#mine").val();
            d.id_service_unit = $("#id_service_unit").val();
            d.start_date = $("#start_date").val();
            d.end_date = $("#end_date").val();
          }
        },
        "columns": [
          { data: 'agency_unit_name', name: 'agency_unit_name' },
          { data: 'agency_unit_name', name: 'agency_unit_name' },
          { data: 'transaction_code', name: 'transaction_code' },
          { data: 'date_created', name: 'date_created' },
          { data: 'person_name_buyer', name: 'person_name_buyer' },
          { data: 'service_name', name: 'service_name' },
          { data: 'status_name', name: 'status_name' },
          { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        "order": [[4, "ASC"]],
        rowCallback: function( row, data, index ) {
          var api = this.api();
          $('td:eq(0)', row).html( index + (api.page() * api.page.len()) + 1);
        },
      });
    }

  $(function(){
    $(".select2").select2().next(".select2-container").css("width", "100%");
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
    })
		showTable();
    $("#btnSearch").click(function(){ showTable() });
  });
</script>
@include("member.filter._date_range_js")
