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
      $url = route('myprofile.update');
      $method = 'POST';
      $first_name = $detail->first_name;
      $last_name = $detail->last_name;
      $user_name = $detail->user_name;
      $person_name = $detail->person_name;
      $id_country = $detail->id_country;
      $id_role = $detail->id_role;
      $email = $detail->email;
      $id_agency_unit = $detail->id_agency_unit > 0 ? $detail->id_agency_unit : 0;
      $is_using_LDAP = $detail->is_using_LDAP;
      $is_active = $detail->is_active;
      $default_agency = \App\AgencyUnit::find($id_agency_unit);
      $parent = !is_null($default_agency->parent) ? $default_agency->parent : $default_agency;
      $services = \App\AgencyUnit::where('id_agency_unit_parent', $parent->id_agency_unit)->orderBy("agency_unit_name")->get();
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
        <label>Email / User Name</label>
        <input type="text" class="form-control" value="<?=(!empty(old('user_name'))) ? old('user_name') : $user_name ;?>" disabled>
      </div>
      <div class="form-group">
        <label>First Name</label>
        <input type="text" name="first_name" class="form-control" value="<?=(!empty(old('first_name'))) ? old('first_name') : $first_name ;?>" placeholder="Enter first_name">
      </div>
      <div class="form-group">
        <label>Last Name</label>
        <input type="text" name="last_name" class="form-control" value="<?=(!empty(old('last_name'))) ? old('last_name') : $last_name ;?>" placeholder="Enter last_name">
      </div>
      <div class="form-group">
        <label for="exampleSelect1">Role</label>
        <input type="text" value="{{ $detail->role->role_name }}" disabled class="form-control">
      </div>
      <div class="form-group">
        <label for="exampleSelect1">Country</label>
        <input type="text" value="{{ $detail->country->country_name }}" disabled class="form-control">
      </div>
      <div class="form-group">
        <label for="exampleSelect1">Agency</label>
        <input type="text" value="{{ $parent->agency_unit_name }}" disabled class="form-control">
      </div>
      <div class="form-group">
        <label for="exampleSelect1">Service Unit</label>
        <select class="form-control select2" id="service_unit" name="id_agency_unit">
          @foreach($services as $service)
            <option value="{{ $service->id_agency_unit}}" <?=(($service->id_agency_unit != $id_agency_unit) ? '' : 'selected');?>>{{ $service->agency_unit_name }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="text" name="password" class="form-control" placeholder="Ignore this if no changes" />
      </div>
      <div class="form-group">
        <label>Confirm Password</label>
        <input type="text" name="confirm_password" class="form-control" />
      </div>
    </div>
    <div class="kt-portlet__foot">
      <div class="kt-form__actions">
        <button type="submit" class="btn btn-primary" name="submit">Save</button>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
      </div>
    </div>
  </form>
  <!--end::Form-->
  </div>

</div>
<script type="text/javascript">
  function serviceList(idCountry, idParent) {
    $.ajax({
      url: "<?=route('api-list-agency-units-search-by');?>" + "?id_country=" + $("#country").val() + "&id_parent=" + idParent + '&service_only=1',
      dataType: 'json',
      success: function(data){
        $("#service_unit").html("");
        $.each(data.data, function(k, value){
          selected = value.id_agency_unit == <?=$id_agency_unit;?> ? 'selected' : '';
          $("#service_unit").append("<option value='"+value.id_agency_unit+"' "+selected+">"+value.agency_unit_name+"</option>");
        })
      }
    })
  }

  function agencyList(idCountry) {
    $.ajax({
      url: "<?=route('api-list-agency-units-search-by');?>" + "?id_country=" + $("#country").val(),
      dataType: 'json',
      success: function(data){
        $("#agency_unit").html("");
        $.each(data.data, function(k, value){
          selected = value.id_agency_unit == <?=$id_agency_unit;?> ? 'selected' : '';
          $("#agency_unit").append("<option value='"+value.id_agency_unit+"' "+selected+">"+value.agency_unit_name+"</option>");
        });
        serviceList(<?=$id_country;?>, $("#agency_unit").val());
      }
    })
  }

  $(function(){
    agencyList(<?=$id_country;?>);
    
    $('.select2').select2();
    $('#country').change(function(){
      agencyList($("#country").val())
    })
    $('#agency_unit').change(function(){
      serviceList($("#country").val(), $("#agency_unit").val());
    })
  })
</script>
@endsection