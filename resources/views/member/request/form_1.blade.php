@extends('admin.index')
@section('content')
<?php
  $has_detail = isset($detail) && ($detail != null);
?>
<div class="kt-portlet">
  <div class="kt-portlet__head">
    <div class="kt-portlet__head-label">
      <h3 class="kt-portlet__head-title text-primary">
        {{ strtoupper($title) }} - <?=($has_detail) ? 'EDIT' : 'NEW';?>
      </h3>
    </div>
  </div>
  <div class="col-md-9 col-xs-12">
  <!--begin::Form-->
  <?php 
    if ($has_detail) {
      
      if (isset($source) && $source == 'tr_service') {
        $row_id = $detail->id_transaction ;
        $route = 'myrequests.update';
      }else {
        $row_id = $detail->id_draft;
        $route = 'myrequests.draft_update';
      }

      $url = route($route, [$row_id]);
      $method = 'POST';
      
      $id_service = $detail->id_service;
      $id_agency_unit_service = $detail->id_agency_unit_service;
      
      if (empty($id_agency_unit_service) && !empty($id_service)) {
        $id_agency_unit_service = \App\ServiceList::find($id_service)->id_agency_unit;
      }

      $id_agency_unit = \App\AgencyUnit::find($id_agency_unit_service)->parent->id_agency_unit;
      $description = $detail->description;
      $supervisor_mail = $detail->supervisor_mail;
      $id_currency = $detail->id_currency;
      $id_user_buyer = $detail->id_user_buyer;
      $id_agency_unit_buyer = $detail->id_agency_unit_buyer;
      $service_price = $detail->service_price;
      $qty = $detail->qty;
      $id_project = $detail->id_project;
      $date_transaction = $detail->date_transaction;
      $payment_method = $detail->payment_method;
      $all_notif_email = $detail->all_notif_email;
      $currency_code = $detail->currency_name;
    }else{
      $url = route('myrequests.store');
      $method = 'POST';
      $id_agency_unit = old('agency') ? old('agency') : 0;
      $id_agency_unit_service = old('id_agency_unit_service') ? old('id_agency_unit_service') : 0;
      $id_service = old('id_service') ? old('id_service') : 0;;
      $description = "";
      $supervisor_mail = "";
      $id_currency = old('id_currency') ? old('id_currency') : 1;
      $id_user_buyer = "";
      $id_agency_unit_buyer = "";
      $service_price = old('service_price') ? old('service_price') : 0;;
      $qty = old('qty') ? old('qty') : 1 ;
      $id_project = "";
      $date_transaction = Date('Y-m-d');
      $payment_method = old('payment_method') ? old('payment_method') : '';
      $all_notif_email = old('all_notif_email') ? old('all_notif_email') : 0;;
      $currency_code = 'IDR';
      $detail = null;
    }
  ?>

  <form class="kt-form" action=" {{ $url }}" method="POST" id="myForm" enctype="multipart/form-data">
    @method($method)
    {{ csrf_field() }}
    <p class="text-right">
      <br>
      <button type="submit" class="btn btn-warning" name="submit" value="dr_service"> Send to Draft </button>
    </p>
    <input type="hidden" name="id_user_buyer" value="{{ session('user_id') }}">
    <input type="hidden" name="id_agency_unit_buyer" value="{{ session('user_agency_unit_id') }}">

    <div class="kt-portlet__body">
      <div class="form-group form-group-last">
        @include('admin.messages')
      </div>
      <div id="loadingStatus"></div>
      <div id="step1">
        <div class="form-group">
          <label>Service Provider</label>
          <select class="form-control select2" required name="agency" id="agency" <?=$has_detail ? 'disabled' : '';?>>
            <option>-- Select provider --</option>
            @foreach($agencies as $agency)
              <option value="<?=$agency->id_agency_unit;?>" <?=(($agency->id_agency_unit == $id_agency_unit) ? 'selected': '');?>>
                {{ $agency->agency_unit_name. ' ('.$agency->agency_unit_code.')' }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="form-group">
          <label>Request Date</label>
          <div id="selected_date"> <b>{{ date('d-M-Y') }}</b></div>
        </div>
        <div class="form-group">
          <label for="exampleSelect1">Select Category</label>
          <select class="form-control select2" required id="agency_unit" name="id_agency_unit_service" <?=$has_detail ? 'disabled' : '';?>></select>
          @if ($has_detail)
            <input type="hidden" name="id_agency_unit_service" value="{{ $id_agency_unit_service }}">
          @endif
        </div>
        <div class="form-group">
          <label for="exampleSelect1">Select Service</label>
          <select class="form-control select2" required id="id_service" name="id_service" <?=$has_detail ? 'disabled' : '';?>></select>
          @if ($has_detail)
            <input type="hidden" name="id_service" value="{{ $id_service }}">
          @endif
        </div>
        <div class="form-group">
          <label for="exampleSelect1">Description</label>
          <textarea name="description" class="form-control" required id="description"><?=(!empty(old('description'))) ? old('description') : $description ;?></textarea>
        </div>
        <div class="form-group">
          <label for="exampleSelect1">Supervisor Email</label>
          <input type="email" name="supervisor_mail" class="form-control" required id="supervisor_mail" value="<?=(!empty(old('supervisor_mail'))) ? old('supervisor_mail') : $supervisor_mail ;?>">
        </div>
        <div class="form-group">
          <label for="exampleSelect1">Unit Price</label>
          <br />
          <input type="hidden" name="id_currency" class="form-control" required id="id_currency" value="{{ $id_currency }}">
          <input type="text" name="currency" class=""  id="currency" style="width: 90px" readonly="readonly" value="{{ $currency_code }}">
          <input type="number" name="service_price" class="" id="service_price" style="width: 150px" readonly="readonly" value="{{ $service_price }}">
        </div>
        <div class="form-group">
          <label for="exampleSelect1">Qty</label>
          <input type="number" name="qty" class="form-control" required id="qty" value="<?=(!empty(old('qty'))) ? old('qty') : $qty ;?>">
        </div>
        <div class="form-group">
          <label for="exampleSelect1">Total Price</label>
          <input type="number" name="total_price" class="form-control" required id="total_price" readonly="readonly" value="{{ $service_price }}">
        </div>
        <div class="form-group">
          <label>Receive All Email Notifications</label>
          <br>
          <input type="radio" name="all_notif_email" value="1" <?=($all_notif_email == 1) ? 'checked' : '';?>> Yes &nbsp;
          <input type="radio" name="all_notif_email" value="0" <?=($all_notif_email == 0) ? 'checked' : '';?>> No &nbsp;
        </div>
        <div class="form-group">
          <label>Payment Methods</label>
          <select class="form-control" required name="payment_method" id="payment_method">
            <option value="">--- Select Payment Method ---</option>
            @foreach($payment_methods as $key => $val)
              <option value="{{ $key }}" <?=($key == $payment_method ? 'selected' : '');?>>{{ $val }}</option>
            @endforeach
          </select>
          <p>&nbsp;</p>
          <div id="atlas">
            <table width="90%">
              <tr>
                Service Fee COA/COA For Payroll (for HR Transaction Only)
                <td style="width: 30%">
                  Search By Project Code
                  <select id="txtProject" class="form-control"></select>
                </td>
                <td style="width: 20%">
                  And Donor Search
                  <select id="txtDonor" class="form-control select2">
                    <option>select by donor</option>
                  </select>
                </td>
                <td style="width: 20%">
                  And Activity Search
                  <select id="txtActivity" class="form-control select2">
                    <option>select by activity </option>
                  </select>
                </td>
                <td align="left">
                  <div style="color: rgba(240, 248, 255, 0)">text</div>
                  <button type="button" id="btnSearchProject" class="btn btn-success" onclick="searchCoa()">
                  <i class="fa fa-plus"></i> Add</button>
                </td>
              </tr>
            </table>
            <br>
            <div id="loadingProject"></div>
            <div style="overflow-x : scroll">
              <table class="table table-striped table-bordered" style="width:100%" id="table_atlas">
                <thead>
                  <tr>
                    <th>#</th>
                    <th valign="top">FUND</th>
                    <th valign="top">Oper. Unit</th>
                    <th valign="top">Impl. Agent</th>
                    <th valign="top">Donor</th>
                    <th valign="top">Expenditure organization / Dept ID</th>
                    <th valign="top">PCBU</th>
                    <th valign="top">Project</th>
                    <th valign="top">Activity ID / Task</th>
                    <th valign="top">Award ID / Contract Number</th>
                    <th valign="top">Expenditure Type *</th>
                    <th valign="top">Funding Source </th>
                    <th valign="top">%</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>

          <div id="non_atlas">
              <div class="pb-3" style="overflow-x : scroll;">
                <table class="table table-striped table-bordered" style="width:100%" id="table_non_atlas">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th valign="top">Unliquidated Obligation No</th>
                      <th valign="top">Agency Reference No</th>
                      <th valign="top">Project/Account No</th>
                      <th valign="top">Percentage(%)</th>
                      <th valign="top">Document(*)</th>
                    </tr>
                  </thead>  
                  <tbody></tbody>
                </table>
                <button type="button" onclick="addRowNonAtlas()" class="btn btn-primary"><i class="fa fa-plus"></i> Add FA</button>
              </div>
          </div>
          <div id="cash">
            @include("member.request.cash_tf")
          </div>
        </div>
        <div class="col-lg-12">
          <div class="col-lg-12 text-right">
            <button type="button" class="btn btn-default" onclick="goToStep(2)" id="btnStep2"> Continue >></button>
            <div id="messageAreaStep1"></div>
          </div>
        </div>
      </div><!-- end of s1 -->
      <div class="form-group" id="step2">
        <div class="col-lg-12">
          <div class="col-sm-10">
            <table class="table">
              <tr>
                <td style="width: 212px">Service Provider</td>
                <td><div id="selected_service_provider"></div></td>
              </tr>
              <tr>
                <td>Service</td>
                <td><div class="selected_service"></div></td>
              </tr>
              <tr>
                <td>Quantity</td>
                <td><div class="selected_price"></div></td>
              </tr>
            </table>
          </div>
          <div class="col-lg-12">
            <h5 class="alert alert-primary">Required Information</h5>
            <div id="required_infos"></div>
            <h5 class="alert alert-primary">Required Document</h5>
            <div id="required_docs"></div>
          </div>
          <div class="col-lg-12">
            <table class="table">
              <tr>
                <td align="left"><button type="button" class="btn btn-default" onclick="goToStep(1)"><< Previous </button></td>
                <td align="right"><button type="button" class="btn btn-default" onclick="goToStep(3)"> Continue >></button></td>
              </tr>
            </table>
          </div>
        </div>
      </div>
      <div id="step3">
        <div class="col-lg-12">
          <table class="table">
            <tr>
              <td>Agency</td>
              <td><div id="selected_agency"></div></td>
            </tr>
            <tr>
              <td>Request Date</td>
              <td><div id="selected_date"> {{ date('d-M-Y') }}</div></td>
            </tr>
            <tr>
              <td>Service</td>
              <td><div class="selected_service"></div></td>
            </tr>
            <tr>
              <td>Service Workflows</td>
              <td>
                <div id="workflows">
                  <table class="table table-striped table-bordered" id="table_workflows">
                    <thead>
                      <tr>
                        <th>Sequence</th>
                        <th>Activity</th>
                        <th>Workday(s)</th>
                        <th>Unit in Charge</th>
                        <th>Included</th>
                      </tr>
                    </thead>
                    <tbody></tbody>
                  </table>
                </div>
              </td>
            </tr>
            <tr>
              <td>Supervisor Email</td>
              <td><div id="selected_spv"></div></td>
            </tr>
            <tr>
              <td>Unit Price</td>
              <td><div id="selected_unit_price"></div></td>
            </tr>
            <tr>
              <td>Qty</td>
              <td><div class="selected_price"></div></td>
            </tr>
            <tr>
              <td>Total Amount</td>
              <td><div class="total_price"></div></td>
            </tr>
            <tr>
              <td>Payment Method</td>
              <td><div id="selected_payment_method"></div></td>
            </tr>
            <tr>
              <td>Required Info</td>
              <td><div id="selected_required_info"></div></td>
            </tr>
            <tr>
              <td>Required Doc</td>
              <td><div id="selected_required_doc"></div></td>
            </tr>
          </table>
          <div class="col-lg-12 col-xl-12" style="max-height: 250px; overflow-y: scroll;">
            @include("member.request.t_and_c")
          </div>
          <table class="table">
            <tr>
              <td colspan="2">
                <input type="checkbox" name="tnc_confirmation" id="tncConfirmation" value="1"> I already read and accept terms and conditions. 
              </td>
            </tr>
            <tr id='submitLoadingStatus'>
              <td colspan="2" align="center">
                <i class='fa fa-2x fa-spinner fa-spin'></i> &nbsp; Processing ...
              </td>
            </tr>
            <tr>
              <td align="left"><button type="button" class="btn btn-default" onclick="goToStep(2)"><< Previous </button></td>
              <td align="right">
                <button type="submit" class="btn btn-warning" name="submit" value="dr_service"> Send to Draft </button>
                <?php $route_name = Route::currentRouteName();?>
                @if (!$has_detail || ($route_name == 'myrequests.draft_edit') || ($has_detail && $route_name == 'myrequests.edit' && $detail->status < 2))
                  <button type="submit" class="btn btn-primary" name="submit" value="tr_service" id="btnSave"> Send My Request </button>
                @endif
              </td>
            </tr>
          </table>
        </div>
      </div>
  </form>
  <!--end::Form-->
  </div>

</div>
<script type="text/javascript">
  var arrinfo = [];
  var arrdoc = [];
  var countarrcoanonatlas = 0;

  function searchCoa(){
    var project = $("#txtProject").val();
    var donor = $("#txtDonor").val();
    var activity = $("#txtActivity").val();
    $.ajax({
      url: "{{ URL::to('member-area/coa/search_by_project') }}" + '/' + project + '?donor='+donor+'&activity='+activity,
      type: 'GET',
      dataType: 'json',
      beforeSend: function(){
        $("#loadingProject").html("<?=\App\GeneralHelper::dt_loading_component();?>")
      },
      success: function(data){
        $("#loadingProject").html("<?=\App\GeneralHelper::message_dismissable('success', 'Project have been added');?>")
        var tRow = "";
        var exptype = "<option value=''>-- Select Expenditure Type --</option>";
        var countAtlas = 0; 
        $.each(data.exp_type, function(a,b){
          exptype += "<option value="+b.id_exptype+">"+b.exp_type_code+" - "+b.exp_type_name+"</option>"
        })
        $.each(data.data, function(k,v){
          tRow += "<tr id='rowAtlas"+v.id_master_coa+""+countAtlas+"'>";
          tRow += "<td><button type='button' onclick='deleteTableRow(\"rowAtlas"+v.id_master_coa+""+countAtlas+"\")'><i class='fa fa-trash'></i></button></td>";
          tRow += "<td>"+v.fund+"</td>";
          tRow += "<td>"+v.opu+"</td>";
          tRow += "<td>"+v.imp_agent+"</td>";
          tRow += "<td>"+v.donor+"</td>";
          tRow += "<td><input type='text' name='atlas_depts["+v.id_master_coa+"]' style='width: 95px'></td>";
          tRow += "<td>"+v.pcbu+"</td>";
          tRow += "<td>"+v.project+"</td>";
          tRow += "<td>"+v.activities+"</td>";
          tRow += "<td><input type='text' name='atlas_contract_num["+v.id_master_coa+"]' style='width: 95px'></td>";
          tRow += "<td><select name='atlas_exp_type["+v.id_master_coa+"]' class='form-control'>"+exptype+"</select></td>";
          tRow += "<td><input type='text' name='atlas_funding_source["+v.id_master_coa+"]' style='width: 95px'></td>";
          tRow += "<td><input type='text' name='atlas_percents["+v.id_master_coa+"]' style='width: 95px' class='percentage_fields'></td>";
          tRow += "</tr>";
          countAtlas++;
        });
        $("#table_atlas tbody").append(tRow);
      }
    })
  }

  function addRowNonAtlas(){
    var tRowNA = "";
    var tRowNAId = "<?=date('His');?>";
    tRowNA += "<tr id='rowNonAtlas"+tRowNAId+"'>";
    tRowNA += "<td><button type='button' onclick='deleteTableRow(\"rowNonAtlas"+tRowNAId+"\")'><i class='fa fa-trash'></i></button></td>";
    tRowNA += "<td><input type='text' name='non_atlas_ulos[]' style='width: 100%'></td>";
    tRowNA += "<td><input type='text' name='non_atlas_arns[]' style='width: 100%'></td>";
    tRowNA += "<td><input type='text' name='non_atlas_projects[]' style='width: 100%'></td>";
    tRowNA += "<td><input type='text' name='non_atlas_percents[]' style='width: 100%' class='percentage_fields'></td>";
    tRowNA += "<td><input type='file' class='required-field-coa' id='non_atlas_files_"+countarrcoanonatlas+"' name='non_atlas_files[]' style='width: 210px'></td>";
    tRowNA += "</tr>";
    $("#table_non_atlas tbody").append(tRowNA);  
    countarrcoanonatlas = countarrcoanonatlas + 1;
    console.log(countarrcoanonatlas)
  }

  function deleteTableRow(divId){
    $("#"+divId).remove();
    countarrcoanonatlas = countarrcoanonatlas - 1;
    console.log(countarrcoanonatlas)
  }

  function serviceList(idCountry, idParent) {

    $.ajax({
      url: "<?=route('api-list-service-list-search-by');?>" + "?parent_only=1&with_price=true&id_parent=" + idParent,
      dataType: 'json',
      beforeSend: function(){
        $("#loadingStatus").html("Loading ....");
      },
      success: function(data){
        $("#loadingStatus").html("");
        $("#id_service").html("");
        console.log(data);
        // $("#id_service").html("<option>--- Select Service ---</option>");
          $("#id_service").append("<option value='' selected>---Select Service---</option>");
        $.each(data.data, function(k, value){
          selected = value.id_service == <?=$id_service;?> ? 'selected' : '';
          $("#id_service").append("<option value='"+value.id_service+"' "+selected+">"+value.service_name+"</option>");
        })
      }
    })
  }

  function agencyList(idParent) {
    $.ajax({
      url: "<?=route('api-list-agency-units-search-by');?>" + "?all=1&id_parent=" +idParent,
      dataType: 'json',
      beforeSend: function(){
        $("#loadingStatus").html("Loading ....");
      },
      success: function(data){
        $("#loadingStatus").html("");
        $("#agency_unit").html("");
        $.each(data.data, function(k, value){
          selected = value.id_agency_unit == <?=$id_agency_unit_service;?> ? 'selected' : '';
          $("#agency_unit").append("<option value='"+value.id_agency_unit+"' "+selected+">"+ value.agency_unit_name+"</option>");
        });
        serviceList(null, $("#agency_unit").val());
      }
    })
  }

  function getDataArrinfo(id, value){
    arrinfo.push([id, value]);
  }

  function getDataArrdocs(id, value,doc_id){
    var file = value.files[0];  
    var filename = file.name;
    var file_upload = $('#'+doc_id)[0].files;
    
    arrdoc.push([id, filename]);
  }

  function implementArrInfo(item, index, arr) {
    $("#valueinfo_"+item[0]).html(item[1]);
  }
  function implementArrDocs(item, index, arr) {
    html = item[1];
    $("#valuedocs_"+item[0]).html(html);
  }

  function goToStep(step = 1) {
    if (step == 1) {
      $("#step2, #step3").css('display', 'none');
      $("#step1").css('display', 'block');
    }else if(step == 2) {
      const arrayreqcoa = $(".required-field-coa").map(function(i,v)
        {return $(v).attr("id")
      }).toArray();
      for (let i in arrayreqcoa) {
        let requiredcoa = $("#"+arrayreqcoa[i]);
        console.log('requiredcoa = ',requiredcoa[0].files.length,requiredcoa)
        if(requiredcoa[0].files.length == 0){
          $("#"+arrayreqcoa[i]).focus();
          alert("Please complete the required upload file first!!");
          return false;
        }
      }
      let sum = checkPercentage();
      console.log("SUM", sum);
      let payment_method = $("#payment_method").val();
      if (payment_method != 'transfer_cash' && payment_method != "" && (sum != 100 || isNaN(sum))){
        $("#messageAreaStep1").html("<br><p class='alert alert-danger text-center'>Percentage of this payment method must be 100%</p>");
        return false;
      }else{
        $("#messageAreaStep1").html("");
      }
      if (!checkRequiredFields()){
        return false;
      }
      $("#step1, #step3").css('display', 'none');
      $("#step2").css('display', 'block');

    }else{
      const arrayreq = $(".required-field").map(function(i,v)
        {return $(v).attr("id")
      }).toArray();
      for (let i in arrayreq) {
        let required = $("#"+arrayreq[i]);
        let reqatt = $("#"+arrayreq[i]).attr("type");
        if(reqatt == 'text'){
          if(required.val() == ""){
            $("#"+arrayreq[i]).focus();
            return false;
          }          
        }else{
          if(required[0].files.length == 0){
            $("#"+arrayreq[i]).focus();
            alert("Please complete the required upload file first!!");
            return false;
          }
        }
      }
      $("#step1, #step2").css('display', 'none');
      $("#step3").css('display', 'block');
      arrinfo.forEach(implementArrInfo);
      arrdoc.forEach(implementArrDocs);
    }
  }

  function checkPercentage(){
    var sum = 0;
    $('.percentage_fields').each(function(){
        sum += parseFloat(this.value);
    });
    return sum;
  }

  function checkRequiredFields(){
    let description = $("#description").val();
    if (description == null || description == "") {
      $("#description").focus();
      return false;
    }
    
    let supervisor_mail = $("#supervisor_mail").val();
    if (supervisor_mail == null || supervisor_mail == "") {
      $("#supervisor_mail").focus();
      return false;
    }


    return true;
  }

  function calculateTotalPrice(){
    $("#total_price").val(parseFloat($("#qty").val()) * parseFloat($("#service_price").val()));
    $(".selected_price").html($("#qty").val());
    $("#selected_unit_price").html($("#currency").val() + ' ' + $("#service_price").val());
    $(".total_price").html($("#currency").val() + ' ' + $("#total_price").val());
  }

  $(function(){
    $("#atlas,#non_atlas,#cash,#submitLoadingStatus").css('display', 'none');
    
    $("#btnSave").click(function(){
      let supervisor_mail = $("#supervisor_mail").val();
      if (supervisor_mail == null || supervisor_mail == ""){
        swal.fire({
          "title": "",
          "text": "Please Fill Supervisor Email to continue",
          "type": "error",
          "confirmButtonClass": "btn btn-secondary",
          "onClose": function(e) {
            console.log('on close event fired!');
          }
        });
        return false;
      }

      let description = $("#description").val();
      if (description == null || description == ""){
        swal.fire({
          "title": "",
          "text": "Please Fill Description to continue",
          "type": "error",
          "confirmButtonClass": "btn btn-secondary",
          "onClose": function(e) {
            console.log('on close event fired!');
          }
        });
        return false;
      }

      let tncConfirmation = $("#tncConfirmation").val();
      if (!$('#tncConfirmation').is(":checked")){
        $("#tncConfirmation").focus();
        swal.fire({
          "title": "",
          "text": "Please accept terms and conditions to continue",
          "type": "error",
          "confirmButtonClass": "btn btn-secondary",
          "onClose": function(e) {
            console.log('on close event fired!');
          }
        });
        return false;
      }

      $("#submitLoadingStatus").css('display', '');
      $(".required-field").prop('required', 'true');
      $( "#myForm" ).validate({
        rules: {
          supervisor_mail: {
            required: true,
            email: true
          },
          tnc_confirmation: {
            required: true
          }
        },
        messages: {
          tnc_confirmation: "Please accept terms and conditions to continue"
        },
        invalidHandler: function(event, validator) {
          $("#submitLoadingStatus").css('display', 'none');
          var errors = validator.numberOfInvalids();
          if (errors) {
            var message = errors == 1
              ? 'Please correct the following error:\n '
              : 'Please correct the following ' + errors + ' errors.\n ';
            var errors = "";
            if (validator.errorList.length > 0) {
              for (x=0;x<validator.errorList.length;x++) {
                var elementName = validator.errorList[x].element.name;
                errors += "\n\u25CF " + elementName.toUpperCase();
              }
            }
            swal.fire({
              "title": "",
              "text": message + errors,
              "type": "error",
              "confirmButtonClass": "btn btn-secondary",
              "onClose": function(e) {
                console.log('on close event fired!');
              }
            });
          }
          validator.focusInvalid();
          event.preventDefault();
        },
      });
    });

    goToStep(1);

    $("#btnStep2").css('display', 'none');
    $(".select2").select2();
    $("#txtProject").select2({
      width: '100%',
      placeholder: "Search for Project Code",
      minimumInputLength: 1,
      cache: true,
      ajax: {
        url: "{{ route('myrequests.get_projects') }}",
        dataType: 'json',
        type: "GET",
        data: function (params) {
          return {
            keyword: params.term
          };
        },
        processResults: function(data) 
        {
          return {
            results: $.map(data.data, function(obj) {
              return {
                id: obj.id,
                text: obj.text
              };
            })
          };
        },
      }
    });
    $("#txtProject").on("change", function() {
      var project = $(this).val();
      $.ajax({
        url: "{{ URL::to('member-area/coa/search_by_project') }}" + '/' + project,
        type: 'GET',
        dataType: 'json',
        success: function(data){
          const uniquedonor = [...new Set(data.data.map(item => item.donor))];
          const uniqueactivities = [...new Set(data.data.map(item => item.activities))];

          var txtDonor = $("#txtDonor"); 
          var txtActivity = $("#txtActivity"); 

          txtDonor.empty();
          txtActivity.empty();

          var optiondonor = $("<option></option>").attr("value", "").text('select by donor');
          txtDonor.append(optiondonor);
          var optionactivity = $("<option></option>").attr("value", "").text('select by Activity');
          txtActivity.append(optionactivity);

          uniquedonor.forEach(function(donor) {
            optiondonor = $("<option></option>").attr("value", donor).text(donor);
            txtDonor.append(optiondonor);
          });
          uniqueactivities.forEach(function(donor) {
            optionactivity = $("<option></option>").attr("value", donor).text(donor);
            txtActivity.append(optionactivity);
          });
        }
      })
    });

    $("#qty").change(function(){
      calculateTotalPrice();
    });
    $("#supervisor_mail").keyup(function(){
      $("#selected_spv").html($("#supervisor_mail").val());
    });

    function updatePaymentDetail(){
      if ($("#payment_method").val() == 'atlas') {
        $("#table_non_atlas tbody").empty();
        $("#non_atlas,#cash").css('display', 'none');
        $("#atlas").css('display', 'block');
      }else if($("#payment_method").val() == 'non_atlas'){
        $("#table_atlas tbody").empty();
        $("#atlas,#cash").css('display', 'none');
        $("#non_atlas").css('display', 'block');
      }else{
        $("#atlas,#non_atlas").css('display', 'none');
        $("#cash").css('display', 'block');
      }

      $("#selected_payment_method").html($("#payment_method option:selected").text());
    }

    $("#payment_method").change(function(){
      updatePaymentDetail();
    });

    $("#agency").change(function(){
      $("#selected_agency").html($('#agency option:selected').text());
      agencyList($("#agency").val());
    });

    $("#agency_unit").change(function(){
      serviceList(null, $("#agency_unit").val());
    });

    $("#id_service").change(function(){
      $(".selected_service").html($('#agency_unit option:selected').text() + 
        ' - ' + $('#id_service option:selected').text());
      
      idService = $("#id_service").val();

      $.ajax({
        url: "{{ URL::to('member-area/service_list/show_as_json') }}" + '/' + idService,
        type: 'GET',
        dataType: 'json',
        beforeSend: function(){
          $("#btnStep2").css('display', 'none');
        },
        success: function(data){
          console.log(data);
          $("#required_docs").html("");
          $("#required_infos").html("");
          $("#price").html("");
          var serviceData = data.data;

          if (serviceData && serviceData.active_price != null) {
            $("#service_price").val(serviceData.total_price);
            $("#currency").val(serviceData.active_price.currency.currency_code);
            $("#id_currency").val(serviceData.active_price.currency.id_currency);

            $("#selected_service_provider").html($("#agency option:selected").text());
            $("#selected_service").html($("#id_service option:selected").text());
            $("#selected_qty").html($("#qty").val());

            $("#btnStep2").css('display', '');

            if (serviceData.required_docs != null) {
              var htmlDoc = "<table class='table table-striped table-bordered'><tr><td>Seq</td><td>Document</td><td>Note</td><td>File</td></tr>";
              var htmlDoc2 = "<table class='table table-striped table-bordered'><tr><td>Seq</td><td>Document</td><td>Note</td><td>Docs Name</td></tr>";
              var count = 1;
              $.each(serviceData.required_docs, function(i, v){
                var isMandatory = "";
                var isRequired = "";

                if (v.is_mandatory == 1){
                  isMandatory = " <span class='text-danger'>*</span>";
                  isRequired = "class='required-field'";
                }

                htmlDoc += "<tr><td>"+count+"</td><td>"+v.document_name+isMandatory+"</td>";
                htmlDoc += "<td>"+v.description+"</td><td id='doc_"+v.id_service_workflow_doc+"'><input type='file' id='doc_id_"+v.id_service_workflow_doc+"' name='required_docs["+v.id_service_workflow_doc+"]' "+isRequired+" onchange='getDataArrdocs("+v.id_service_workflow_doc+",this,this.id)'></td>";
                htmlDoc2 += "<tr><td>"+count+"</td><td>"+v.document_name+isMandatory+"</td>";
                htmlDoc2 += "<td>"+v.description+"</td><td id='valuedocs_"+v.id_service_workflow_doc+"'></td>"; 
                count++;
              });

              htmlDoc += "</table>";
              $("#required_docs").html(htmlDoc);
              $("#selected_required_doc").html(htmlDoc2);
            }

            if (serviceData.required_infos != null) {
              var htmlInfo = "<table class='table table-striped table-bordered'><tr><td>Seq</td><td>Information</td><td>Description</td><td>Info Value</td></tr>";
              var htmlInfo2 = "<table class='table table-striped table-bordered'><tr><td>Seq</td><td>Information</td><td>Description</td><td>Value</td></tr>";
              var count = 1;
              $.each(serviceData.required_infos, function(i, v){
                var isMandatory = "";
                var isRequired = "";

                if (v.is_mandatory == 1){
                  isMandatory = " <span class='text-danger'>*</span>";
                  isRequired = "class='required-field'";
                }
                htmlInfo += "<tr><td>"+count+"</td><td>"+v.info_title+isMandatory+"</td>";
                htmlInfo += "<td>"+v.description+"</td><td><input type='text' name='required_infos["+v.id_service_workflow_info+"]' "+isRequired+" id='info_"+v.id_service_workflow_info+"' onchange='getDataArrinfo("+v.id_service_workflow_info+", this.value)'></td>"; 
                htmlInfo2 += "<tr><td>"+count+"</td><td>"+v.info_title+isMandatory+"</td>";
                htmlInfo2 += "<td>"+v.description+"</td><td id='valueinfo_"+v.id_service_workflow_info+"'></td>";
                count++;
              });
              htmlInfo += "</table>";
              $("#required_infos").html(htmlInfo);
              $("#selected_required_info").html(htmlInfo2);
            }

            if (serviceData.group_workflows != null && serviceData.group_workflows.length > 0) {
              $("#table_workflows tbody").empty();
              var tbodyWorkflows = "";
              var checkCount = 1;
              var id_service = 0;
              $.each(serviceData.group_workflows, function(i, v){
                id_service = v.id;
                tbodyWorkflows += "<tr><td colspan='6'>"+v.name+"</td></tr>";
                var subServiceCount = 1;
                $.each(v.sub_services, function(i2,v2){
                  tbodyWorkflows += "<tr><td align='center'>" + v2.sequence;
                  tbodyWorkflows += "<input type='hidden' name='workflows["+v2.id_service_workflow+"]'";
                  var workflowPrice = (parseFloat(v2.price) > 0) ? parseFloat(v2.price) : 0;
                  tbodyWorkflows += "value='"+workflowPrice+"'></td>";
                  tbodyWorkflows += "<td>"+v2.name+"</td><td>"+v2.workflow_day+"</td><td>"+v2.agency+"</td>";
                  if (subServiceCount == 1){
                    
                    tbodyWorkflows += "<td style='vertical-align: middle' rowspan='"+ v.sub_services.length +"'><input type='checkbox' class='checkbox_price' id='checkboxprice"+checkCount+"' name='checkbox_price_input["+id_service+"]' checked onclick='testingcheckbox(this.id)' value='"+(v2.price == null ? '-' : v2.price)+"'><label for='checkboxprice"+checkCount+"'></label> <br> "+(v2.price == null ? '-' : v2.price)+"</td>";
                    checkCount++;
                  }
                  tbodyWorkflows += "</tr>";
                  subServiceCount++;
                });
              });
              $("#table_workflows tbody").append(tbodyWorkflows);
            }

            setTimeout(function(){
              calculateTotalPrice();
            }, 1000);
          }else{
            swal.fire("WARNING", "Sorry, Service doesn't have Active Price. Please, Contact Admin", 'error');
          }
        },
        error: function(error){
          console.log(error);
          alert("Server Error");
        }
      })
    })

    /*-- handle EDIT --*/

    @if ($has_detail || $id_agency_unit > 0)
      $("#agency").trigger("change");
      @if ($payment_method == 'atlas')
        var tRow = "";
        var countAtlas = 1;
        @if ($has_detail)
          
            
          
          @foreach($detail->payments() as $payment)
            var exptype = "<option value=''>-- Select Expenditure Type --</option>";
            var select = "";
            @foreach($exptype as $exp)
              @if($payment->id_exptype == $exp->id_exptype)
                select = "selected";
              @endif
              exptype += "<option value='<?=$exp->id_exptype;?>' "+select+"><?=$exp->exp_type_code;?> - <?=$exp->exp_type_name;?></option>";
              select = "";
            @endforeach
            tRow += "<tr id='rowAtlas<?=$payment->id_master_coa;?>"+countAtlas+"'>";
            tRow += "<td><button type='button' onclick='deleteTableRow(\"rowAtlas<?=$payment->id_master_coa;?>"+countAtlas+"\")'><i class='fa fa-trash'></i></button></td>";
            tRow += "<td><?=$payment->fund;?></td>";
            tRow += "<td><?=$payment->opu;?></td>";
            tRow += "<td><?=$payment->imp_agent;?></td>";
            tRow += "<td><?=$payment->donor;?></td>";
            tRow += "<td><input type='text' name='atlas_depts[<?=$payment->id_master_coa;?>]' style='width: 95px' value='<?=$payment->dept;?>'></td>";
            tRow += "<td><?=$payment->pcbu;?></td>";
            tRow += "<td><?=$payment->project;?></td>";
            tRow += "<td><?=$payment->activities;?></td>";
            tRow += "<td><input type='text' name='atlas_contract_num[<?=$payment->id_master_coa;?>]' style='width: 95px' value='<?=$payment->contract_number;?>'></td>";
            tRow += "<td><select name='atlas_exp_type[<?=$payment->id_master_coa;?>]' class='form-control'>"+exptype+"</select></td>";
            tRow += "<td><input type='text' name='atlas_funding_source[<?=$payment->id_master_coa;?>]' style='width: 95px' value='<?=$payment->funding_source;?>'></td>";
            tRow += "<td><input type='number' name='atlas_percents[<?=$payment->id_master_coa;?>]' style='width: 95px' value='<?=$payment->percentage;?>' class='percentage_fields'></td>";
            tRow += "</tr>";
            countAtlas++;
          @endforeach
        @endif
        $("#table_atlas tbody").append(tRow);

      @elseif ($payment_method == 'non_atlas')
        var tRowNA = "";
        @if ($has_detail)
          @foreach($detail->payments() as $payment)
            var tRowNAId = "<?=date('His').$payment->id_transaction_coa_other;?>";
            tRowNA += "<tr id='rowNonAtlas"+tRowNAId+"'>";
            tRowNA += "<td><button type='button' onclick='deleteTableRow(\"rowNonAtlas"+tRowNAId+"\")'><i class='fa fa-trash'></i></button></td>";
            tRowNA += "<td><input type='text' name='non_atlas_ulos[]' style='width: 95px' value='<?=$payment->ulo;?>'></td>";
            tRowNA += "<td><input type='text' name='non_atlas_arns[]' style='width: 95px' value='<?=$payment->arn;?>'></td>";
            tRowNA += "<td><input type='text' name='non_atlas_projects[]' style='width: 95px' value='<?=$payment->project_no;?>'></td>";
            tRowNA += "<td><input type='text' name='non_atlas_percents[]' style='width: 95px' value='<?=$payment->percentage;?>' class='percentage_fields'></td>";
            tRowNA += "<td id='non_atlas_files_<?=$payment->ulo;?>_<?=$payment->arn;?>_<?=$payment->project_no;?>'><input type='file' name='non_atlas_files[]' id='non_atlas_files_"+countarrcoanonatlas+"' class='required-field-coa' style='width: 95px'></td>";
            tRowNA += "</tr>";
          @endforeach
        @endif
        $("#table_non_atlas tbody").append(tRowNA);
      @endif
      updatePaymentDetail();
      setTimeout(function(){
        $("#id_service, #qty").trigger("change");
        $("#supervisor_mail").trigger("keyup");
        setTimeout(function(){
          <?php
            if ($has_detail){
              if(isset($detail->id_draft)){
                
                $service_docs = $detail->required_docs_new($detail->id_draft);
                
                if ($payment_method == 'non_atlas'){
                  $service_coa_other_docs = $detail->required_coa_docs($detail->id_draft);
                  if (!is_null($service_coa_other_docs) && count($service_coa_other_docs) > 0){
                    foreach ($service_coa_other_docs as $value_coa) {
                      if($value_coa->file_path != ""){
                        $url_coa_other_doc = '<a href='. asset($value_coa->file_path) .' target="_blank"><i class="fa fa-download"></i></a>';
                        $url_coa_other_doc .= '&ensp; <a class="btn bg-danger text-light" href='. route('myservices.delete_temporary_doc_draft_coa_other', ['id_workflow_doc_coa_other' => $value_coa->id_transaction_coa_other]).'>X</a>';
                        echo "$('#non_atlas_files_".$value_coa->ulo."_".$value_coa->arn."_".$value_coa->project_no."').html('".$url_coa_other_doc."');";
                      }else{
                        $url_coa_other_doc = '<input type="file" name="non_atlas_files[]" style="width: 95px">';
                        echo "$('#non_atlas_files_".$value_coa->ulo."_".$value_coa->arn."_".$value_coa->project_no."').html('".$url_coa_other_doc."');";
                      }
                    }
                  }
                }
                if (!is_null($service_docs) && count($service_docs) > 0){
                  foreach ($service_docs as $value) {
                    if($value->workflowDoc->doc_name != ""){
                      $url_doc = '<a href='. asset($value->workflowDoc->doc_name) .' target="_blank"><i class="fa fa-download"></i></a>';
                      $url_doc .= '&ensp; <a class="btn bg-danger text-light" href='. route('myservices.delete_temporary_doc_draft', ['id_transaction_workflow_doc' => $value->workflowDoc->id_draft_doc]).'>X</a>';
                      // dd("$('#doc_".$value->id_workflow."').html('".$url_doc."');");
                      echo "$('#doc_".$value->workflowDoc->id_workflow."').html('".$url_doc."');";
                    }else{
                      $isRequired = null;
                      if ($value->is_mandatory == 1){
                        $isRequired = 'class="required-field"';
                      }
                      $url_doc = '<input type="file" id="doc_id_'.$value->workflowDoc->id_service_workflow_doc.'" name="required_docs['.$value->workflowDoc->id_service_workflow_doc.']" '.$isRequired.'>';
                      echo "$('#doc_".$value->workflowDoc->id_service_workflow_doc."').html('".$url_doc."');";
                    }
                  }
                }
              }else{
                $service_infos = $detail->required_infos();
                $service_docs = $detail->required_docs();
                
                if (!is_null($service_infos) && count($service_infos) > 0){
                  foreach ($service_infos as $value) {
                    echo "$('#info_".$value->id_service_workflow_info."').val('".$value->info_value."');\n";
                  }
                }
                if (!is_null($service_docs) && count($service_docs) > 0){
                  foreach ($service_docs as $value) {
                    if($value->document_path != "" || $value->document_path !== ""){
                      $url_doc = '<a href='. asset($value->document_path) .' target="_blank"><i class="fa fa-download"></i></a>';
                      $url_doc .= '&ensp; <a class="btn bg-danger text-light" href='. route('myservices.delete_temporary_doc', ['id_transaction_workflow_doc' => $value->id_transaction_workflow_doc]).'>X</a>';
                      echo "$('#doc_".$value->id_service_workflow_doc."').html('".$url_doc."');";
                    }else{
                      $isRequired = null;
                      if ($value->is_mandatory == 1){
                        $isRequired = 'class="required-field"';
                      }
                      $url_doc = '<input type="file" id="doc_id_'.$value->id_service_workflow_doc.'" name="required_docs['.$value->id_service_workflow_doc.']" '.$isRequired.'>';
                      echo "$('#doc_".$value->id_service_workflow_doc."').html('".$url_doc."');";
                    }
                  }
                }
              }
              

            }
          ?>
        }, 300);

      }, 2500);

    @endif
  })

  function testingcheckbox(valueId){
    
    if (valueId == "checkboxprice1"){
      console.log(valueId);
      // $("input:checkbox[id="+valueId+"]:checked").attr('checked', 'checked');
      document.getElementById(valueId).checked = true;
    }
    var allvalue = 0;
    $("input:checkbox[class=checkbox_price]:checked").each(function(){
        allvalue += parseFloat($(this).val());
    });
    allvalue = allvalue.toFixed(2)
    // document.getElementById("selected_unit_price").innerHTML = allvalue.toFixed(2);
    // document.getElementById("total_price").innerHTML = allvalue.toFixed(2);
    // document.getElementById("total_price").value = allvalue.toFixed(2);
    $("#total_price").val(parseFloat($("#qty").val()) * parseFloat(allvalue));
    $(".selected_price").html($("#qty").val());
    $("#service_price").val(allvalue);
    $("#selected_unit_price").html($("#currency").val() + ' ' + $("#service_price").val());
    $(".total_price").html($("#currency").val() + ' ' + $("#total_price").val());
  }
    // $("#checkboxprice1").on('click', function() {
    //   console.log($('.checkbox_price').filter(':checked').length);
    // });


</script>
@endsection