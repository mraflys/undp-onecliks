@extends('admin.index')
@section('content')
  @if(\Auth::user()->id_role == 3)
  <div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="title" id="importModalLabel">Import Project</h5>
              </div>
              <div class="modal-body">
                  <form id="import" action="{{ route('projects.store') }}" method="post" enctype="multipart/form-data">
                      @csrf
                      <div class="form-group">
                          <label class="control-label">Authorized Date from</label>
                          <div class="">
                              <div class="input-group input-daterange">
                                  <input type="text" id="date1" class="form-control" autocomplete="off" name="date1" required>
                                  <div class="input-group-addon"> &nbsp;to </div>
                                  <input type="text" id="date2" class="form-control" autocomplete="off" name="date2" required>
                              </div>
                          </div>
                      </div>
                      <input type="file" required name="import"
                          accept=".xls,.xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel">
                          <br><br>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-link btn-cancle-input" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-input" onclick="loading()"><i class="feather icon-upload-cloud"></i>
                            Import</button>
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
  <div class="modal fade" id="formModal" tabindex="-1" role="dialog" aria-labelledby="formModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="title" id="formModalLabel">User Project Settings Form</h5>
              </div>
              <div class="modal-body">
                  <form id="settingForm" action="{{ route('project.setting-store-input') }}" method="post" enctype="multipart/form-data">
                      @csrf
                      <div class="form-group">
                        <label class="control-label">Input User</label>
                        <select id="status_input" class="form-control select2" name="id_user" style="width: 100%">
                          @foreach ($users as $user)
                            <option value="{{$user->id_user}}">{{$user->person_name}}</option>
                          @endforeach
                        </select>
                      </div>
                      <div class="form-group">
                          <label class="control-label">Input Project ID</label>
                          <div class="">
                              <div class="input-group">
                                  <input type="text" id="tidnoInput" class="form-control" autocomplete="off" name="checkbox_table" required>
                              </div>
                              <div id="tidnoInputHelp" class="form-text">Example : 00048762,00109159,00081148</div>

                          </div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-link btn-cancle-input-setting-form" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-input-setting-form" onclick="loading()"><i class="feather icon-upload-cloud"></i>
                            Submit</button>
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
  {{-- <div class="modal fade w-100" id="tableModal" tabindex="-1" role="dialog" aria-labelledby="tableModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="title" id="tableModalLabel">User Project Settings Table</h5>
              </div>
              <div class="modal-body">
                <form id="settingForm" action="{{ route('project.setting-store') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                      <label class="control-label">Input User</label>
                      <select id="status" class="form-control select2" name="id_user" style="width: 100%">
                        @foreach ($users as $user)
                          <option value="{{$user->id_user}}">{{$user->person_name}}</option>
                        @endforeach
                      </select>
                    </div>
                    <label class="control-label">Table Project</label>
                    <div style="overflow-x : scroll;">
                      <input type="text" hidden id="checkbox_table" name="checkbox_table">
                      <table class="table table-striped table-bordered nowrap display responsive w-100% p-2" 
                      id="tablenameproject">
                          <thead>
                              <tr>
                                  <th>Name</th>
                                  <th></th>
                                  <th>Project</th>
                                  <th>Position</th>
                                  <th>Calendar Group</th>
                                  <th>Oprating Unit</th>
                              </tr>
                          </thead>
                          <tbody id="inputnameproject" class="search-api">
                          </tbody> 
                      </table>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-link btn-cancle-input-setting-table" data-dismiss="modal">Cancel</button>
                      <button type="submit" class="btn btn-primary btn-input-setting-table" onclick="loading()"><i class="feather icon-upload-cloud"></i>
                          Submit</button>
                      <button class="btn btn-primary btn-loadin-setting-table" style="display: none" type="button" disabled>
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        Loading...
                      </button>
                    </div>
                </form>
              </div>
          </div>
      </div>
  </div> --}}
  <p>
    <a href="javascript:void(0);" id="add_project" class="btn btn-success" data-toggle="modal"
    data-target="#importModal" data-backdrop="static" data-keyboard="false"><i class='fa fa-plus'></i> Add Payroll Expenditure</a>
    {{-- <a href="javascript:void(0);" class="btn btn-primary add_settings_table" id="add_settings_table" data-toggle="modal"
    data-target="#tableModal" data-backdrop="static" data-keyboard="false" style="display: none"><i class='fa fa-table' aria-hidden="true"></i> user project settings table</a> --}}
    {{-- <button class="btn btn-primary btn-settings-table" type="button" disabled>
      <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
      user project settings table
    </button> --}}
    <a href="javascript:void(0);" class="btn btn-primary" id="add_settings_form" data-toggle="modal"
    data-target="#formModal" data-backdrop="static" data-keyboard="false"><i class='fa fa-list-alt' aria-hidden="true"></i> User Payroll Expenditure Settings Form</a>
  </p>

  <hr>
  @endif
  @include('admin.messages')
  <form class="form-horizontal m-b-10" role="form">
    <div class="form-group">
        <label class="col-sm-3 control-label" style="padding-top: 0px;">View Payroll Expenditure</label>
        <div class="form-group pt-3 px-3">
            <label for="exampleSelect1">view By Project ID</label><br>
            <select class="form-control w-25 select2" id="exampleSelect1" name="viewBy">
                <option value="">--Select Project ID--</option>
                @foreach($distinctTrPEX as $TrPEX)
                  <option value="{{$TrPEX->Project}}">{{$TrPEX->Project}}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-7">
            <div class="input-group input-daterange">
                &nbsp; <button type="button" class="btn btn-primary" id="btnSearch">Show Payroll Expenditure</button>
            </div>
        </div>
    </div>
  </form>
  <hr>
  <div class="modal fade" id="listModal" tabindex="-1" role="dialog"  aria-labelledby="listModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="title" id="listModalLabel">List Project Person</h5>
            </div>
            <div class="modal-body">
                <table class="table table-striped">
                  <thead>
                    <tr>
                      <th>Name</th>
                      <th>Position</th>
                    </tr>
                  </thead>
                  <tbody id="listPersonUl">
                  </tbody>
                </table>
                <div class="modal-footer">
                  <button type="button" class="btn btn-link btn-list-cancle" data-dismiss="modal">Cancel</button>
                </div>
            </div>
            
        </div>
    </div>
  </div>
  <div class="pb-3" style="overflow-x : scroll;">
    <input type="text" hidden id="roleUser" value="{{\Auth::user()->id_role}}">
    <table class="table table-striped" style="width:100%" id="mytable">
      <thead>
        <tr>
          <th>TIDNO</th>
          <th>Name</th>
          <th>Email</th>
          <th>Fund</th>
          <th>OU</th>
          <th>ImplA</th>
          <th>Donor</th>
          <th>Dept ID</th>
          <th>Project ID</th>
          <th>Activity</th>
          <th>PCBU</th>
          <th>GLU</th>
          <th>Curr</th>
          <th>Journal</th>
          <th>ErnDed CD</th>
          <th>ErnDed Acc</th>
          <th>Base Amount</th>
          <th>Value</th>
          <th>Person List</th>
        </tr>
      </thead>
      <tbody>
        
      </tbody>
    </table>
  </div>

  <script type="text/javascript"> 
    const selectedUserPositionVal = [];
    var generalNPComponent,tbl,optionGeneral;
    // $('#tablenameproject tbody').on('click', 'td input', function() {
    //     var checked = $(this).prop('checked');
    //     var val = $(this).prop('value');

    //     if (checked) {
    //         var valueToPush = new Array();
    //         valueToPush["id"] = val;
    //         selectedUserPositionVal.push(val);
    //         $('#checkbox_table').val(selectedUserPositionVal);
    //     } else {
    //       var valueToPush = new Array();
    //       valueToPush["id"] = val;
    //       for(var i in selectedUserPositionVal){
    //           if(selectedUserPositionVal[i] == val){
    //               myIndex = i;        
    //           }  
    //       }  
    //       if (myIndex !== -1) {
    //           selectedUserPositionVal.splice(myIndex, 1);
    //       }
    //       $('#checkbox_table').val(selectedUserPositionVal);
    //     }
    // });
    // $(document).on('click', '.btn-input-setting-table', function() {
    //   $('.btn-input-setting-table').css('display','none');
    //   $('.btn-loadin-setting-table').css('display','block');
    //   $('.btn-cancle-input-setting-table').attr('disabled','disabled');
    // });
    // $(document).on('click', '.btn-list-cancle', function() {
    //   $('#listPersonUl').html('');
    // });
    // function doLoadNPComponent() {
    //   $.ajax({
    //       url: '{!! route('project.get-inputname') !!}',
    //       type: 'post',
    //       dataType: 'json',
    //       success: function(data) {
    //         generalNPComponent = data.content;
    //         $('#inputnameproject').html(generalNPComponent);
    //         tbl = $('#tablenameproject').DataTable({
    //           columnDefs: [{ visible: false, targets: 0 }],
    //           displayLength: 10,
    //           ordering: false,
    //           drawCallback: function (settings) {
    //               var api = this.api();
    //               var rows = api.rows({ page: 'current' }).nodes();
    //               var last = null;
      
    //               api
    //                   .column(0, { page: 'current' })
    //                   .data()
    //                   .each(function (group, i) {
    //                       if (last !== group) {
    //                           $(rows)
    //                               .eq(i)
    //                               .before('<tr class="group"><td colspan="5">' + group + '</td></tr>');
      
    //                           last = group;
    //                       }
    //                   });
    //           },
    //         });
    //         $('.btn-settings-table').css('display','none');
    //         $('.add_settings_table').css('display','');
    //       },
    //       error: function(data) {}
    //   });
    // }
    function loading(){
      $('.btn-input-setting-form').css('display','none');
      $('.btn-loading').css('display','block');
      $('.btn-cancle-input').attr('disabled','disabled');
    }
    function person_list(id){
      $("#loadingProject").html("<?=\App\GeneralHelper::dt_loading_component();?>")
      $.ajax({
        type: 'POST',
        url: '{!! route('project.person-list') !!}',
        data: {
            id: id
        },
        success: function(data) {
          $("#loadingProject").html("<?=\App\GeneralHelper::message_dismissable('success', 'Project have been Open');?>")
          const dataLength = data.name.length;
          var html = '';
          for(let dataStart = 0; dataStart < dataLength; dataStart++){
            html += '<tr><td>'+data.name[dataStart]+'</td><td>'+data.position[dataStart]+'</td></tr>';
          }
          $('#listPersonUl').html(html);
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
            url: "<?=URL::to('admin/projects');?>" + '/' + id,
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
          { 
            className: 'btn btn-success',
            text: "<i class='fa fa-download'> Excel</i>", 
            init: function(api, node, config) {
              $(node).removeClass('dt-button')
            },
            action: function ( e, dt, button, config ) {
              var base = '{{ route('project.excel') }}';
              window.location = base;
            }
          }
        ],
        processing: true,
        serverSide: true,
        ajax: {
          url: "{!! route("project.ajax-list") !!}",
          data: function(d){
            d.roleUser =$('#roleUser').val();
            d.viewBy = viewBy;
          },
        },
        "columns": [
          { data: 'TIDNO', name: 'TIDNO' },
          { data: 'Name', name: 'Name' },
          { data: 'Email', name: 'Email' },
          { data: 'Fund', name: 'Fund' },
          { data: 'OperatingUnit', name: 'OperatingUnit' },
          { data: 'ImplementingAg', name: 'ImplementingAg' },
          { data: 'Donor', name: 'Donor' },
          { data: 'DeptID', name: 'DeptID' },
          { data: 'Project', name: 'Project' },
          { data: 'ProjAct', name: 'ProjAct' },
          { data: 'PCBusUnit', name: 'PCBusUnit' },
          { data: 'GLUnit', name: 'GLUnit' },
          { data: 'Currency', name: 'Currency' },
          { data: 'Journal', name: 'Journal' },
          { data: 'ErnDedCd', name: 'ErnDedCd' },
          { data: 'ErnDedAcc', name: 'ErnDedAcc' },
          { data: 'BaseAmount', name: 'BaseAmount' },
          { data: 'NumericValue', name: 'NumericValue' },
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