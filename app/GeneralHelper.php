<?php

namespace App;
use DB;
use Session;
use Cache;
use Mail;

class GeneralHelper
{
  const DATE_FORMAT =  'Y-m-d'; 
  const DATE_FORMAT_MYSQL = 'Y-m-d H:i:s';

  public static function check_access($detail){
    if(session('user_role_id') == 3){
      $is_valid = true;
    }else{
      $id_user = $detail->id_user_buyer;
      $id_agency = $detail->id_agency_unit_buyer;
      $id_agency_service = $detail->id_agency_unit_service;
      $sess_user_id = session('user_id');
      $sess_user_agency_id = session('user_agency_unit_id');
      $is_valid = (
        ($id_user == $sess_user_id) ||
        ($id_agency == $sess_user_agency_id) ||
        ($id_agency_service == $sess_user_agency_id)
      );
  
      if (!$is_valid){
        $user_id_pics = $detail->service_workflows()->pluck('id_user_pic_primary', 'id_user_pic_alternate')->toArray();
        $is_valid = in_array($sess_user_id, array_keys($user_id_pics)) || in_array($sess_user_id, $user_id_pics);
      }
    }
    return $is_valid;
  }
  public static function app_configs(){
    $res = DB::table('app_configs')->where('id', 1)->first();
    return $res;
    if (Cache::get('app_configs') == null){
      $res = DB::table('app_configs')->where('id', 1)->first();
      Cache::put('app_configs', $res, 1800);
      return $res;
    }else{
      return Cache::get('app_configs');
    }
  }

  public static function add_log($log = array()) {
    $log['created_at']   = date('Y-m-d H:i:s');
    $log['ipaddress']    = $_SERVER['REMOTE_ADDR'];
    $log['useragent']    = $_SERVER['HTTP_USER_AGENT'];
    $log['url']          = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    DB::table('app_logs')->insert($log);    
  }

  public static function payment_methods(){
    return [
      'atlas' => 'Quantum Charge of Account ( SELECT THIS FOR Quantum AGENCY i.e. UNDP, UNFPA, UN WOMEN,  UNDP AND UNU )',
      'non_atlas' => 'FINANCIAL AUTHORIZATION  ( SELECT THIS FOR NON Quantum AGENCY )',
      'transfer_cash' => 'OTHERS  : TRANSFER / CASH'
    ];
  }

  public static function workday_add($start_date, $next, $holiday = null)
  {
      $interval = new \DateInterval("P1D");
      $date = new \DateTime($start_date);

      for ($i = $next; $i > 0; $i--) {
          $date->add($interval);
          $weekday = $date->format('w');
          // cek for sunday or saturday
          if ($weekday == 0 || $weekday == 6) {
              $nextday = true;
              $i++; // nextday
              continue;
          }
          // cek for holiday
          if (empty($holiday))
              continue; // no holiday
          foreach ($holiday as $day) {
              if ($date->format(DATE_FORMAT) == $day) {
                  $i++;
                  continue;
              }
          }
      }
      return $date->format(self::DATE_FORMAT_MYSQL);
  }

  
  // delay
  public static function workday_delay($cur_date, $end_date, $holiday = null)
  {
      $delay = 0;
      $format = "Ymd";
      $cur_date = new \DateTime($cur_date);
      $end_date = new \DateTime($end_date);


      for ($i = 0; $i == 0; ) {
          $cur_date_format = $cur_date->format($format);
          $end_date_format = $end_date->format($format);
          //echo $cur_date_format."-".$end_date_format."<br/>";
          if ($cur_date_format <= $end_date_format) break;
          $tmp = self::workday_add($end_date->format(self::DATE_FORMAT_MYSQL), 1, $holiday);
          $end_date = new \DateTime($tmp);
          $delay++;
      }

      return $delay;
  }

  public static function next_day($start_date, $n, $format = 'Y-m-d H:i:s') {
    $interval = new \DateInterval("P".$n."D");
    $cur_date = new \DateTime($start_date);
    $cur_date->add($interval);

    return $cur_date->format($format);  
  }

  public static function get_number_workday($date1, $date2, $reverse = "false") {
    $result =  DB::select("SELECT fn_get_number_workday('$date1', '$date2', $reverse) as result");
    return (count($result) > 0) ? $result[0]->result : null;
  }

  public static function send_email($to = null, $subject = null, $content = "", $cc = null, $add_log = true){
    try {
      // Mail::raw($content, function ($message) use ($to, $subject, $cc) {
      //     $message->to($to)->subject($subject); 
      // });
      $data['to'] = $to;
      $data['subject'] = $subject;
      $data['content'] = $content;

      Mail::send([], [], function($message) use ($data) {
          $message->from(env('MAIL_FROM_ADDRESS'));
          $message->to($data['to']);
          $message->subject($data['subject']);
          $message->setBody($data['content'], 'text/html');
      });
    }catch(\Exception $e){
      if (!$add_log) return $e->getMessage();
      self::add_log([
        'type' => 'error',
        'description' => 'Email :'.$e->getMessage(), 
        'id_user' => null]);
    }
  }

  public static function dt_loading_component($text = 'Please Wait ... '){
    return "<div style='width: 100%; background: #f8faf7; padding: 5px'><i class='fa fa-2x fa-spinner fa-spin'></i> &nbsp; $text</div>";
  }

  public static function message_dismissable($type = 'success', $text = 'Sucess'){
    return "<div class='alert alert-".$type." alert-dismissible'><a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a><h6>".$text."</h6></div>";
  }

  public static function basic_loading_component(){
    return "<div id='basicLoadingComponent'><i class='fa fa-2x fa-spinner fa-spin'></i> &nbsp; Processing ...</div>";
  }

  public static function push_file($path_name, $file){
    try{
      $path_name = date('Y').'/'.date('m').'/'.date('d').'/'.$path_name;
      $ext  = $file->getClientOriginalExtension();
      $originFileName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME), '-');
      $filename = date('YmdHis').'-'.rand(100,999).'-'.substr(md5($originFileName), 0, 8).'.'.$ext;
      $filePath = $path_name.'/'.$filename;
      Storage::put($filePath, file_get_contents($file));
      return $filePath;
    }catch(\Exception $e){
      throw new \Exception("push_file: ".$e->getMessage());
    }
  }
}
