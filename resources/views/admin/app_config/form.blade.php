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
      $url = route('app_configs.update', [$detail->id]);
      $method = 'PUT';
      $name = $detail->name;
      $short_description = $detail->short_description;
      $description = $detail->description;
      $logo = $detail->logo;
      $banner = $detail->banner;
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
        <label>App Name</label>
        <input type="text" name="name" class="form-control" value="<?=(!empty(old('name'))) ? old('name') : $name ;?>" placeholder="Enter Code">
      </div>
      <div class="form-group">
        <label>Short Description</label>
        <input type="text" name="short_description" class="form-control" value="<?=(!empty(old('short_description'))) ? old('short_description') : $short_description ;?>" placeholder="Enter Short Description">
      </div>
      <div class="form-group">
        <label for="exampleTextarea">Description</label>
        <textarea class="form-control" id="exampleTextarea" rows="3" name="description">{{ (old('description')) ? old('description') : $description }}</textarea>
      </div>
      <div class="form-group">
        <label>Logo</label>
        <input type="file" name="logo" class="form-control">
        @if (!empty($logo))
          <br>
          <img src="{{ asset($logo) }}" width="100px">
        @endif
      </div>
      <div class="form-group">
        <label>banner</label>
        <input type="file" name="banner" class="form-control">
        @if (!empty($banner))
          <br>
          <img src="{{ asset($banner) }}" width="100px">
        @endif
      </div>
    </div>
    <div class="kt-portlet__foot">
      <div class="kt-form__actions">
        <button type="submit" class="btn btn-primary" name="submit">Save</button>
        <a href="{{ route('app_configs.index') }}" class="btn btn-secondary">Cancel</a>
      </div>
    </div>
  </form>
  <!--end::Form-->
  </div>

</div>
@endsection