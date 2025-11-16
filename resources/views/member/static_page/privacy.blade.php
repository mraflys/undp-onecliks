@extends('admin.index')
@section('content')
<div class="box-center">
  <table class="table table-striped table-borderd">
    <tr style="height:15px;background-color:#207CE5;">
      <!-- <th style="color:#fff;font-size:15px;">No</th> -->
      <th style="color:#fff;font-size:15px;">Description</th>
      <th style="color:#fff;font-size:15px; text-align: center;">Option</th>
    </tr>
    <tr style="height:20px;">
      <!-- <td style="text-align:center">1</td> -->
      <td>&nbsp;&nbsp;One-Click Application User Manual for Requester</td>
      <td style="text-align:center">
        <a href="<?php echo URL::to("assets/manual")."/"?>UNDP_One_Click_Manual_for_Requester.pdf" target="_blank">Download</a> </td>
    </tr>
    <tr style="height:20px;">
      <!-- <td style="text-align:center">2</td> -->
      <td>&nbsp;&nbsp;One-Click Application User Manual for Service Unit</td>
      <td style="text-align:center">
        <a href="<?php echo URL::to("assets/manual")."/"?>UNDP_One_Click_Manual_for_ServiceUnit.pdf" target="_blank">Download</a>
      </td>
    </tr>
  </table>
</div>

@endsection