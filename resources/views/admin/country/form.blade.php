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
      $url = route('countries.update', [$detail->id_country]);
      $method = 'PUT';
      $name = $detail->country_name;
      $country_code = $detail->country_code;
      $description = $detail->description;
      $file = $detail->country_image_path;
      $is_active = $detail->is_active;
    }else{
      $url = route('countries.store');
      $method = 'POST';
      $name = "";$country_code = "";$description = "";$file = "";
      $is_active = 1;
    }
  ?>
  <form class="kt-form" action=" {{ $url }}" method="POST" enctype="multipart/form-data">
    @method($method)
    {{ csrf_field() }}

    <div class="kt-portlet__body">
      <div class="form-group form-group-last">
        @include('admin.messages')
      </div>
      <div class="form-group">
        <label>Code</label>
        <input type="text" name="country_code" class="form-control" value="<?=(!empty(old('country_code'))) ? old('country_code') : $country_code ;?>" placeholder="Enter Code">
        <span class="form-text text-muted">Code is unique value.</span>
      </div>
      <div class="form-group">
        <label>Name</label>
        <input type="text" name="name" class="form-control" value="<?=(!empty(old('name'))) ? old('name') : $name ;?>" placeholder="Enter Name">
      </div>
      <div class="form-group">
        <label for="exampleTextarea">Description</label>
        <textarea class="form-control" id="exampleTextarea" rows="3" name="description">{{ (old('description')) ? old('description') : $description }}</textarea>
      </div>
      <div class="form-group">
        <label>Image</label>
        <input type="file" name="file" class="form-control">
        @if (!empty($file))
          <br>
          <img src="{{ asset($file) }}" width="100px">
        @endif
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
        <a href="{{ route('countries.index') }}" class="btn btn-secondary">Cancel</a>
      </div>
    </div>
  </form>
  <!--end::Form-->
  </div>

</div>
@endsection