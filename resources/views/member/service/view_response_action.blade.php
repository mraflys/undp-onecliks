@extends('admin.index')
@section('content')

<div class="form-group form-group-last">
  @include('admin.messages')
</div>
<h4 class="alert alert-info" id="tiket-tr">{{ $detail->transaction_code }}</h4>
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
            <td class="td-judul">Description</td>
            <td>
              <form action="{{ route('myservices.update_service', [$detail->id_transaction]) }}" method="POST">
                {{ csrf_field() }}
                <input type="hidden" name="id_transaction" value="{{ $detail->id_transaction }}">
                <textarea class="form-control" name="description">{{ $detail->description }}</textarea>
                <br><button class="btn btn-primary">Update</button>
              </form>
            </td>
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
        <!-- end of payment method-->
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
                    @if($workflows[0]->id_transaction_workflow == $info->id_transaction_workflow)
                      <tr>
                        <td>{{ $count }}</td>
                        <td>{{ $info->info_title }}</td>
                        <td>{{ $info->info_value }}</td>
                        <td>{{ $info->description }}</td>
                        <?php $count++;?>
                      </tr>
                    @endif
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
                    @if($workflows[0]->id_transaction_workflow == $doc->id_transaction_workflow)
                      <tr>
                        <td>{{ $count }}</td>
                        <td>{{ $doc->document_name }}</td>
                        <td><a href="{{ asset($doc->document_path) }}" target="_blank"><i class="fa fa-download"></i></a></td>
                        <td>{{ $doc->note }}</td>
                        <?php $count++;?>
                      </tr>
                    @endif
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
          <td><?=$detail->is_free_of_charge != 1 ? 'Not' : '';?> Free of Charge</td>
        </tr>
    </tbody>
  </table>
  <div class="row-x">
    <!-- accordion -->
    <div id="accordion">
      <form action="{{ route('myservices.confirm_service', [$detail->id_transaction]) }}" method="POST" enctype="multipart/form-data">
        {{ csrf_field() }}
        <?php $count = 1 ;?>

        @foreach($workflows as $workflow)
          <?php
            if ($workflow->sequence == 1) continue;
            $agency_unit_name = (!is_null($workflow->agency)) ? $workflow->agency->agency_unit_name : 'Requester';
            $user_primary = !is_null($workflow->primary_pic) ? $workflow->primary_pic->person_name : '';
            $user_alternate = !is_null($workflow->alternate_pic) ? $workflow->alternate_pic->person_name : '';
            $completer = !is_null($workflow->finisher()) ? $workflow->finisher()->person_name : '';
            $end_date = is_null($workflow->date_end_actual) ? Date('Y-m-d') : $workflow->date_end_actual;
            $current = (!is_null($workflow->date_start_actual) && is_null($workflow->date_end_actual));
            $workflow->date_start_actual = is_null($workflow->date_start_actual) ? Date('Y-m-d') : $workflow->date_start_actual;
          ?>
          <div class="card">
            <div class="card-header">
              <a class="card-link" data-toggle="collapse" href="#collapse<?=$workflow->sequence;?>">
                <b>Sequence {{ $count }} - </b>{{ $workflow->workflow_name }} {{ ($current) ? ' - current ' : '' }} 
              </a>
            </div>
            <div id="collapse<?=$workflow->sequence;?>" class="collapse <?=($current) ? 'show' : '';?>" data-parent="#accordion">
              <div class="card-body">
                <table class="table">
                  <tr>
                    <td>SLA workday : {{ $workflow->workflow_day }} Day(s)</td>
                    <td>Actual workday : {{ \App\GeneralHelper::get_number_workday($end_date, $workflow->date_start_actual) }}</td>
                    <td>
                      Agency Unit In Charge: {{ $user_primary.' / '.$user_alternate.' - '.$agency_unit_name }}<br>
                      Completed By : {{ $completer }}
                    </td>
                  </tr>
                </table>
                
                @if (count($workflow->infos) > 0 )
                  <table class="table">
                  <tr>
                    <th>Info</th>
                    <th>description</th>
                    <th>Value</th>
                  </tr>
                  <?php $b=[]; ?>
                  @foreach($workflow->infos as $info)
                    <tr>
                      <tr>
                        <td>{{ $info->info_title }}</td>
                        <td>{{ $info->description }}</td>
                        <td>
                          {{ $info->info_value }}@if ($current)<a class="ml-3 btn bg-danger text-light" href="{{ route('myservices.delete_info', ['id_transaction_workflow_info' => $info->id_transaction_workflow_info])  }}">X</a> @endif
                        </td>
                      </tr>
                    </tr>
                    <?php $b[$info->id_service_workflow_info]= $info->id_service_workflow_info;?>
                  @endforeach
                  </table>
                @else
                  <p><b>No Informations</b></p>
                @endif
                
                @if (count($workflow->docs) > 0 )
                  <table class="table">
                  <tr>
                    <th>Doc</th>
                    <th>Description</th>
                    <th>Value</th>
                  </tr>
                  <?php $a=[]; ?>
                  @foreach($workflow->docs as $doc)
                    <tr>
                      <td>{{ $doc->document_name }}</td>
                      <td>{{ $doc->description }}</td>
                      <td>
                        <a href="{{ URL::to($doc->document_path)}}" target="_blank">Download</a> @if ($current)<a class="btn bg-danger text-light" href="{{ route('myservices.delete_doc', ['id_transaction_workflow_doc' => $doc->id_transaction_workflow_doc])  }}">X</a> @endif
                      </td>
                    </tr>
                    <?php $a[$doc->id_service_workflow_doc]= $doc->id_service_workflow_doc; ?>
                  @endforeach
                  </table>
                @else
                  <p><b>No Documents</b></p>
                @endif

                @if ($current)
                  <div class="p-3"><h5>Required Informations</h5></div>
                  <table class="table">
                  <tr>
                    <th>Info</th>
                    <th>Description</th>
                    <th>Value</th>
                  </tr>
                  @foreach($service_info_list as $service_info)
                    @if($service_info->workflow_name==$workflow->workflow_name)
                      @foreach($service_info->infos as $info)
                        @if(!isset($b[$info->id_service_workflow_info]))
                        <tr>
                          <td>{{ $info->info_title }} @if($info->is_mandatory==1) <span class="text-danger">*</span> @endif</td>
                          <td>{{ $info->description }}</td>
                          <td>
                            <input type="text" name="infos[{{ $info->id_service_workflow_info }}]" @if($info->is_mandatory==1) required @endif value="{{ $info->value }}">
                          </td>
                        </tr>
                        @endif
                      @endforeach
                    @endif
                  @endforeach
                  </table>

                  <div class="p-3"><h5>Required Documents</h5></div>
                  <table class="table">
                    <tr>
                      <th>Doc</th>
                      <th>Value</th>
                    </tr>
                    @foreach($service_lists as $service_list)
                      @if($service_list->workflow_name==$workflow->workflow_name)
                        @foreach($service_list->docs as $doc)
                          @if(!isset($a[$doc->id_service_workflow_doc]))
                            <tr>
                              <td>{{ $doc->document_name }} @if($doc->is_mandatory==1) <span class="text-danger">*</span> @endif </td>
                              <td>{{ $doc->description }}</td>
                              <td>
                                <input type="file" name="required_docs[{{$doc->id_service_workflow_doc}}]" @if($doc->is_mandatory==1) required @endif>
                              </td>
                            </tr> 
                          @endif
                        @endforeach
                      @endif
                    @endforeach
                  </table>
                  <div class="col-sm-12">
                    <h5 class="alert alert-info text-center">Rework Workflow</h5>
                    <table class="table">
                      <tr>
                        <td align="right">
                          <input type="hidden" id="id_transaction_workflow_rework" name="id_transaction_workflow" value="<?php echo $workflow->id_transaction_workflow;?>"/>
                          Additional Cost : USD
                        </td>
                        <td> <input type="text" name="add_price" id="add_price_rework" value="0" style="width: 100px" /></td>
                        <td align="right">Additional Work Days : </td>
                        <td><input type="text" name="add_workday" id="add_workday_rework" value="0" style="width: 100px" /> Day(s)</td>
                      </tr>
                      <tr>
                        <td align="right">Comment : </td>
                        <td colspan=5>
                          <textarea style="width: 100%" rows="2" id="rework_comment" name="rework_comment"></textarea>
                        </td>
                      </tr>
                      <tr>
                        <td></td>
                        <td colspan="5">
                          <input type="checkbox" id="rework_notify_all" name="cb_rework" value="1" />
                          <label for="cb_rework">Notify requester</label>&nbsp;
                          <button type="button" id="rework-action" name="action" class='btn btn-primary' value="rework">Rework</button>
                        </td>
                      </tr>
                    </table>
                  </div>
                  @if ($workflow->sequence > 2)
                    <div class="col-sm-12">
                      <h5 class="alert alert-danger text-center">Goback Workflow</h5>
                      <table class="table">
                        <tr>
                          <td align="right">Comment : </td>
                          <td colspan=5>
                            <input type="hidden" id="id_transaction_workflow_goback" name="id_transaction_workflow" value="<?php echo $workflow->id_transaction_workflow;?>"/>
                            <textarea style="width: 100%" rows="2" id="goback_comment" name="goback_comment"></textarea>
                          </td>
                        </tr>
                        <tr>
                          <td></td>
                          <td colspan="5">
                            <input type="checkbox" id="goback_notify_all" name="cb_goback" value="1" />
                            <label for="cb_goback">Notify requester</label>&nbsp;
                            <button type="button" id="goback-action" name="action" class='btn btn-danger' value="goback">Goback</button>
                          </td>
                        </tr>
                      </table>
                    </div>
                  @endif
                @endif
              </div>
            </div>
          </div>
          <?php $count++;?>
        @endforeach
        <div class="col-sm-12 text-center d-flex mt-5 p-0">
          <hr>
          <a href="{{ route('myservices.index') }}" class="btn btn-info"><i class="fa fa-arrow-left"></i> | &nbsp;Back</a>
          @if(session('user_role_id') != 3)
            <a href="#" class="btn btn-danger mx-2" data-toggle="modal" data-target="#formReturn"><i class="fa fa-window-close"></i> | &nbsp;Return Service</a>
          @endif
          <button class="btn btn-primary"><i class="fa fa-arrow-right"></i> | &nbsp;Confirm & Go To Next Workflow</button>
          <a href="#" class="btn btn-danger ml-auto" data-toggle="modal" data-target="#formFinish"><i class="fa fa-check"></i> | &nbsp;Finish Service</a>
        </div>
      </form>
    </div>
  </div>

<!-- Modal -->
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
<div class="modal fade" id="formFinish" tabindex="-1" role="dialog" aria-labelledby="formFinishLabel" aria-hidden="true">
  <form action="{{ route('myservices.finish_service', [$detail->id_transaction]) }}" method="GET">
    {{ csrf_field() }}
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="formFinishLabel">Are you sure to finish this service?</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary">Finish</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
      </div>
    </div>
  </div>
  </form>
</div>
<script>
  $(document).on('click', '#rework-action', function() {
    var id_transaction_workflow_value = document.getElementById('id_transaction_workflow_rework').value;
    var add_price_value = document.getElementById('add_price_rework').value;
    var add_workday_value = document.getElementById('add_workday_rework').value;
    var rework_comment_value = document.getElementById('rework_comment').value;
    var cb_rework_value = document.getElementById('rework_notify_all').value;
    var tiker_tr_value = document.getElementById('tiket-tr').innerHTML;
    $.ajax({
        type: 'post',
        url: '{!! route('myservices.rework_workflow') !!}',
        data: {
          id_transaction_workflow: id_transaction_workflow_value,
          add_price: add_price_value,
          add_workday: add_workday_value,
          rework_comment: rework_comment_value,
          cb_rework: cb_rework_value,
          tiker_tr: tiker_tr_value,
          type: 'rework'
        },
        success: function(data) {
          window.location.href = data+'?status=rework&tiker_tr='+tiker_tr_value+'&type=rework';
        },
        error: function(data) {

        }
    });
  });
  $(document).on('click', '#goback-action', function() {
    var id_transaction_workflow_value = document.getElementById('id_transaction_workflow_goback').value;
    var cb_goback_value = document.getElementById('goback_notify_all').value;
    var goback_comment_value = document.getElementById('goback_comment').value;
    var tiker_tr_value = document.getElementById('tiket-tr').innerHTML;
    $.ajax({
        type: 'post',
        url: '{!! route('myservices.rework_workflow') !!}',
        data: {
          id_transaction_workflow: id_transaction_workflow_value,
          goback_comment: goback_comment_value,
          cb_goback: cb_goback_value,
          tiker_tr: tiker_tr_value,
          type: 'goback'
        },
        success: function(data) {
          window.location.href = data+'?status=goback&tiker_tr='+tiker_tr_value+'&type=goback';
        },
        error: function(data) {

        }
    });
  });
</script>
@endsection