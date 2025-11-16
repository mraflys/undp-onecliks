@extends('admin.index')
@section('content')
<div class="kt-portlet">
  <div class="kt-portlet__head">
    <div class="kt-portlet__head-label">
      <h3 class="kt-portlet__head-title text-primary">
        {{ strtoupper($title) }} - <?=(isset($detail)) ? 'EDIT' : 'NEW';?>
      </h3>
    </div>
  </div>
  <div class="col-md-9 col-xs-12">
  <!--begin::Form-->
  <?php
    if (isset($detail) && !is_null($detail)) {
      $default_sequence = \App\WorkFlowInfo::get_latest_seq($detail->id_service_workflow); 
      $url = route('workflow_infos.update', [$detail->id_service_workflow_info]);
      $method = 'PUT';
      $info_title = $detail->info_title;
      $id_service_workflow = $detail->id_service_workflow;
      $description = $detail->description;
      $sequence = $detail->sequence > 0 ? $detail->sequence : $default_sequence;
      $is_mandatory = $detail->is_mandatory;
    }else{
      $default_sequence = \App\WorkFlowInfo::get_latest_seq(\Request()->id_service_workflow); 
      $url = route('workflow_infos.store');
      $method = 'POST';
      $info_title = '';
      $id_service_workflow = \Request()->id_service_workflow;
      $description = '';
      $is_mandatory = 1;
      $sequence = $default_sequence;
    }
  ?>
  <form class="kt-form" action=" {{ $url }}" method="POST" enctype="multipart/form-data">
    @method($method)
    {{ csrf_field() }}
    <input type="hidden" name="id_service_workflow" value="{{ $id_service_workflow }}">

    <div class="kt-portlet__body">
      <div class="form-group form-group-last">
        @include('admin.messages')
      </div>
      <div class="form-group">
        <label>Caption</label>
        <input type="text" name="info_title" class="form-control" value="<?=(!empty(old('info_title'))) ? old('info_title') : $info_title ;?>" placeholder="Enter Caption">
      </div>
      <div class="form-group">
        <label for="exampleTextarea">Description</label>
        <textarea class="form-control" id="exampleTextarea" rows="3" name="description">{{ (old('description')) ? old('description') : $description }}</textarea>
      </div>
      <div class="form-group">
        <label>Sequence</label>
        <input type="text"  class="form-control" value="<?=(!empty(old('sequence'))) ? old('sequence') : $sequence ;?>" disabled>
         <input type="hidden" name="sequence" class="form-control" value="<?=(!empty(old('sequence'))) ? old('sequence') : $sequence ;?>" placeholder="Enter Sequence">
      </div>
      <div class="form-group">
        <label>Is Mandatory ?</label>
        <br>
        <input type="radio" name="is_mandatory" value="1" <?=($is_mandatory == 1) ? 'checked' : '';?>> Yes &nbsp;
        <input type="radio" name="is_mandatory" value="0" <?=($is_mandatory == 0) ? 'checked' : '';?>> No &nbsp;
      </div>
    </div>
    <div class="kt-portlet__foot">
      <div class="kt-form__actions">
        <button type="submit" class="btn btn-primary" name="submit">Save</button>
        <a href="{{ route('workflow.infos', ['id_service_workflow' => $id_service_workflow]) }}" class="btn btn-secondary">Cancel</a>
      </div>
    </div>
  </form>
  <!--end::Form-->
  </div>

</div>
@endsection