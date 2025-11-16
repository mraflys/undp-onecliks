@extends('admin.index')
@section('content')
  <div class="col-sm-6">
    <h5 class="text-justify"><span class="text-primary">{{ $workflow->workflow_name }}</span></h5>
    <table class="table">
      <tr><th class="text-right" style="width: 20%">Code</th><td>{{ $workflow->workflow_code }}</td></tr>
      <tr><th class="text-right">Workday</th><td>{{ $workflow->workflow_day }}</td></tr>
      <tr><th class="text-right">Sequence</th><td>{{ $workflow->sequence }}</td></tr>
      <tr><th class="text-right">Start Billing</th><td>{{ $workflow->is_start_billing == 1 ? 'Yes' : 'No' }}</td></tr>
      <tr><th class="text-right">Start Contract</th><td>{{ $workflow->is_start_contract == 1 ? 'Yes' : 'No'}}</td></tr>
    </table> 
  </div>

  <p>
    <a href="{{ route('workflow_infos.add', ['id_service_workflow' => \Request()->id_service_workflow]) }}" class="btn btn-success"><i class='fa fa-plus'></i> Add Data</a>
    <a href="{{ route('service_list.workflow', [$workflow->service->parent->id_service]) }}" class="btn btn-default"><i class='fa fa-arrow-left'></i> Back </a>
  </p>
  <hr>
  <table class="table table-striped" id="mytable">
    <thead>
      <tr>
        <th style="width: 5%">#</th>
        <th style="width: 10px">Sequence</th>
        <th style="width: 35%">Caption</th>
        <th style="width: 10px">Is Mandatory</th>
        <th style="width: 25%">Description</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php 
        $total = count($list);
        $no = 1;
      ?>
      @foreach($list as $l)
        <?php 
          $action = "";
          if ($total > 1){
            if ($no == $total ) {
              $action .= "<a href='".route('workflow_infos.sequence.update', ['id_service_workflow' => \Request()->id_service_workflow, 'id' => $l->id_service_workflow_info, 'arrow' => 'up'])."'><i class='fa fa-arrow-up'></i></a> ";
            }elseif ($no == 1 ) {
              $action .= "<a href='".route('workflow_infos.sequence.update', ['id_service_workflow' => \Request()->id_service_workflow, 'id' => $l->id_service_workflow_info, 'arrow' => 'down'])."'><i class='fa fa-arrow-down'></i></a> ";
            }else{
              $action .= "<a href='".route('workflow_infos.sequence.update', ['id_service_workflow' => \Request()->id_service_workflow, 'id' => $l->id_service_workflow_info, 'arrow' => 'up'])."'><i class='fa fa-arrow-up'></i></a> ";
              $action .= "<a href='".route('workflow_infos.sequence.update', ['id_service_workflow' => \Request()->id_service_workflow, 'id' => $l->id_service_workflow_info, 'arrow' => 'down'])."'><i class='fa fa-arrow-down'></i></a> ";
            }
          }
          
          $no++;
        ?>
        <tr>
          <td><?=$action;?></td>          
          <td><?=$l->sequence;?></td>          
          <td>{{ $l->info_title }}</td>          
          <td>{{ $l->is_mandatory == 1 ? 'Yes' : 'No' }}</td>          
          <td>{{ $l->description }}</td>          
          <td>
            <a href="{{ route('workflow_infos.edit', [$l->id_service_workflow_info]) }}" class="btn btn-sm btn-warning"><i class='fa fa-edit'></i></a> &nbsp;
            <a href="#" class="btn btn-sm btn-danger" onclick="deleteRow({{ $l->id_service_workflow_info}})"><i class='fa fa-trash'></i></a> &nbsp;
          </td>          
        </tr>
      @endforeach
    </tbody>
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
            url: "<?=URL::to('admin/workflow_infos');?>" + '/' + id,
            type: 'POST',  // user.destroy
            data: {
              "_token": "{{ csrf_token() }}",
              "_method" : 'DELETE',
            },
            success: function(result) {
              swal.fire('Deleted!','Your file has been deleted.','success'); 
              location.reload();
            },error: function(){
              swal.fire('ERROR!','DATA can not be removed.','error'); 
            }
          });
        }
      });
    }
  </script>
@endsection
