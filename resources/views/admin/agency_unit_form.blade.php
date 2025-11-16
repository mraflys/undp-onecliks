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
      $url = route('agency_units.update', [$detail->id_agency_unit]);
      $method = 'PUT';
      $name = $detail->agency_unit_name;
      $agency_unit_code = $detail->agency_unit_code;
      $country = $detail->id_country;
      $finance_email = $detail->finance_email;
      $description = $detail->description;
      $is_service_agency = $detail->is_service_unit;
      $is_active = $detail->is_active;
    }else{
      $url = route('agency_units.store');
      $method = 'POST';
      $name = "";$agency_unit_code = "";$description = "";$finance_email = "";
      $is_service_agency = 1;$is_active = 1;$country = 0;
    }
  ?>
  <form class="kt-form" action=" {{ $url }}" method="POST">
    @method($method)
    {{ csrf_field() }}

    <div class="kt-portlet__body">
      <div class="form-group form-group-last">
        <!-- <div class="alert alert-danger" role="alert">
          <div class="alert-text">
            The example form below demonstrates common HTML form elements that receive updated styles from Bootstrap with additional classes.
          </div>
        </div> -->
        @include('admin.messages')
      </div>
      <div class="form-group">
        <label>Code</label>
        <input type="text" name="agency_unit_code" class="form-control" value="<?=(!empty(old('agency_unit_code'))) ? old('agency_unit_code') : $agency_unit_code ;?>" placeholder="Enter Agency Unit Code">
        <span class="form-text text-muted">Agency Unit Code is unique value.</span>
      </div>
      <div class="form-group">
        <label>Name</label>
        <input type="text" name="name" class="form-control" value="<?=(!empty(old('name'))) ? old('name') : $name ;?>" placeholder="Enter Agency Unit Name">
        <!-- <span class="form-text text-muted">We'll never share your email with anyone else.</span> -->
      </div>
      <div class="form-group">
        <label for="exampleSelect1">Country</label>
        <select class="form-control" id="exampleSelect1" name="country">
          @foreach($countries as $ct)
            <option value="{{ $ct->id_country }}" {{ ($ct->id_country == $country) ? 'selected': '' }}>{{ strtoupper($ct->country_name) }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group">
        <label for="exampleTextarea">Description</label>
        <textarea class="form-control" id="exampleTextarea" rows="3" name="description">{{ (old('description')) ? old('description') : $description }}</textarea>
      </div>
      <div class="form-group">
        <label>Finance Email</label>
        <input type="email" name="finance_email" class="form-control" value="<?=(!empty(old('finance_email'))) ? old('finance_email') : $finance_email ;?>" placeholder="Enter Finance Email">
      </div>
      <div class="form-group">
        <label>Is A Service Agency ?</label>
        <br>
        <input type="radio" name="is_service_agency" value="1" <?=($is_service_agency == 1) ? 'checked' : '';?>> Yes &nbsp;
        <input type="radio" name="is_service_agency" value="0" <?=($is_service_agency == 0) ? 'checked' : '';?>> No &nbsp;
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
        <a href="{{ route('agency_units.index') }}" class="btn btn-secondary">Cancel</a>
      </div>
    </div>
  </form>
  <!--end::Form-->
  </div>

</div>
@endsection