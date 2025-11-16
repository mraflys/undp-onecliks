@extends('admin.index')
@section('content')



<style type="text/css">
  #basicLoadingComponent {
    position:absolute;
    max-width: 100%;
    height: auto;
    top: 47%;
    left: 50%;
    /*border:2px solid;*/
    background: #fff;
    /*transform: translate(-50%, -50%);*/
  }
</style>

@if ($status != null)
	<div class="alert alert-success alert-dismissible">
  	<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
  	<h6>Transaction {{$tiker_tr}} has been {{$type}}</h6>
	</div>
@endif
@include('admin.messages')
<ul class="nav nav-tabs  nav-tabs-line" role="tablist">
  <li class="nav-item">
    <a class="nav-link active" data-toggle="tab" href="#new_list" role="tab">New Service </a>
  </li>
  <li class="nav-item">
    <a class="nav-link" data-toggle="tab" href="#ongoing_list" role="tab">Ongoing Service</a>
  </li>
  @if(session('user_role_id') != 3)
    <li class="nav-item">
      <a class="nav-link" data-toggle="tab" href="#history_list" role="tab">History</a>
    </li>
  @endif
  @if(session('user_role_id') == 3)
    <li class="nav-item">
      <a class="nav-link" data-toggle="tab" href="#restore_list" role="tab">Restore Service</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-toggle="tab" href="#document_list" role="tab">Document Service</a>
    </li>
  @endif
</ul>

<div class="tab-content">
  <?=\App\GeneralHelper::basic_loading_component();?>
  <div class="tab-pane active" id="new_list" role="tabpanel">
    @include("member.service.new_list")
  </div>
  <div class="tab-pane" id="ongoing_list" role="tabpanel">
    @include("member.service.ongoing_list")
  </div>
  @if(session('user_role_id') != 3)
    <div class="tab-pane" id="history_list" role="tabpanel">
      @include("member.service.tracking_list")
    </div>
  @endif
  @if(session('user_role_id') == 3)
    <div class="tab-pane" id="restore_list" role="tabpanel">
      @include("member.service.restore_list")
    </div>
    <div class="tab-pane" id="document_list" role="tabpanel">
      @include("member.service.document_list")
    </div>
  @endif
</div>

<script type="text/javascript">
  function showLoading(){
    $("#basicLoadingComponent").css('display', '');
  }
  $(function(){
    $("#basicLoadingComponent").css('display', 'none');
  })
</script>
@endsection