@extends('admin.index')
@section('content')
@include('admin.messages')
<hr>
<table class="table table-striped" id="mytableDraft">
  <thead>
    <tr>
      <th class="sort" field="service_name" title="Order by Service Name" target="_ongoing">Service Name</th>
      <th class="sort" field="service_unit_name" title="Agency" target="_ongoing">Service Unit</th>
      <th class="sort" field="agency_unit_name" title="Agency" target="_ongoing">Service Agency</th>  
      <th>Created At</th>    
      <th>Option</th>
    </tr>
  </thead>
  <tbody>
    @foreach($list as $l)
      <?php 
        $service_agency = !is_null($l->agency) ? $l->agency->agency_unit_name : null;
        $agency = !is_null($service_agency) && !is_null($l->agency->parent) ? $l->agency->parent->agency_unit_name : 'Requester'; 
      ?>
      <tr>
        <td>{{ $l->service_name }}</td>
        <td>{{ $agency }}</td>
        <td>{{ $service_agency != null ? $service_agency : 'Requester' }}</td>
        <td>{{ $l->date_created }}</td>
        <td>
          <a href="{{ route('myrequests.draft_edit', [$l->id_draft]) }}" class="btn" title="Edit"><i class="fa fa-edit"></i> </a>
          <a href="{{ route('myrequests.draft_delete', [$l->id_draft]) }}" class="btn" title="Delete"><i class="fa fa-trash"></i> </a>
        </td>
      </tr>
    @endforeach
  </tbody>
</table>

<script type="text/javascript">
  $(function(){
    $("#mytableDraft").DataTable({
      order: [3]
    });
  })
</script>
@endsection