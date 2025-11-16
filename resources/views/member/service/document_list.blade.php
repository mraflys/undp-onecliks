@include('member.filter._date_range', [
  'btn_search_id' => 'btnSearchDocument',
  'start_date_id' => 'start_date_do',
  'end_date_id' => 'end_date_do']
)
<div class="modal fade" id="listModal" tabindex="-1" role="dialog"  aria-labelledby="listModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
      <div class="modal-content">
          <div class="modal-header">
              <h5 class="title" id="listModalLabel">Dokument List</h5>
          </div>
          <div class="modal-body">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>Workflow Name</th>
                    <th>Service Name</th>
                    <th>Document Name</th>
                  </tr>
                </thead>
                <tbody id="listDocUl">
                </tbody>
              </table>
              <div class="modal-footer">
                <button type="button" class="btn btn-link btn-list-cancle" data-dismiss="modal">Cancel</button>
              </div>
          </div>
          
      </div>
  </div>
</div>
<table class="table table-striped" id="mytableDocument">
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
  function document_list(id){
    $("#loadingProject").html("<?=\App\GeneralHelper::dt_loading_component();?>")
    $.ajax({
      type: 'POST',
      url: '{!! route('myservices.detail_document') !!}',
      data: {
          id: id
      },
      success: function(data) {
        console.log(data[0]);
        $("#loadingProject").html("<?=\App\GeneralHelper::message_dismissable('success', 'Project have been Open');?>")
        const dataLength = data.length;
        var html = '';
        for(let dataStart = 0; dataStart < dataLength; dataStart++){
          html += '<tr><td>'+data[dataStart].name_workflow+'</td><td>'+data[dataStart].name_service+'</td><td><a href="'+data[dataStart].document_url+'" target="_blank">'+data[dataStart].document_name+'</a></td></tr>';
        }
        $('#listDocUl').html(html);
      },
      error: function(data) {

      }
    });
    
  }
  function showTableDocument(){
      $("#mytableDocument").DataTable().destroy();
      var oTable = $('#mytableDocument').DataTable({
        dom: 'lBfrtip',
        buttons: [
            { extend: 'excel', text: "<i class='fa fa-download'> Excel</i>" }
        ],
        lengthMenu: [[ 10, 25, 50, -1 ],['10', '25', '50', 'All']],
        "processing": true,
        language: { processing: "<?=\App\GeneralHelper::dt_loading_component();?>" },
        "serverSide": true,
        "ajax": {
          "url": "<?=route('myservices.list_document');?>",
          "type": "GET",
          data: function(d){
            d.start_date = $("#start_date_do").val();
            d.end_date = $("#end_date_do").val();
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
        // rowCallback: function( row, data, index ) {
        //   var api = this.api();
        //   $('td:eq(0)', row).html( index + (api.page() * api.page.len()) + 1);
        // },
      });
    }

  $(function(){
    showTableDocument();
    $("#btnSearchDocument").click(function(){ showTableDocument(); });
  });
</script>