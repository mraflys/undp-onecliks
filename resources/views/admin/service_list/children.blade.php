@extends('admin.index')
@section('content')
<div class="col-sm-6">
  <h5 class="text-justify"><span class="text-primary">{{ $detail->service_name }}</span></h5>
  <table class="table" id="mytable">
    <tr><th class="text-right">Code</th><td>{{ $detail->service_code }}</td></tr>
    <tr><th class="text-right">Agency Unit</th><td>{{ $detail->agency->parent->agency_unit_name }}</td></tr>
    <tr><th class="text-right">Service Unit</th><td>{{ $detail->agency->agency_unit_name }}</td></tr>
    <tr><th class="text-right">Description</th><td>{{ $detail->description }}</td></tr>
  </table>
</div>
<div class="col-sm-12">
  <h5><span class="alert alert-primary">Service Workflow </span></h5>
  <p>&nbsp;<a href="#" id="btnAddService" class="btn btn-clean"><i class='fa fa-plus'></i> Add Group</a></p>

  @foreach($detail->children as $child)
    <div class="col-sm-12">
      <table class="table">
        <tr>
          <td style="width: 60%"><h5>{{ $child->service_name }}</h5></td>
          <td align="right">
            <span class="text-right">
              <a href="{{ route('workflows.add', ['id_service' => $child->id_service]) }}" class="btn btn-md btn-success" title='Add workflow'><i class='fa fa-plus'></i> Add Workflow</a> &nbsp;
              <a href="{{ route('member.pricelist.index', ['id_service' => $child->id_service]) }}"" class="btn btn-md btn-success" title='Manage Pricelist'><i class='fa fa-money-bill-alt'></i> Pricelist </a> &nbsp;
              <a href="#" onClick="editService({{ $child->id_service }})" class="btn btn-md btn-warning"><i class='fa fa-edit'></i> Edit</a> &nbsp;
              <a href="{{ route('services_api.delete', [$child->id_service]) }}" class="btn btn-md btn-danger"><i class='fa fa-trash'></i> Delete</a> 
            </span>
          </td>
        </tr>
      </table>
      <div class="col-sm-12" id="wokrflow-{{ $child->id }}">
        <table class="table table-striped datatable-simple">
          <thead>
            <tr>
              <th style="width: 5%">#</th>
              <th style="width: 4%">Seq</th>
              <th style="width: 25%">Activity</th>
              <th>WorkDay(s)</th>
              <th>Unit In Charge</th>
              <th>Req Info</th>
              <th>Req Doc</th>
              <th>Action</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
          <?php 
            $children = $child->workflows()->orderBy('sequence')->get();
            $total = count($children);
            $no = 1;
          ?>
          @foreach($children as $flow)
            <?php 
              $action = "";
              if ($total > 1){
                if ($no == $total ) {
                  $action .= "<a href='".route('workflows.sequence.update', ['id_service' => \Request()->id_service, 'id' => $flow->id_service_workflow, 'arrow' => 'up'])."'><i class='fa fa-arrow-up'></i></a> ";
                }elseif ($no == 1 ) {
                  $action .= "<a href='".route('workflows.sequence.update', ['id_service' => \Request()->id_service, 'id' => $flow->id_service_workflow, 'arrow' => 'down'])."'><i class='fa fa-arrow-down'></i></a> ";
                }else{
                  $action .= "<a href='".route('workflows.sequence.update', ['id_service' => \Request()->id_service, 'id' => $flow->id_service_workflow, 'arrow' => 'up'])."'><i class='fa fa-arrow-up'></i></a> ";
                  $action .= "<a href='".route('workflows.sequence.update', ['id_service' => \Request()->id_service, 'id' => $flow->id_service_workflow, 'arrow' => 'down'])."'><i class='fa fa-arrow-down'></i></a> ";
                }
                $no++;
              }
            ?>
            <tr>
              <td><?=$action;?></td>      
              <td>{{ $flow->sequence }}</td>
              <td>{{ $flow->workflow_name }}</td>
              <td>{{ $flow->workflow_day }}</td>
              <td>{{ (($flow->agency) ? $flow->agency->agency_unit_name : '' )}}</td>
              <td>{{ $flow->is_required_info == 1 ? 'Yes' : 'No' }}</td>
              <td>{{ $flow->is_required_doc == 1 ? 'Yes' : 'No' }}</td>
              <td>
                <a href="{{ route('workflows.edit', [$flow->id_service_workflow]) }}" class="btn btn-sm btn-warning"><i class='fa fa-edit'></i></a> &nbsp;
                <a href="{{ route('workflows.delete_child', [$flow->id_service_workflow]) }}" class="btn btn-sm btn-danger"><i class='fa fa-trash'></i></a> &nbsp;
              </td>
              <td>
                <a href="{{ route('workflow.docs', ['id_service_workflow' => $flow->id_service_workflow]) }}" class="btn btn-sm btn-success" title="Add Workflow doc"><i class='fa fa-file'></i></a> &nbsp;
                <a href="{{ route('workflow.infos', ['id_service_workflow' => $flow->id_service_workflow]) }}" class="btn btn-sm btn-success" title="Add workflow info"><i class='fa fa-info'></i></a> &nbsp;
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
        <br>
      </div>
    </div>
  @endforeach

<!-- Modal -->
<div id="myModalService" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Service Group</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <form id="frmService">
          <div class="form-group">
            <label></label>
            <input type="hidden" name="id_service" id="id_service" class="form-control">
            <input type="hidden" name="id_service_parent" id="id_service_parent" value="{{ $detail->id_service}}" class="form-control">
            <input type="text" name="service_name" id="service_name" class="form-control">
          </div>
          <div class="form-group">
            <label></label>
            <button type="button" class="btn btn-primary" id="btnSaveService">Save</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

</div>

<script type="text/javascript">
  function editService(idService){
    $.ajax({
      url: "{{ URL::to('api/services/') }}" + '/' + idService,
      dataType: 'json',
      success: function(data) {
        $("#service_name").val('');
        $("#id_service").val('');
        $("#id_service").val(data.data.id_service);
        $("#service_name").val(data.data.service_name);
        $("#myModalService").modal('show');
      },
      error: function(){
        alert('Error');
      }
    })
  }

  $(function(){
    $(".datatable-simple").DataTable({
      "bFilter": false,
      "lengthChange": false,
      "order": [[1, 'asc']]
    });

    $("#btnAddService").click(function(){
      $("#myModalService").modal('show');
    });

    $("#btnSaveService").click(function(){
      var idService = $("#id_service").val();
      var idServiceParent = $("#id_service_parent").val();
      var url = "{{ URL::to('/') }}" + '/api/services/' + idServiceParent + '/add';
      if (idService > 0) {
        url = "{{ URL::to('/') }}" + '/api/services/' + idServiceParent + '/update';
      }
      console.log(url);

      $.ajax({
        url: url,
        dataType: 'json',
        type: 'POST',
        data: {
          id_service: idService,
          id_service_parent: idServiceParent,
          "_sess": <?=\Auth::user()->id_user;?>,
          service_name: $("#service_name").val(),
          "_token": "{{ csrf_token() }}",
        },
        success: function(data) {
          $("#service_name").val('');
          $("#id_service").val('');
          $("#myModalService").modal('hide');
          // location.reload(true);
        },
        error: function(){
          alert('Error');
        }
      });
    });
  })
</script>

@endsection