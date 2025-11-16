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
                        <td><a href="{{ $doc->document_path }}" target="_blank"><i class="fa fa-download"></i></a></td>
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
        <!-- end of payment method-->
    </tbody>
  </table>
  <div class="row">
    <div class="col-sm-12">
      <hr>
      <div id="accordion">
        <?php $no = 1 ;?>
        @foreach($workflows as $workflow)
          <?php if ($workflow->sequence == 1) continue;?>
          <div class="card">
            <div class="card-header">
              <a class="card-link" data-toggle="collapse" href="#collapse<?=$workflow->sequence;?>">
                <b>Sequence {{ $no }} - </b>{{ $workflow->workflow_name }} 
              </a>
            </div>
            <div id="collapse<?=$workflow->sequence;?>" class="collapse" data-parent="#accordion">
              @if (count($workflow->docs) > 0 )
                <table class="table p-3">
                <tr>
                  <th>Doc</th>
                  <th>Value</th>
                </tr>
                <?php $a=[]; ?>
                @foreach($workflow->docs as $doc)
                  <tr>
                    <td>{{ $doc->document_name }}</td>
                    <td>
                      <a href="{{ URL::to($doc->document_path)}}" target="_blank">Download</a>
                    </td>
                  </tr>
                  <?php $a[$doc->id_service_workflow_doc]= $doc->id_service_workflow_doc; ?>
                @endforeach
                </table>
              @else
                <p class="p-3"><b>No Required Documents</b></p>
              @endif
              <div class="card-body">
                 <table class="table">
                  <tr>
                    <td>SLA workday (s) : {{ $workflow->workflow_day }}</td>
                    <td>Actual Day (s) : <?=$workflow->date_end_actual ==  null ? date('Y-m-d H:i:s') : '';?></td>
                    <?php 
                      $pic = $workflow->primary_pic;
                      $finisher = $workflow->finisher();
                      $agency_unit_name = (!is_null($workflow->agency)) ? $workflow->agency->agency_unit_name : 'Requester';
                      $finisher_agency_unit_name = (!is_null($finisher) && !is_null($finisher->agency)) ? $finisher->agency->agency_unit_name : 'Requester';
                    ?>
                    <td>
                      Agency Unit In charge: {{ ($pic != null) ? ($pic->person_name.' - '.$agency_unit_name) : '-' }}
                      <br>Completed By: {{ $finisher != null ? $finisher->person_name.' - '.$finisher_agency_unit_name : '' }}
                    </td>
                  </tr>
                </table>
              </div>
            </div>
          </div>
          <?php $no++;?>
        @endforeach
      </div>
    </div>
    <div class="col-sm-12">
      <hr>
      <p class="text-center"><a href="{{ route('myservices.index') }}" class="btn btn-info"><i class="fa fa-arrow-left"></i> Back</a></p>
    </div>
  </div>
@endsection