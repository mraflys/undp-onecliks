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
      $url = route('holidays.update', [$detail->id_calendar_holiday]);
      $method = 'PUT';
      $name = $detail->holiday_name;
      $country = $detail->id_country;
      $finance_email = $detail->finance_email;
      $description = $detail->description;
      // $is_active = $detail->is_active;
      $date_holiday_start = $detail->date_holiday_start;
      $date_holiday_end = $detail->date_holiday_end;
    }else{
      $url = route('holidays.store');
      $method = 'POST';
      $name = "";$holiday_code = "";$description = "";$date_holiday_start = "";$date_holiday_end = "";
      $is_service_agency = 1;$is_active = 1;$country = 0;
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
        <label for="exampleSelect1">Country</label>
        <select class="form-control" id="exampleSelect1" name="country">
          @foreach($countries as $ct)
            <option value="{{ $ct->id_country }}" {{ ($ct->id_country == $country) ? 'selected': '' }}>{{ strtoupper($ct->country_name) }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group">
        <label>Start Date</label>
        <input type="date" name="date_holiday_start" class="form-control" value="<?=(!empty(old('date_holiday_start'))) ? old('date_holiday_start') : $date_holiday_start ;?>">
      </div>
      <div class="form-group">
        <label>End Date</label>
        <input type="date" name="date_holiday_end" class="form-control" value="<?=(!empty(old('date_holiday_end'))) ? old('date_holiday_end') : $date_holiday_end ;?>">
      </div>
      <div class="form-group">
        <label for="exampleTextarea">Description</label>
        <textarea class="form-control" id="exampleTextarea" rows="3" name="description">{{ (old('description')) ? old('description') : $description }}</textarea>
      </div>
    </div>
    <div class="kt-portlet__foot">
      <div class="kt-form__actions">
        <button type="submit" class="btn btn-primary" name="submit">Save</button>
        <a href="{{ route('holidays.index') }}" class="btn btn-secondary">Cancel</a>
      </div>
    </div>
  </form>
  <!--end::Form-->
  </div>

</div>
@endsection