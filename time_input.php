<?php
require_once('common_function.php');

// セッションの開始
ob_start();
session_start();
session_regenerate_id(true);

if(true === isset($_POST['employee_id'])){
  $employee_id = (int)$_POST['employee_id'];
}

//var_dump($employee_id);

//出勤ボタンが押された場合の処理
if(true === isset($_POST['start_time'])){
  $start_date = date('Y-m-d', time());
  $start_time = date('H:i', time());

  //出勤ボタンが押されているかの確認
  $dbh = get_dbh();
  $sql = 'SELECT count(*) FROM work WHERE work_employee_id = :work_employee_id AND date = :start_date AND form IS NOT NULL;';
  $pre = $dbh->prepare($sql);
  $pre->bindValue(':work_employee_id', $employee_id);
  $pre->bindValue(':start_date', $start_date);
  $r = $pre->execute();

  if(false === $r){
    echo "エラーが発生しました";
    exit();
  }

  $datum_count = $pre->fetch();
  $data_count = $datum_count[0];


    if(0 !== $data_count){
      //エラーフラグを立てる（まだ出勤ボタンが押されていない）
      $_SESSION['msg_flg']['start_already_pushed'] = true;
      header('Location: ./index.php');
    }else{
      //db接続
      $dbh = get_dbh();
      //SQL準備
      $sql = 'INSERT INTO work(work_employee_id, form, date, start_time, created_at) VALUES (:work_employee_id, :form, :date, :start_time, :created_at);';
      $pre = $dbh->prepare($sql);

      //値のバインド
      $pre->bindValue(':work_employee_id', $employee_id, PDO::PARAM_INT);
      $pre->bindValue(':form', '出勤', PDO::PARAM_STR);
      $pre->bindValue(':date', $start_date, PDO::PARAM_STR);
      $pre->bindValue(':start_time', $start_time, PDO::PARAM_STR);
      $pre->bindValue(':created_at', date(DATE_ATOM), PDO::PARAM_STR);
      
      //sql発行
      $r = $pre->execute();
      if(false === $r){
        echo 'データベース更新時にエラーが発生しましたので中止します';
        exit();
      }
      $_SESSION['msg_flg']['start_flg'] = true;
      header('Location: ./index.php');
    }
}


//退勤ボタンが押された場合の処理
if(true === isset($_POST['end_time'])){
  $end_date = date('Y-m-d', time());
  $end_time = date('H:i', time());

  //出勤ボタンが押されているかの確認(formとend_dateに値があるかの確認)
  $dbh = get_dbh();
  $sql = 'SELECT count(*) FROM work WHERE work_employee_id = :work_employee_id AND date = :end_date AND form IS NOT NULL;';
  $pre = $dbh->prepare($sql);
  $pre->bindValue(':work_employee_id', $employee_id);
  $pre->bindValue(':end_date', $end_date);
  $r = $pre->execute();

  if(false === $r){
    echo "エラーが発生しました";
    exit();
  }

  $datum_count = $pre->fetch();
  $data_count = $datum_count[0];

    //取得件数0件の場合（dateに値なし）と1件の場合（dateに値あり）
    if(0 === $data_count){
      //エラーフラグを立てる（まだ出勤ボタンが押されていない）
      $_SESSION['msg_flg']['start_until_pushed'] = true;
      header('Location: ./index.php');
    }else{
      //db接続
      $dbh = get_dbh();
      //SQL準備
      $sql = 'UPDATE work SET end_time=:end_time, updated_at=:updated_at, break_time=:break_time WHERE date=:date AND work_employee_id=:work_employee_id;';
      $pre = $dbh->prepare($sql);

      //値のバインド
      $pre->bindValue(':work_employee_id', $employee_id, PDO::PARAM_INT);      
      $pre->bindValue(':date', $end_date, PDO::PARAM_STR);
      $pre->bindValue(':end_time', $end_time, PDO::PARAM_STR);
      $pre->bindValue(':updated_at', date(DATE_ATOM), PDO::PARAM_STR);
      $pre->bindValue(':break_time', '00:00:00', PDO::PARAM_STR);
      
      //sql発行
      $r = $pre->execute();
      if(false === $r){
        echo 'データベース更新時にエラーが発生しましたので中止します';
        exit();
      }

      //break_timeへ時間自動挿入（6時間以上の場合1時間休憩）
      $dbh = get_dbh();
      $sql = 'SELECT start_time, end_time FROM work WHERE work_employee_id = :work_employee_id AND date = :date AND form IS NOT NULL;';
      $pre = $dbh->prepare($sql);
      $pre->bindValue(':work_employee_id', $employee_id);
      $pre->bindValue(':date', $end_date);
      $r = $pre->execute();

      $breaks_time = $pre->fetchAll(PDO::FETCH_ASSOC);
      //var_dump($breaks_time[0]);
      foreach($breaks_time as $break_time){
        $break_time_dem = strtotime($break_time['end_time']) - strtotime($break_time['start_time']);
        //var_dump($break_time_dem);
        if($break_time_dem >= 28800){
          $break_time = '01:00:00';
        }elseif($break_time_dem >= 21600){
          $break_time = '0:45:00';
        }

        
          $dbh = get_dbh();
          $sql = 'UPDATE work SET break_time=:break_time WHERE work_employee_id = :work_employee_id AND date = :date AND form IS NOT NULL;';
          $pre = $dbh->prepare($sql);
          $pre->bindValue(':work_employee_id', $employee_id, PDO::PARAM_INT);
          $pre->bindValue(':break_time', $break_time, PDO::PARAM_STR);
          $pre->bindValue(':date', $end_date, PDO::PARAM_STR);
          $r = $pre->execute();
          
      }

      $_SESSION['msg_flg']['work_finished'] = true;
      header('Location: ./index.php');
    }
}