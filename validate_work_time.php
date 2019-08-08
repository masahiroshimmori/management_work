<?php
function validate_work_time($work_update_data){
  $error_detail = array();

  if (1 !== preg_match('/(0[0-9]{1}|1{1}[0-9]{1}|2{1}[0-3]{1}):(0[0-9]{1}|[1-5]{1}[0-9]{1}):(0[0-9]{1}|[1-5]{1}[0-9]{1})$/',
  $work_update_data['start_time'])){
    $error_detail["error_start_time"] = true;
  }

  if (1 !== preg_match('/(0[0-9]{1}|1{1}[0-9]{1}|2{1}[0-3]{1}):(0[0-9]{1}|[1-5]{1}[0-9]{1}):(0[0-9]{1}|[1-5]{1}[0-9]{1})$/',
  $work_update_data['end_time'])){
    $error_detail["error_end_time"] = true;
  }
  
  if (1 !== preg_match('/(0[0-9]{1}|1{1}[0-9]{1}|2{1}[0-3]{1}):(0[0-9]{1}|[1-5]{1}[0-9]{1}):(0[0-9]{1}|[1-5]{1}[0-9]{1})$/',
  $work_update_data['break_time'])){
    $error_detail["error_break_time"] = true;
  }
  return $error_detail;
}