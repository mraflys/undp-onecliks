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
      $url = route('users.update', [$detail->id_user]);
      $method = 'PUT';
      $first_name = $detail->first_name;
      $last_name = $detail->last_name;
      $user_name = $detail->user_name;
      $person_name = $detail->person_name;
      $id_country = $detail->id_country;
      $id_role = $detail->id_role;
      $email = $detail->email;
      $id_agency_unit = $detail->id_agency_unit > 0 ? $detail->id_agency_unit : 0;
      $id_agency_unit_parent = ($detail->agency) ? $detail->agency->id_agency_unit_parent : 0;
      $is_using_LDAP = $detail->is_using_LDAP;
      $is_internal_user = $detail->is_internal_user;
      $is_active = $detail->is_active;
      $default_agency = \App\AgencyUnit::find($id_agency_unit);
    }else{
      $url = route('users.store');
      $method = 'POST';
      $first_name = '';
      $last_name = '';
      $user_name = '';
      $person_name = '';
      $id_country = 1;
      $id_role = 1;
      $email = '@gmail';
      $id_agency_unit = 0;
      $id_agency_unit_parent = 0;
      $is_internal_user = 0;
      $is_using_LDAP = 0;
      $is_active = 1;
      $default_agency = \App\AgencyUnit::where('id_country', $id_country)->first();
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
        <input type="text" name="user_name" class="form-control" value="<?=(!empty(old('user_name'))) ? old('user_name') : $user_name ;?>" placeholder="Enter User Name">
        <span class="form-text text-muted">Username is unique value.</span>
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
        <select class="form-control select2" id="role" name="id_role">
          @foreach($roles as $ct)
            <option value="{{ $ct->id_role }}" {{ ($ct->id_role == $id_role) ? 'selected': '' }}>{{ strtoupper($ct->role_name) }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group">
        <label for="exampleSelect1">Country</label>
        <select class="form-control select2" id="country" name="id_country">
          @foreach($countries as $ct)
            <option value="{{ $ct->id_country }}" {{ ($ct->id_country == $id_country) ? 'selected': '' }}>{{ strtoupper($ct->country_name) }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group">
        <label for="exampleSelect1">Agency</label>
        <select class="form-control select2" id="agency_unit"></select>
      </div>
      <div class="form-group">
        <label for="exampleSelect1">Unit</label>
        <select class="form-control select2" id="service_unit" name="id_agency_unit"></select>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" class="form-control" placeholder="Ignore if no change">
      </div>
      <div class="form-group">
        <label>Is Using LDAP ?</label>
        <br>
        <input type="radio" name="is_using_LDAP" value="1" <?=($is_using_LDAP == 1) ? 'checked' : '';?>> Yes &nbsp;
        <input type="radio" name="is_using_LDAP" value="0" <?=($is_using_LDAP == 0) ? 'checked' : '';?>> No &nbsp;
      </div>
      <div class="form-group">
        <label>Is Internal User ?</label>
        <br>
        <input type="radio" name="is_internal_user" value="1" <?=($is_internal_user == 1) ? 'checked' : '';?>> Yes &nbsp;
        <input type="radio" name="is_internal_user" value="0" <?=($is_internal_user == 0) ? 'checked' : '';?>> No &nbsp;
      </div>
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
        $("#service_unit").html("-- Please Select Unit --");
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
        $("#agency_unit").html("<option>-- Please Select Agency --</option>");
        $.each(data.data, function(k, value){
          selected = value.id_agency_unit == <?=$id_agency_unit_parent;?> ? 'selected' : '';
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