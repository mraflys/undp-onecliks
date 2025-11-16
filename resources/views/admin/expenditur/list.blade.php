@extends('admin.index')
@section('content')
  @if(\Auth::user()->id_role == 3)
  <div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="title" id="importModalLabel">Insert Expenditure Type</h5>
              </div>
              <div class="modal-body">
                  <form id="import" action="{{ route('expenditur.store') }}" method="post" enctype="multipart/form-data">
                      @csrf
                      <div class="form-group">
                          <label class="control-label">Expenditure Code</label>
                          <div class="">
                            <input type="text" id="code" class="form-control" autocomplete="off" name="code" required>
                          </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label">Expenditure Name</label>
                        <div class="">
                          <input type="text" id="name" class="form-control" autocomplete="off" name="name" required>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label">Description</label>
                        <div class="">
                          <textarea name="description" id="description" class="form-control" required></textarea>
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-link btn-cancle-input" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-input" onclick="loading()"><i class="feather icon-upload-cloud"></i>
                            Insert</button>
                        <button class="btn btn-primary btn-loading" style="display: none" type="button" disabled>
                          <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                          Loading...
                        </button>
                      </div>
                  </form>
              </div>
              
          </div>
      </div>
  </div>
  <p>
    <a href="javascript:void(0);" id="add_expenditure" class="btn btn-success" data-toggle="modal"
    data-target="#importModal" data-backdrop="static" data-keyboard="false"><i class='fa fa-plus'></i> Add Expenditure Type</a>
  </p>

  <hr>
  @endif
  @include('admin.messages')
  <hr>
  <div class="modal fade" id="listModal" tabindex="-1" role="dialog" aria-labelledby="listModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="title" id="listModalLabel">Edit Expenditure Type</h5>
            </div>
            <div class="modal-body">
                <form id="import" action="{{ route('expenditur.update_new') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="text" hidden id="id_exptype" name="id_exptype">
                    <div class="form-group">
                        <label class="control-label">Expenditure Code</label>
                        <div class="">
                          <input type="text" id="code_edit" class="form-control" autocomplete="off" name="code_edit" required>
                        </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label">Expenditure Name</label>
                      <div class="">
                        <input type="text" id="name_edit" class="form-control" autocomplete="off" name="name_edit" required>
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label">Description</label>
                      <div class="">
                        <textarea name="description_edit" id="description_edit" class="form-control" required></textarea>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-link btn-cancle-input" data-dismiss="modal">Cancel</button>
                      <button type="submit" class="btn btn-primary btn-input" onclick="loading()"><i class="feather icon-upload-cloud"></i>
                          Insert</button>
                      <button class="btn btn-primary btn-loading" style="display: none" type="button" disabled>
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        Loading...
                      </button>
                    </div>
                </form>
            </div>
            
        </div>
    </div>
  </div>
  <div class="pb-3" style="overflow-x : scroll;">
    <input type="text" hidden id="roleUser" value="{{\Auth::user()->id_role}}">
    <table class="table table-striped" style="width:100%" id="mytable">
      <thead>
        <tr>
          <th>Exp Type Code</th>
          <th>Exp Type Name</th>
          <th>Description</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        
      </tbody>
    </table>
  </div>

  <script type="text/javascript"> 
    const selectedUserPositionVal = [];
    var generalNPComponent,tbl,optionGeneral;
    function loading(){
      $('.btn-input-setting-form').css('display','none');
      $('.btn-loading').css('display','block');
      $('.btn-cancle-input').attr('disabled','disabled');
    }
    function expenditur_detail(id){
      $("#loadingProject").html("<?=\App\GeneralHelper::dt_loading_component();?>")
      $.ajax({
        type: 'POST',
        url: '{!! route('expenditur.expenditur-detail') !!}',
        data: {
            id: id
        },
        success: function(data) {
          console.log(data)
          $("#id_exptype").val(data.id_exptype);
          $("#code_edit").val(data.exp_type_code);
          $("#name_edit").val(data.exp_type_name);
          $("#description_edit").val(data.description);

        },
        error: function(data) {

        }
      });
      
    }
    function deleteRow(id){
      swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes'
      }).then(function(result) {
        if (result.value) { 
          $.ajax({
            url: "<?=URL::to('admin/expenditur');?>" + '/' + id,
            type: 'POST',
            data: {
              "_token": "{{ csrf_token() }}",
              "_method" : 'DELETE',
            },
            success: function(result) {
              swal.fire('Deleted!','Your file has been deleted.','success'); 
            },error: function(){
              swal.fire('ERROR!','DATA can not be removed.','error'); 
            }
          });
        }
      });
    }
    function showTable(){
      $("#mytable").DataTable().destroy();
      var viewBy = $("#exampleSelect1").val();
      var oTable = $('#mytable').DataTable({
        dom: 'lBfrtip',
        buttons: [
            { extend: 'excel', text: "<i class='fa fa-download'> Excel</i>" }
        ],
        lengthMenu: [[ 10, 25, 50, -1 ],['10', '25', '50', 'All']],
        processing: true,
        serverSide: true,
        ajax: {
          url: "{!! route("expenditur.ajax-list") !!}",
          data: function(d){
            d.roleUser =$('#roleUser').val();
            d.viewBy = viewBy;
          },
        },
        "columns": [
          { data: 'exp_type_code', name: 'exp_type_code' },
          { data: 'exp_type_name', name: 'exp_type_name' },
          { data: 'description', name: 'description' },
          { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
      });
    }



    $(function(){
      $(".select2").select2();
      // doLoadNPComponent();
      $('#date1').datepicker({
        format: 'yyyy-mm-dd',
      });
      $('#date2').datepicker({
        format: 'yyyy-mm-dd',
      });
      showTable();
      $("#btnSearch").click(function() {
        showTable();
      })
    });
  </script>
@endsection