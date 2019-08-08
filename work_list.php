<?php
ob_start();
session_start();
session_regenerate_id(true);

require_once('./common_function.php');

//管理職未満はこのページへのリンクを不可とする。
if (1 > $_SESSION['employee']['employee_role']) {
  // TopPage(認証後トップページ)に遷移させる
  header('Location: ./index.php');
  exit;
}
$employee_data = array();
if(true === isset($_SESSION['employee'])){
    $employee_data = $_SESSION['employee'];
}else{
  $login_alert['login_alert'] = true;
  $_SESSION['login_alert'] = $login_alert;
  header('Location: ./login/login.php');
  exit();
}

if(isset($_SESSION['msg_flg']) && true === isset($_SESSION['msg_flg'])){
  $msg_flg = $_SESSION['msg_flg'];
}
unset($_SESSION['msg_flg']);

date_default_timezone_set('Asia/Tokyo');


//前月・次月リンクを押された場合、GETパラメータから年月を取得
if(true === isset($_GET['ym'])){
  $ym = $_GET['ym'];
}else{
  //今月の年月を表示
  $ym = date('Y-m');
}

//タイムスタンプを作成し、フォーマットをチェックする
$timestamp = strtotime($ym . '-01');
if ($timestamp === false) {
    $ym = date('Y-m');
    $timestamp = strtotime($ym . '-01');
}

//前月・次月の年月を取得
//mktime(hour,minute,second,month,day,year)
$prev = date('Y-m', mktime(0,0,0,date('m',$timestamp)-1,1,date('Y', $timestamp)));
$next = date('Y-m', mktime(0,0,0,date('m',$timestamp)+1,1,date('Y', $timestamp)));
$ym_p = $ym. '%';
// var_dump($ym_p);
// var_dump($prev);
// var_dump($next);

//データの抽出
$dbh = get_dbh();
$sql = 'SELECT work.work_id, work.date, employee_users.employee_name, work.start_time, work.end_time, work.break_time FROM employee_users, work WHERE employee_users.employee_id = work.work_employee_id AND date LIKE :date ORDER BY date DESC;';
$pre = $dbh->prepare($sql);
$pre->bindValue(':date', $ym_p);
$r = $pre->execute();

$data = $pre->fetchAll(PDO::FETCH_ASSOC);
//var_dump($data);

//残業時間の計算
function over_work($start_time, $end_time, $break_time){
  $start_time = explode(':', $start_time);
  $end_time = explode(':', $end_time);
  $start_time_h = $start_time[0] * 60;
  $end_time_h = $end_time[0] * 60;
  $start_time = $start_time_h + $start_time[1];
  $end_time = $end_time_h + $end_time[1];
  
  $break_time = explode(':', $break_time);
  $break_time_h = $break_time[0] * 60;
  $break_time = $break_time_h + $break_time[1];
  $over_time = $end_time - $start_time - $break_time - 480;
  if($over_time > 0){
    $over_time = date('H:i', mktime(0,$over_time));
  }else{
    $over_time = '';
  }
  return $over_time;
}

?>
<!doctype html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="js/nowtime.js"></script>

    <title>従業員勤怠一覧画面</title>
  </head>
  <body>
    <div class="jumbotron-fluid bg-info py-2 px-5 mb-5">
      <div class="container">
        <div class="row">
          <div class="col-6">
            <h3 class="text-white"><a href="./index.php" class="text-white">出勤・退勤管理システム</a></h3>            
          </div>
          <div class="col-6">
            <p class="text-white text-right"><a class="text-white" href="./logout.php">ログアウト</a></p>
            <p class="text-white text-right">ようこそ<?php echo $employee_data['employee_name'];?>さん(社員コード:<?php echo $employee_data['employee_code'];?>)</p>            
          </div>
        </div>
      </div>
    </div>

    <?php if(true === isset($msg_flg['start_already_pushed'])): ?>
    <div class="container my-2">
      <div class="alert alert-danger alert-dismissible fade show">
          <p>既に出勤ボタンが押されています。</p>
          <button class="close" data-dismiss="alert">&times;</button>
      </div>
    </div>
    <?php endif ; ?>

    <?php if(true === isset($msg_flg['start_until_pushed'])): ?>
    <div class="container my-2">
      <div class="alert alert-danger alert-dismissible fade show">
          <p>出勤ボタンが押されていません。</p>
          <button class="close" data-dismiss="alert">&times;</button>
      </div>
    </div>
    <?php endif ; ?>

    <?php if(true === isset($msg_flg['start_flg'])): ?>
    <div class="container my-2">
      <div class="alert alert-primary alert-dismissible fade show">
          <p>打刻しました。今日も一日頑張りましょう！</p>
          <button class="close" data-dismiss="alert">&times;</button>
      </div>
    </div>
    <?php endif ; ?>

    <?php if(true === isset($msg_flg['work_finished'])): ?>
    <div class="container my-2">
      <div class="alert alert-primary alert-dismissible fade show">
          <p>お疲れ様でした！</p>
          <button class="close" data-dismiss="alert">&times;</button>
      </div>
    </div>
    <?php endif ; ?>
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="display-4">
        <a href="?ym=<?php echo $prev; ?>">&laquo;</a> <?php echo $ym; ?> <a href="?ym=<?php echo $next; ?>">&raquo;</a>   
      </div>
    </div>
  </div>

    <div class="container">
    <h1>従業員労働時間一覧</h1>
      <table class="table my-5 table-hover">
      <thead>
        <tr><th>日付</th><th>従業員氏名</th><th>出勤時刻</th><th>退勤時刻</th><th>休憩時間</th><th>時間内労働時間</th><th>時間外労働時間</th><th></th></tr>
      </thead>
      <tbody>
        <?php foreach($data as $datum) :?>
        
        <tr>
          <td><?php echo h($datum['date']);?></td>
          <td><?php echo h($datum['employee_name']);?></td>
          <td><?php echo h($datum['start_time']);?></td>
          <td><?php echo h($datum['end_time']);?></td>
          <td>
          <?php if($datum['end_time'] !== NULL) :?>
          <?php echo h($datum['break_time']);?>
          <?php endif ;?>
          </td>
          <td>
          <?php if($datum['end_time'] !== NULL) :?>
          <?php $work_time = date_diff(new DateTime($datum['start_time']), new DateTime($datum['end_time']))->format('%H:%I');?>
          <?php if('08:00' <= $work_time): ?>
          <?php echo '08:00' ; ?>
          <?php else :?>
          <?php echo h($work_time);?>
          <? endif ;?>
          <?php endif ;?>
          </td>
          <td>
          <?php if($datum['end_time'] !== NULL) :?>
          <?php $over_time = over_work($datum['start_time'], $datum['end_time'], $datum['break_time']);?>
          <?php echo $over_time ; ?>
          <?php endif ;?>
          </td>
          <td>
          <a class="btn btn-light" href="./work_update.php?work_id=<?php echo h($datum['work_id']);?>">修正</a>
          </td>
        </tr>
        <?php endforeach ; ?>
      </tbody>
      </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
  </body>
</html>