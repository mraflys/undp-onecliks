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
      $url = route('coas.update', [$detail->id_master_coa]);
      $method = 'PUT';
      $opu = $detail->opu;
      $fund = $detail->fund;
      $imp_agent = $detail->imp_agent;
      $donor = $detail->donor;
      $pcbu = $detail->pcbu;
      $project = $detail->project;
      $activities = $detail->activities;
    }else{
      $url = route('coas.store');
      $method = 'POST';
      $opu = "";
      $fund = "";
      $imp_agent = "";
      $donor = "";
      $pcbu = "";
      $project = "";
      $activities = "";
    }
  ?>
  <form class="kt-form" action=" {{ $url }}" method="POST">
    @method($method)
    {{ csrf_field() }}

    <div class="kt-portlet__body">
      <div class="form-group form-group-last">
        @include('admin.messages')
      </div>
      <?php $fields = ['opu', 'fund', 'imp_agent', 'donor', 'pcbu', 'project', 'activities'];?>
      @foreach($fields as $field)
        <div class="form-group">
          <label>{{ strtoupper($field) }}</label>
          <input type="text" name="{{ $field }}" class="form-control" value="<?=(!empty(old($field))) ? old($field) : $$field ;?>" placeholder="Enter {{ $field }}">
        </div>
      @endforeach
    <div class="kt-portlet__foot">
      <div class="kt-form__actions">
        <button type="submit" class="btn btn-primary" name="submit">Save</button>
        <a href="{{ route('coas.index') }}" class="btn btn-secondary">Cancel</a>
      </div>
    </div>
  </form>
  <!--end::Form-->
  </div>

</div>
@endsection