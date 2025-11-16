@extends('admin.index')
@section('content')
<div class="bar-blue"><h1>Frequently Asked Question</h1></div>
<br />
    <style type="text/css">
        .FAQ {
            cursor: hand;
            cursor: pointer;
            border: 1px solid #CCC;
            width: 860px;
            margin-top: 10px;
            padding: 10px;
            color: #333;
        }

        .ANS {
            display: none;
            margin-top: 7px;
        }
    </style>
    <script type="text/javascript">
        function toggle(Info) {
            var CState = document.getElementById(Info);
            CState.style.display = (CState.style.display != 'block')
                                 ? 'block' : 'none';
        }
</script>
	<div id="vhtm533lnntu" class="hid abs" style="left:26px; top:347px; width:905px; height:1649px;">
            <p><font face="Verdana" size="3" color="blue"> Click on a question to show the answer - click again to hide it. </font></p>
            <div class="FAQ" onclick="toggle('faq1');">
                <font face="times new roman" size="3"> <i> 1. I have submitted a new request, what to do next?  </i></font>
                <div id="faq1" class="ANS" style="display: none;">
                    <font face="Verdana" size="3">
                        <p><br />After submitted a new request, UNDP Agency will review your request and confirm it. Upon confirmation, you will soon get notified by One-Click Application about your request and service status to your email.</p>
                        <p>You can also check by accessing <b>My Request</b> >> <b>My Ongoing Request</b> menu and search for your ticket number.</p>
                        <p><img src="<?php echo URL::to("assets/image")."/"?>1_myrequest_screen.png" width="800px" alt="My Ongoing Request"></p>
                    </font>
                </div>
            </div>

            <div class="FAQ" onclick="toggle('faq2');">
                <font face="times new roman" size="3"> <i> 2. My Request got returned, what should I do?  </i></font>
                <div id="faq2" class="ANS" style="display: none;">
                    <font face="Verdana" size="3">
                        <p><br />After you submitted a new request, UNDP Agency will review your request. If your request information/document is incomplete, UNDP can return your request so you can complete it and send it back.</p>
                        <p>Please check <b>My Request</b> >> <b>My Ongoing Request</b> menu and search for your ticket number. Click on <u style="color:blue">Please Respond</u> link and you can view the reason, complete any information/document required and send it back to UNDP Agency.</p>
                        <p><img src="<?php echo URL::to("assets/image")."/"?>2_returned_request.png" width="800px" alt="Returned Request"></p>
                    </font>
                </div>
            </div>

            <div class="FAQ" onclick="toggle('faq3');">
                <font face="times new roman" size="3"> <i> 3. I have submitted a request, but I want to cancel it. What should I do?  </i></font>
                <div id="faq3" class="ANS" style="display: none;">
                    <font face="Verdana" size="3">
                        <p><br />Cancellation can only be processed by UNDP Service Unit. You can contact them by accessing to <b>My Request</b> >> <b>My Ongoing Request</b>, click on your Ticket Number to view the request detail.</p>
                        <p><img src="<?php echo URL::to("assets/image")."/"?>3_view_detail.png" width="800px" alt="view detail link"></p>
                        <p>In the request detail page, at the Workflow section you can click on PIC Primary / PIC Alternate name to email them about your cancellation request. They will respond to your query immediately.</p>
                        <p><img src="<?php echo URL::to("assets/image")."/"?>3_pic_screen.png" width="800px" alt="view detail page"></p>
                    </font>
                </div>
            </div>

            <div class="FAQ" onclick="toggle('faq4');">
                <font face="times new roman" size="3"> <i> 4. My request has completed, why is it still appear in My Ongoing Request Page?</i></font>
                <div id="faq4" class="ANS" style="display: none;">
                    <font face="Verdana" size="3">
                        <p><br />After a service is complete, you need to rate the request to have it completely finished.</p>
                        <p>You can access to <b>My Service</b> >> <b>My Ongoing Request</b> menu, click on <u style="color:blue">Please Rate</u> link and please rate your satisfaction for that spesific request.</p>
                        <p><img src="<?php echo URL::to("assets/image")."/"?>4_please_rate.png" width="800px" alt="please rate link"></p>
                    </font>
                </div>
            </div>

            <div class="FAQ" onclick="toggle('faq5');">
                <font face="times new roman" size="3"> <i> 5. I want to request a service that is not in the New Request form, what should I do?</i></font>
                <div id="faq5" class="ANS" style="display: none;">
                    <font face="Verdana" size="3">
                        <p><br />We have registered all services in this system, but if you need a spesific service that is not listed in the <b>New Request</b> form, you can contact us at:</p>
                        <p>
                            <ul>
                                <li>Admin Service Unit: <a href="mailto:admin.id@undp.org">admin.id@undp.org</a></li>
                                <li>Human Resource Unit: <a href="mailto:hr.id@undp.org">hr.id@undp.org</a></li>
                                <li>Information Technology Unit: <a href="mailto:ict.id@undp.org">ict.id@undp.org</a></li>
                                <li>Procurement Unit: <a href="mailto:procurement.id@undp.org">procurement.id@undp.org</a></li>
                                <li>Finance Unit: <a href="mailto:frmu.id@undp.org">frmu.id@undp.org</a></li>
                            </ul>
                        </p>
                    </font>
                </div>
            </div>

            <div class="FAQ" onclick="toggle('faq6');">
                <font face="times new roman" size="3"> <i> 6. How to print My Ongoing request list?</i></font>
                <div id="faq6" class="ANS" style="display: none;">
                    <font face="Verdana" size="3">
                        <p><br />One-Click Application provide export to excel function to download all your request list to .xls format.</p>
                        <p>You can access to <b>My Ongoing Request</b> menu, click on <img src="<?php echo URL::to("assets/image")."/"?>xls_icon.png" height="20" /> to export the list into Microsoft Excel format. You can then print the Microsoft Excel format.</p>
                    </font>
                </div>
            </div>

            <div class="FAQ" onclick="toggle('faq7');">
                <font face="times new roman" size="3"> <i> 7. I left my computer stand by for several minutes and why is the system ask me to relogin?</i></font>
                <div id="faq7" class="ANS" style="display: none;">
                    <font face="Verdana" size="3">
                        <p><br />One-Click Application will maintain your user session for 20 (twenty) minutes.</p><p>This means if you are idle for more than 20 minutes, for security reason we have to ask you to relogin to verify the active user. Please note that this is the standard practice in web security.</p>
                    </font>
                </div>
            </div>
        </div>
@endsection