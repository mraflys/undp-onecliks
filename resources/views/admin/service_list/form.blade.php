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
      $url = route('service_list.update', [$detail->id_service]);
      $method = 'PUT';
      $name = $detail->service_name;
      $service_code = $detail->service_code;
      $description = $detail->description;
      $is_required_contract = $detail->is_required_contract;
      $is_active = $detail->is_active;
      $id_agency_unit = $detail->id_agency_unit;
    }else{
      $url = route('service_list.store');
      $method = 'POST';
      $name = "";$service_code = "";$description = "";
      $id_service_parent = "";
      $is_required_contract = 1;$is_active = 1;$id_agency_unit = 0;
    }
  ?>
  <form class="kt-form" action=" {{ $url }}" method="POST">
    @method($method)
    {{ csrf_field() }}

    <div class="kt-portlet__body">
      <div class="form-group form-group-last">
        @include('admin.messages')
      </div>
      <!-- <div class="form-group">
        <label>Code</label>
        <input type="text" name="service_code" class="form-control" value="<?=(!empty(old('service_code'))) ? old('service_code') : $service_code ;?>" placeholder="Enter Service List Code">
        <span class="form-text text-muted">Service List Code.</span>
      </div> -->
      <div class="form-group">
        <label>Name</label>
        <input type="text" name="name" class="form-control" value="<?=(!empty(old('name'))) ? old('name') : $name ;?>" placeholder="Enter Service List Name">
        <!-- <span class="form-text text-muted">We'll never share your email with anyone else.</span> -->
      </div>
      <div class="form-group">
        <label for="exampleSelect1">Agency</label>
        <select class="form-control select2" id="agency_unit" name="id_agency_unit"></select>
      </div>
      <div class="form-group">
        <label for="exampleTextarea">Description</label>
        <textarea class="form-control" id="exampleTextarea" rows="3" name="description">{{ (old('description')) ? old('description') : $description }}</textarea>
      </div>
      <!-- <div class="form-group">
        <label>Is Required Contract ?</label>
        <br>
        <input type="radio" name="is_required_contract" value="1" <?=($is_required_contract == 1) ? 'checked' : '';?>> Yes &nbsp;
        <input type="radio" name="is_required_contract" value="0" <?=($is_required_contract == 0) ? 'checked' : '';?>> No &nbsp;
      </div> -->
      <div class="form-group">
        <label>Is Active ?</label>
        <br>
        <input type="radio" name="is_active" value="1" <?=($is_active == 1) ? 'checked' : '';?>> Yes &nbsp;
        <input type="radio" name="is_active" value="0" <?=($is_active == 0) ? 'checked' : '';?>> No &nbsp;
      </div>
    </div>
    <div class="kt-portlet__foot">
      <div class="kt-form__actions">
        <button type="submit" class="btn btn-primary" name="submit">Save</button>
        <a href="{{ route('service_list.index') }}" class="btn btn-secondary">Cancel</a>
      </div>
    </div>
  </form>
  <!--end::Form-->
  </div>

</div>
<script type="text/javascript">
  function agencyList(idCountry) {
    $.ajax({
      url: "<?=route('api-list-agency-units-search-by');?>" + "?all=1",
      dataType: 'json',
      success: function(data){
        $("#agency_unit").html("");
        $.each(data.data, function(k, value){
          var selected = value.id_agency_unit == <?=$id_agency_unit;?> ? 'selected' : '';
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