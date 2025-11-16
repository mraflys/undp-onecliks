<div class="kt-grid__item kt-grid__item--fluid  kt-grid__item--order-tablet-and-mobile-1  kt-login__wrapper">

  <!--begin::Head-->
  <div class="kt-login__head">
    <span class="kt-login__signup-label">Already have an account yet?</span>&nbsp;&nbsp;
    <a href="{{ route('login') }}" class="kt-link kt-login__signup-link">Sign in!</a>
  </div>
  <!--end::Head-->
  <!--begin::Body-->
  <div class="kt-login__body">

    <!--begin::Signin-->
    <div class="kt-login__form">

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
      $id_agency_unit_parent = $detail->agency->id_agency_unit_parent;
      $is_using_LDAP = $detail->is_using_LDAP;
      $is_internal_user = $detail->is_internal_user;
      $is_active = $detail->is_active;
      $default_agency = \App\AgencyUnit::find($id_agency_unit);
    }else{
      $url = route('auth.register');
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
        <label>Email</label>
        <input type="text" name="user_name" class="form-control" value="<?=(!empty(old('user_name'))) ? old('user_name') : $user_name ;?>" placeholder="Enter User Name">
        <span class="form-text text-muted" style="margin-bottom: 5px">Email will be used as Username and it is unique value.</span>
      </div>
      <div class="form-group">
        <label>First Name</label>
        <input type="text" name="first_name" class="form-control" value="<?=(!empty(old('first_name'))) ? old('first_name') : $first_name ;?>" placeholder="Enter first_name">
        <span class="form-text text-muted">&nbsp;</span>
      </div>
      <div class="form-group">
        <label>Last Name</label>
        <input type="text" name="last_name" class="form-control" value="<?=(!empty(old('last_name'))) ? old('last_name') : $last_name ;?>" placeholder="Enter last_name">
        <span class="form-text text-muted">&nbsp;</span>
      </div>
      <div class="form-group">
        <label>Phone Number</label>
        <input type="text" name="phone" class="form-control" value="<?=(!empty(old('phone'))) ? old('phone') : "" ;?>" placeholder="Enter phone number">
        <span class="form-text text-muted">&nbsp;</span>
      </div>
      <div class="form-group">
        <label for="exampleSelect1">Country</label>
        <select class="form-control select2" id="country" name="id_country">
          @foreach($countries as $ct)
            <option value="{{ $ct->id_country }}" {{ ($ct->id_country == $id_country) ? 'selected': '' }}>{{ strtoupper($ct->country_name) }}</option>
          @endforeach
        </select>
        <span class="form-text text-muted">&nbsp;</span>
      </div>
      <div class="form-group">
        <label for="exampleSelect1">Agency</label>
        <select class="form-control select2" id="agency_unit"></select>
        <span class="form-text text-muted">&nbsp;</span>
      </div>
      <div class="form-group">
        <label for="exampleSelect1">Service Unit</label>
        <select class="form-control select2" id="service_unit" name="id_agency_unit"></select>
        <span class="form-text text-muted">&nbsp;</span>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" class="form-control">
        <span class="form-text text-muted">&nbsp;</span>
      </div>
    </div>
    <div class="kt-portlet__foot">
      <div class="kt-form__actions">
        <button type="submit" class="btn btn-primary" name="submit">Sign Up Now</button>
        <a href="{{ route('login') }}" class="btn btn-secondary">Cancel</a>
      </div>
    </div>
  </form>
  <!--end::Form-->
  </div>
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