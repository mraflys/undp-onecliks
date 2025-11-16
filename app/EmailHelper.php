<?php

namespace App;
use DB;
use Session;
use Cache;
use Mail;
use App\GeneralHelper;

class EmailHelper
{
  const DATE_FORMAT =  'Y-m-d'; 
  const DATE_FORMAT_MYSQL = 'Y-m-d H:i:s';

  public static function send_new_request_notification($to, $ticket, $description){
  	$content = "Dear,
      <p>A requester has been requested a service.<br/>
      The request is $ticket - $description.</p>";
     GeneralHelper::send_email($to, "[OneClick] $ticket - New Request - ", $content);
  }

  public static function send_mail_confirmation($to, $person_name, $ticket, $description, $pic, $free = 0, $pic_email = null){
    $free_msg = "";
    if ($free) $free_msg = "It is FREE OF CHARGE.";
    $content = "
      Dear $person_name,<br/>
      Thank you, Your Request has been reviewed.<br/>
      Your ticket number, $ticket, has been accepted and assign to $pic.<br/>
      $free_msg
      To monitor your request, please visit OneClick site and click Menu 'My Request' - 'On Going Request'.";
     GeneralHelper::send_email($to, "[OneClick] $ticket - Confirmed ", $content, $pic_email);
  }

  public static function send_mail_reject($to, $person_name, $ticket, $reason){
    $content = "
    Dear $person_name,<br/>
    Thank you, Your Request has been reviewed.<br/>
    Your ticket number, $ticket, is rejected.<br/>
    Reason : $reason.";
    GeneralHelper::send_email($to, "[OneClick] $ticket - Rejected", $content);
  }

  public static function send_mail_return($to, $person_name, $ticket, $reason){
    $content = "
    Dear $person_name,<br/>
    Thank you, Your Request has been reviewed.<br/>
    Your ticket number, $ticket, is returned and need revision.<br/>
    Reason : $reason.";
    GeneralHelper::send_email($to, "[OneClick] $ticket - Returned ", $content);
  }

  public static function send_mail_goback($to, $person_name, $ticket, $reason){
    $content = "
    Dear $person_name,<br/>
    This ticket No: $ticket has been returned back to you for follow up.<br/>
    The reason is : <quote>$reason</quote><br/>.";
    GeneralHelper::send_email($to, "[OneClick] $ticket - has been returned to you", $content);
  }

  public static function send_mail_rework($to, $person_name, $ticket, $reason){
    $content = "
    Dear $person_name,<br/>
    Your Request with Ticket No: $ticket has been reworked.<br/>
    The reason for rework is <quote>$reason</quote><br/>";
    GeneralHelper::send_email($to, "[OneClick] $ticket - has been reworked", $content);
  }
}
