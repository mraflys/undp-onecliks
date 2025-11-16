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
      $url = route('projects.update', [$detail->id_project]);
      $method = 'PUT';
      $name = $detail->project_name;
      $id_agency_unit = $detail->id_agency_unit;
      $id_agency_unit_parent = $detail->id_agency_unit_parent;
    }else{
      $url = route('projects.store');
      $method = 'POST';
      $name = "";
      $id_agency_unit = 0;
      $id_agency_unit_parent = 0;
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
        <label>Name</label>
        <input type="text" name="name" class="form-control" value="<?=(!empty(old('name'))) ? old('name') : $name ;?>" placeholder="Enter Name">
      </div>
      <div class="form-group">
        <label>Agency Unit</label>
        <select name="id_agency_unit_parent" id="id_agency_unit_parent" class="form-control select2">
          <option>-- select agency --</option>
          @foreach($agencies as $agency)
            <option value="{{ $agency->id_agency_unit }}" <?=($agency->id_agency_unit == $id_agency_unit_parent) ? 'selected' : '';?>>{{ $agency->agency_unit_code.' - '.$agency->agency_unit_name }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group">
        <label>Unit</label>
        <select name="id_agency_unit" id="id_agency_unit" class="form-control select2"></select>
      </div>
    </div>
    <div class="kt-portlet__foot">
      <div class="kt-form__actions">
        <button type="submit" class="btn btn-primary" name="submit">Save</button>
        <a href="{{ route('projects.index') }}" class="btn btn-secondary">Cancel</a>
      </div>
    </div>
  </form>
  <!--end::Form-->
  </div>
</div>
<script type="text/javascript">
  function agencyList(idParent) {
    $.ajax({
      url: "<?=route('api-list-agency-units-search-by');?>" + "?all=1&id_parent=" + idParent,
      dataType: 'json',
      success: function(data){
        $("#id_agency_unit").html("");
        $.each(data.data, function(k, value){
          selected = value.id_agency_unit == <?=$id_agency_unit;?> ? 'selected' : '';
          $("#id_agency_unit").append("<option value='"+value.id_agency_unit+"' "+selected+">"+value.agency_unit_code + ' - '+ value.agency_unit_name+"</option>");
        });
      }
    })
  }

  $(function(){
    $(".select2").select2();
    agencyList(<?=$id_agency_unit_parent;?>)
    $("#id_agency_unit_parent").change(function(){
      agencyList($("#id_agency_unit_parent").val())
    })
  })
</script>
@endsection