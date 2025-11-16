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
      $default_sequence = \App\WorkFlow::get_latest_seq($detail->id_service); 
      $url = route('workflows.update', [$detail->id_service_workflow]);
      $method = 'PUT';
      $name = $detail->workflow_name;
      $workflow_code = $detail->workflow_code;
      $workflow_day = $detail->workflow_day;
      $id_service = $detail->id_service;
      $is_required_info = $detail->is_required_info;
      $is_required_doc = $detail->is_required_doc;
      $is_start_billing = $detail->is_start_billing;
      $is_start_contract = $detail->is_start_contract;
      $is_active = $detail->is_active;
      $sequence = $detail->sequence > 0 ? $detail->sequence : $default_sequence;
      $id_agency_unit = $detail->id_agency_unit;
      $id_user_pic_primary = $detail->id_user_pic_primary;
      $id_user_pic_alternate = $detail->id_user_pic_alternate;
    }else{
      $default_sequence = \App\WorkFlow::get_latest_seq(\Request()->id_service); 
      $url = route('workflows.store');
      $method = 'POST';
      $name = "";$workflow_code = "";
      $workflow_day = 1;
      $id_workflow_parent = "";
      $is_required_info = 0;
      $is_required_doc = 0;
      $is_start_billing = 0;
      $is_start_contract = 0;
      $id_user_pic_primary = 0;
      $id_user_pic_alternate = 0;
      $id_agency_unit = 0;
      $sequence = $default_sequence;
      $id_service = $service->id_service;
    }
  ?>
  <form class="kt-form" action=" {{ $url }}" method="POST">
    @method($method)
    {{ csrf_field() }}

    <div class="kt-portlet__body">
      <div class="form-group form-group-last">
        @include('admin.messages')
      </div>
      <div class="form-group">
        <label>Activity</label>
        <input type="hidden" name="id_service" class="form-control" value="<?=(!empty(old('id_service'))) ? old('id_service') : $id_service ;?>">
        <input type="text" name="name" class="form-control" value="<?=(!empty(old('name'))) ? old('name') : $name ;?>" placeholder="Enter Workflow Name">
      </div>
     <!--  <div class="form-group">
        <label>Code</label>
        <input type="text" name="workflow_code" class="form-control" value="<?=(!empty(old('workflow_code'))) ? old('workflow_code') : $workflow_code ;?>" placeholder="Enter Workflow Code">
        <span class="form-text text-muted">Workflow Code.</span>
      </div> -->
       <div class="form-group">
        <label>Workday(s)</label>
        <input type="number" name="workflow_day" class="form-control" value="<?=(!empty(old('workflow_day'))) ? old('workflow_day') : $workflow_day ;?>" placeholder="Enter Workflow Day">
      </div>
      <div class="form-group">
        <label>Start Billing ?</label>
        <br>
        <input type="radio" name="is_start_billing" value="1" <?=($is_start_billing == 1) ? 'checked' : '';?>> Yes &nbsp;
        <input type="radio" name="is_start_billing" value="0" <?=($is_start_billing == 0) ? 'checked' : '';?>> No &nbsp;
      </div>
      <div class="form-group">
        <label>Start Contract ?</label>
        <br>
        <input type="radio" name="is_start_contract" value="1" <?=($is_start_contract == 1) ? 'checked' : '';?>> Yes &nbsp;
        <input type="radio" name="is_start_contract" value="0" <?=($is_start_contract == 0) ? 'checked' : '';?>> No &nbsp;
      </div>
      <div class="form-group">
        <label for="exampleSelect1">Unit In Charge</label>
        <select class="form-control select2" id="agency_unit" name="id_agency_unit">
          
        </select>
      </div>
      <div class="form-group">
        <label>Is Required Info ?</label>
        <br>
        <input type="radio" name="is_required_info" value="1" <?=($is_required_info == 1) ? 'checked' : '';?>> Yes &nbsp;
        <input type="radio" name="is_required_info" value="0" <?=($is_required_info == 0) ? 'checked' : '';?>> No &nbsp;
      </div>
      <div class="form-group">
        <label>Is Required Doc ?</label>
        <br>
        <input type="radio" name="is_required_doc" value="1" <?=($is_required_doc == 1) ? 'checked' : '';?>> Yes &nbsp;
        <input type="radio" name="is_required_doc" value="0" <?=($is_required_doc == 0) ? 'checked' : '';?>> No &nbsp;
      </div>
      <div class="form-group">
        <label>Primary Person in Charge</label>
        <select name="id_user_pic_primary" class='form-control select2'>
          @foreach($users as $user)
            <option value="{{ $user->id_user }}" <?=($user->id_user == $id_user_pic_primary) ? 'selected' : '';?>>{{ $user->first_name.' '.$user->last_name.' ('.$user->user_name.') ' }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group">
        <label>Alternative Person in Charge</label>
        <select name="id_user_pic_alternate" class='form-control select2'>
          @foreach($users as $user)
            <option value="{{ $user->id_user }}" <?=($user->id_user == $id_user_pic_alternate) ? 'selected' : '';?>>{{ $user->first_name.' '.$user->last_name.' ('.$user->user_name.') ' }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group">
        <label>Sequence</label>
        <input type="text" name="sequence" class="form-control" value="<?=(!empty(old('sequence'))) ? old('sequence') : $sequence ;?>" placeholder="Sequence">
      </div>
    </div>
    <div class="kt-portlet__foot">
      <div class="kt-form__actions">
        <button type="submit" class="btn btn-primary" name="submit">Save</button>
        <a href="{{ route('service_list.workflow', [$service->parent->id_service]) }}" class="btn btn-secondary">Cancel</a>
      </div>
    </div>
  </form>
  <!--end::Form-->
  </div>

</div>
<script type="text/javascript">
  function agencyList(idCountry) {
    $.ajax({
      url: "<?=route('api-list-agency-units-search-by');?>" + '?all=1',
      dataType: 'json',
      success: function(data){
        $("#agency_unit").html("");
        $("#agency_unit").append("<option value=''>---Select Unit---</option>");
        $.each(data.data, function(k, value){
          var selected = (value.id_agency_unit == <?=($id_agency_unit) ? $id_agency_unit : 0;?>) ? 'selected' : '';
          $("#agency_unit").append("<option value='"+value.id_agency_unit+"' "+selected+">"+value.agency_unit_name+"</option>");
        })
      }
    })
  }

  agencyList();
  $(function(){
    $('.select2').select2();
    $('#country').change(function(){
      agencyList($("#country").val())
    })
  })
</script>
@endsection