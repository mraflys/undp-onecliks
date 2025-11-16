@extends('admin.index')
@section('content')

<div class="form-group form-group-last">
  @include('admin.messages')
</div>
<h4 class="alert alert-info">{{ $detail->transaction_code }}</h4>
<table cellpadding="5" class="table table-striped">
      <tbody>
        {{-- <tr>
            <td class="td-judul">Request Date</td>
            <td>{{ date('d-M-Y H:i', strtotime($detail->date_created)) }}</td>
        </tr>
        <tr>
            <td class="td-judul">Estimated Start Date</td>
            <td>{{ date('d-M-Y', strtotime($detail->date_created)) }}</td>
        </tr> --}}
        <tr>
            <td class="td-judul">Request Date</td>
            <td>{{ date('d-M-Y H:i', strtotime($detail->date_transaction)) }}</td>
        </tr>
        <tr>
            <td class="td-judul">Estimated Start Date</td>
            <td>{{ date('d-M-Y', strtotime($detail->date_transaction)) }}</td>
        </tr>
        <tr>
            <td class="td-judul">Requester</td>
            <td>{{ $detail->person_name_buyer.' - '.$detail->agency_name_buyer }} <img src=""></td>
        </tr> 
        <tr>
            <td class="td-judul">Service</td>
            <td>{{ $detail->service_name }}</td>
        </tr>    
        <tr>
            <td class="td-judul">Short Description</td>
            <td>{{ $detail->description }}</td>
        </tr>    
        <tr>
            <td class="td-judul">Unit Price</td>
            <td>{{ ($detail->currency) ? $detail->currency->currency_name : 'USD' }} {{ $detail->service_price }}</td>
        </tr> 
        <tr>
            <td class="td-judul">Quantity</td>
            <td>{{ $detail->qty }}</td>
        </tr>
        <tr>
            <td class="td-judul">Total Amount</td>
            <td>{{ ($detail->currency) ? $detail->currency->currency_name : 'USD' }} {{ $detail->service_price * $detail->qty }}</td>
        </tr>
        <!-- payment method -->
        <tr>
            <td class="td-judul">Payment Method</td>
            <td>{{ \App\GeneralHelper::payment_methods()[$detail->payment_method] }}</td>
        </tr>
        <tr>
          @if ($detail->payment_method == 'transfer_cash')
            <td class="td-judul">Payment Information</td>
            <td>@include("member.request.cash_tf")</td>
          @else
            <td style="vertical-align: middle;">Service Fee COA/COA For Payroll (for HR Transaction Only)</td>
            <td>
              <div class="table-generator">
                <table>
                  <thead>
                    <tr>
                      @if ($detail->payment_method == 'atlas')
                        <th>FUND</th>
                        <th>Oper. Unit</th>
                        <th>Impl. Agent</th>
                        <th>Donor</th>
                        <th>Expenditure organization / Dept ID</th>
                        <th>PCBU</th>
                        <th>Project</th>
                        <th>Activity ID / Task</th>
                        <th>Award ID / Contract Number</th>
                        <th>Expenditure Type *</th>
                        <th>Funding Source </th>
                        <th>%</th>
                      @else
                        <th>Unliquidated Obligation No</th>
                        <th>Agency Reference No</th>
                        <th>Project No</th>
                        <th>File</th>
                        <th>%</th>
                      @endif
                    </tr>
                  </thead>
                  <tbody>
                    @if ($detail->payment_method == 'atlas')
                      @foreach($detail->payments() as $payment)
                        @if($detail->payment_atlas_presentage()==100)
                          <tr>
                            <td>{{ $payment->fund }}</td>
                            <td>{{ $payment->opu }}</td>
                            <td>{{ $payment->imp_agent }}</td>
                            <td>{{ $payment->donor }}</td>
                            <td>{{ $payment->dept }}</td>
                            <td>{{ $payment->pcbu }}</td>
                            <td>{{ $payment->project }}</td>
                            <td>{{ $payment->activities }}</td>
                            <td>{{ $payment->contract_number }}</td>
                            <td>{{ $payment->exp_type_code }} - {{ $payment->exp_type_name }}</td>
                            <td>{{ $payment->funding_source }}</td>
                            <td>{{ $payment->percentage }}</td>
                          </tr>
                        @endif
                      @endforeach
                    @else
                      @foreach($detail->payments() as $payment)
                        @if($payment->percentage==100)
                          <tr>
                            <td>{{ $payment->ulo }}</td>
                            <td>{{ $payment->arn }}</td>
                            <td>{{ $payment->project_no }}</td>
                            <td>{{ $payment->file_path }}</td>
                            <td>{{ $payment->percentage }}</td>
                          </tr>
                        @endif
                      @endforeach
                    @endif
                    {{-- @if ($detail->payment_method == 'atlas')
                      @foreach($detail->payments() as $payment)
                        <tr>
                          <td>{{ $payment->opu }}</td>
                          <td>{{ $payment->fund }}</td>
                          <td>{{ $payment->dept }}</td>
                          <td>{{ $payment->imp_agent }}</td>
                          <td>{{ $payment->donor }}</td>
                          <td>{{ $payment->pcbu }}</td>
                          <td>{{ $payment->project }}</td>
                          <td>{{ $payment->activities }}</td>
                          <td>{{ $payment->percentage }}</td>
                        </tr>
                      @endforeach
                    @else
                      @foreach($detail->payments() as $payment)
                        <tr>
                          <td>{{ $payment->ulo }}</td>
                          <td>{{ $payment->arn }}</td>
                          <td>{{ $payment->project_no }}</td>
                          <td>{{ $payment->file_path }}</td>
                          <td>{{ $payment->percentage }}</td>
                        </tr>
                      @endforeach
                    @endif --}}
                  </tbody>
                </table>
              </div>
            </td>
          @endif
        </tr>
        @if (!is_null($detail->comments()))
          <tr>
            <td>Comments</td>
            <td>
              <table class="table table-striped">
                <tbody>
                  <?php 
                    $comments = $detail->comments();
                  ?>
                  @foreach($comments as $comment)
                    <tr>
                      <td>
                        Source: <b>{{ $comment->type }}</b><br>
                        workflow name: {{ $comment->workflow_name }}</br>
                        <hr>
                        <b>{{ $comment->created_by }}</b> comment at <small>{{ $comment->date_created }}</small> <br><br>
                        <i>{{ $comment->comment }}</i>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </td>
          </tr>
        @endif
        <tr>
          <td>Required Informations</td>
          <td>
            @if (count($infos) > 0)
              <table class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <td>#</td>
                    <td>Title</td>
                    <td>Information</td>
                    <td>Description</td>
                  </tr>
                </thead>
                <tbody>
                  <?php $count = 1;?>
                  @foreach($infos as $info)
                    <tr>
                      <td>{{ $count }}</td>
                      <td>{{ $info->info_title }}</td>
                      <td>{{ $info->info_value }}</td>
                      <td>{{ $info->description }}</td>
                      <?php $count++;?>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            @else
              no information required
            @endif
          </td>
        </tr>
        <tr>
          <td>Required Documents</td>
          <td>
            @if (count($docs) > 0)
              <table class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <td>#</td>
                    <td>Document Name</td>
                    <td>Download</td>
                    <td>Note</td>
                  </tr>
                </thead>
                <tbody>
                  <?php $count = 1;?>
                  @foreach($docs as $doc)
                    <tr>
                      <td>{{ $count }}</td>
                      <td>{{ $doc->document_name }}</td>
                      <td>
                        @if($doc->document_path != "")
                          <a href="{{ asset($doc->document_path) }}" target="_blank"><i class="fa fa-download"></i></a> 
                          &ensp; <a class="btn bg-danger text-light" href="{{ route('myservices.delete_temporary_doc', ['id_transaction_workflow_doc' => $doc->id_transaction_workflow_doc])  }}">X</a>
                        @else
                          <div class="loader" id="loader{{$doc->id_transaction_workflow_doc}}" style="display:none"></div>
                          <div class="fileupload{{$doc->id_transaction_workflow_doc}}">
                            <form id="uploadmail{{$doc->id_transaction_workflow_doc}}" enctype="multipart/form-data" autocomplete="off">
                              <input type="file" id="{{$doc->id_transaction_workflow_doc}}" name="required_docs[{{$doc->id_transaction_workflow_doc}}]" @if($doc->is_mandatory==1) required @endif onchange="uploadfile(this.id)">
                            </form>
                          </div>
                        @endif
                      </td>
                      <td>{{ $doc->note }}</td>
                      <?php $count++;?>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            @else
              no document required
            @endif
          </td>
        </tr>
        <tr>
          <td>Service Condition</td>
          <td><input type="checkbox" id="is_free_of_charge" value="1"> &nbsp; Free of Charge ? </td>
        </tr>
        <!-- end of payment method-->
    </tbody>
  </table>
  <div class="row">
    <div class="col-sm-5">
      <a href="#" class="btn btn-danger" data-toggle="modal" data-target="#formReject">Reject Request</a>
      <a href="#" class="btn btn-warning" data-toggle="modal" data-target="#formReturn">Return Request</a>
      <a href="{{ route('myservices.index') }}" class="btn btn-info">Back to List</a>
    </div>
    <div class="col-sm-6 text-right">
      <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#formPic">Assign PIC</a>
    </div>
  </div>

<!-- Modal -->
<div class="modal fade" id="formReject" tabindex="-1" role="dialog" aria-labelledby="formRejectLabel" aria-hidden="true">
  <form action="{{ route('myservices.reject') }}" method="POST">
    {{ csrf_field() }}
    <input type="text" hidden name="id_transaction" value="{{$detail->id_transaction}}">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="formRejectLabel">Reject Service</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
          <small>Please provide reason for Rejection</small>
          <textarea class="form-control" name="comment"></textarea>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary">Reject</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
      </div>
    </div>
  </div>
  </form>
</div>

<div class="modal fade" id="formReturn" tabindex="-1" role="dialog" aria-labelledby="formReturnLabel" aria-hidden="true">
  <form action="{{ route('myservices.return') }}" method="POST">
    {{ csrf_field() }}
    <input type="text" hidden name="id_transaction" value="{{$detail->id_transaction}}">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="formReturnLabel">Return Service</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
          <small>Please provide reason for Return</small>
          <textarea class="form-control" name="comment"></textarea>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary">Return</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
      </div>
    </div>
  </div>
  </form>
</div>

<div class="modal fade" id="formPic" tabindex="-1" role="dialog" aria-labelledby="formPicLabel" aria-hidden="true">
  <div class="modal-dialog" role="document" style="max-width: 1000px">
    <div class="modal-content" style="width: 1000px">
      <div class="modal-header">
        <h5 class="modal-title" id="formPicLabel">Assign PIC</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ route('post.myservices.assign_pic') }}" method="POST" style="width: 1000px">
        {{ csrf_field() }}
        <input type="text" hidden name="id_transaction" value="{{$detail->id_transaction}}">
        <div class="modal-body">
            <div id="chartArea"></div>
            <input type="hidden" id="free_of_charge" name="is_free_of_charge">
            <table class="table table-striped table-bordered">
              <thead>
                <tr>
                  <td align="center">Seq</td>
                  <td>Activity</td>
                  <td align="center">Workday(s)</td>
                  <td>Unit in Charge</td>
                  <td>PIC (Primary)</td>
                  <td>PIC (Alternate)</td>
                </tr>
              </thead>
              <tbody>
                <?php
                  // prevent loop in loop
                  function user_options($users, $current_pic_id = null){
                    $user_options = "<option value='0'>-- Choose PIC --</option>";
                    foreach ($users as $value) {
                       $user_options .= "<option value='".$value->id_user."' ".(($value->id_user == $current_pic_id) ? 'selected': '').">".$value->person_name."</option>";
                     }
                     return $user_options; 
                   }
                ?>
                @foreach($workflows as $workflow)
                  <?php
                    $agency_unit_name = (!is_null($workflow->agency)) ? $workflow->agency->agency_unit_name : 'Requester';
                    if($agency_unit_name == 'Requester'){
                      $workflow->id_user_pic_primary = $detail->id_user_buyer;
                    }
                    if ($workflow->sequence == 1) continue;
                  ?>
                  <tr>
                    <td align="center">{{ ($workflow->sequence - 1) }}</td>
                    <td>{{ $workflow->workflow_name }}</td>
                    <td align="center">{{ $workflow->workflow_day }}</td>
                    <td>{{ $agency_unit_name }}</td>
                    <td>
                      <select class="form-control select2" name="primary_pics[{{ $workflow->id_transaction_workflow}}]">
                        <?=user_options($users, $workflow->id_user_pic_primary);?>
                      </select>
                    </td>
                    <td>
                      <select class="form-control select2" name="alternate_pics[{{ $workflow->id_transaction_workflow}}]">
                        <?=user_options($users, $workflow->id_user_pic_alternate);?>
                      </select>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
            <div class="text-center"><?=\App\GeneralHelper::basic_loading_component();?></p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-primary" id="assignPic">Confirm & Assign PIC</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script type="text/javascript">
  $(document).ready(function (e) {
    $.ajaxSetup({
      headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });
  });
  function showAlert(type = 'success', message = 'Data has been updated!'){
    swal.fire({
      title: "Message",
      text: message,
      type: type,
      buttonsStyling: false,
      confirmButtonText: "Close",
      confirmButtonClass: "btn btn-brand"
    });
  }
  function uploadfile(varname){
    $('.fileupload' + varname).css('display', 'none');
    document.getElementById('loader' + varname).style.display = "";
    var form = $('form#uploadmail' + varname)[0];
    var formData = new FormData(form);
    console.log(formData);
    $.ajax({
        url: '{!! route('myservices.upload_final_doc') !!}',
        type: 'post',
        data: formData,
        processData: false,
        contentType: false,
        success: function(data) {
          window.location.reload();
        },
        error: function(data) {
           
        }
    });
  }
  $(function(){
    $("#basicLoadingComponent").css('display', 'none');
    $("#assignPic").click(function(){
      $("#basicLoadingComponent").css('display', '');
    })
    $(".select2").select2();
    $("#is_free_of_charge").click(function(){
      if ($("#is_free_of_charge").is(':checked')){
        $("#free_of_charge").val(1)
      }else{
        $("#free_of_charge").val(0)
      }
    });

    <?php 
      if (!is_null(session('message'))) {
        echo "showAlert('".session('message_type')."','".session('message')."')";
      }
    ?>
  })
</script>
@endsection
