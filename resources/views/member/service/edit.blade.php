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
          <td>Service Condition</td>
          <td>
              <form action="{{ route('myservices.update_service', [$detail->id_transaction]) }}" method="POST">
                {{ csrf_field() }}
                <input type="hidden" name="id_transaction" value="{{ $detail->id_transaction }}">
                @if ($detail->is_free_of_charge != 1)
                  <input type="hidden" name="is_free_of_charge" value="1">
                  <button class="btn btn-warning">Make It Free of Charge</button>
                @else
                  <input type="hidden" name="is_free_of_charge" value="0">
                  <button class="btn btn-warning">Make It NOT Free of Charge</button>
                @endif
              </form>
          </td>
        </tr>
        <!-- end of payment method-->
    </tbody>
  </table>
  <div class="row">
    <form action="{{ route('myservices.update_service', [$detail->id_transaction]) }}" method="POST">
      {{ csrf_field() }}
      <div class="col-sm-12">
        <hr>
        <?php $no = 1 ;?>
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
              function show_user_options($users, $selected_user_id = null){

                $user_options = "<option value='0'>-- Choose PIC --</option>";
                foreach ($users as $value) {
                   $user_options .= "<option value='".$value->id_user."' ".($value->id_user == $selected_user_id ? 'selected' : '').">".$value->person_name."</option>";
                 }
                 return $user_options; 
              }
            ?>
            @foreach($workflows as $workflow)
              <?php
                $agency_unit_name = (!is_null($workflow->agency)) ? $workflow->agency->agency_unit_name : 'Requester'; 
                
              ?>
              <tr>
                <td align="center">{{ $workflow->sequence }}</td>
                <td>{{ $workflow->workflow_name }}</td>
                <td align="center">{{ $workflow->workflow_day }}</td>
                <td>{{ $agency_unit_name }}</td>
                @if ($workflow->workflow_day >= 1 || $workflow->sequence > 1)
                  <td>
                    <select class="form-control select2" name="primary_pics[{{ $workflow->id_transaction_workflow}}]">
                      <?=show_user_options($users, $workflow->id_user_pic_primary);?>
                    </select>
                  </td>
                  <td>
                    <select class="form-control select2" name="alternate_pics[{{ $workflow->id_transaction_workflow}}]">
                      <?=show_user_options($users, $workflow->id_user_pic_alternate);?>
                    </select>
                  </td>
                @else
                  <td>-</td>
                  <td>-</td>
                @endif
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="col-sm-12 text-center">
        <hr>
        <a href="{{ route('myservices.index') }}" class="btn btn-info"><i class="fa fa-arrow-left"></i> | &nbsp;Back</a>
        <a href="#" class="btn btn-danger" data-toggle="modal" data-target="#formCancel"><i class="fa fa-window-close"></i> | &nbsp;Cancel Service</a>
        <button class="btn btn-primary"><i class="fa fa-users"></i> | &nbsp;Update PIC</button>
      </div>
    </form>
  </div>

<!-- Modal -->
<div class="modal fade" id="formCancel" tabindex="-1" role="dialog" aria-labelledby="formCancel" aria-hidden="true">
  <form action="{{ route('myservices.update_service', [$detail->id_transaction]) }}" method="POST">
    {{ csrf_field() }}
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="formRejectLabel">Cancel Service</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>
          Service will be cancelled. <br>
          Service Price : <b> {{ ($detail->currency) ? $detail->currency->currency_name : 'USD' }} {{ $detail->service_price }}</b>
        </p>
        <input type="hidden" name="cancel" value="1">
        <input type="hidden" name="id_transaction" value="{{ $detail->id_transaction }}">
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary">Cancel Service</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
  </form>
</div>
<script type="text/javascript">
  $(function(){
    $('.select2').select2();
  })
</script>
@endsection
