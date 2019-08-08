<?php

ob_start();
session_start();
session_regenerate_id(true);

require_once('./common_function.php');
require_once('./update_employee_data.php');

//管理職未満はこのページへのリンクを不可とする。
if (1 > $_SESSION['employee']['employee_role']) {
  // TopPage(認証後トップページ)に遷移させる
  header('Location: ./index.php');
  exit;
}
//ログインしてなかったらログインページへ飛ばす
$employee_data = array();
if(true === isset($_SESSION['employee'])){
    $employee_data = $_SESSION['employee'];
}else{
    $login_alert['login_alert'] = true;
    $_SESSION['login_alert'] = $login_alert;
    header('Location: ./login/login.php');
    exit();
}
// パラメタを受け取る
$update_employee_id = (string)@$_GET['employee_id'];
// 確認
//var_dump(update_employee_id);

$datum = update_employee_data($update_employee_id);

if (true === empty($datum)) {
    header('Location: ./employee_list.php');
    exit;
}

// $_SESSION['output_buffer']にデータがある場合は、情報を上書きする
// 配列の「加算演算子による結合」では先に出したほうが優先されるので、セッション情報を先に書く
if (true === isset($_SESSION['output_buffer'])) {
    $datum = $_SESSION['output_buffer'] + $datum;
}
//var_dump($datum);

// (二重に出力しないように)セッション内の「出力用情報」を削除する
unset($_SESSION['output_buffer']);

// CSRFトークンの発行
$csrf_token = create_csrf_token();

?>
<!doctype html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="js/nowtime.js"></script>
    <title>従業員情報修正</title>
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
<div class="container">
<div class="row">

  <form action="employee_update_fin.php" method="post" class="mx-auto">
      <input type="hidden" name="employee_id" value="<?php echo h($datum['employee_id']);?>">
      <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token);?>">

      <?php if( (isset($datum['error_overlap_employee_code'])) && (true === $datum['error_overlap_employee_code']) ) :?>
      <span class="text-danger">その従業員コードは既に登録済みです。</span><br>
      <?php endif ;?>

      <?php if(isset($datum['error_csrf']) && true === $datum['error_csrf']): ?>
          <span class="text-danger">CSRFエラーです。5分以内に遷移しててください。<br></span>
      <?php endif; ?>

      <?php if(isset($datum['error_must_employee_code']) && true === $datum['error_must_employee_code']): ?>
          <span class="text-danger">従業員コードが未入力です。<br></span>
      <?php endif; ?>

      <?php if(isset($datum['error_invalid_employee_code']) && true === $datum['error_invalid_employee_code']): ?>
          <span class="text-danger">従業員コードは半角英数で入力してください。<br></span>
      <?php endif; ?>

        <label>従業員コード：</label>
        <input type="text" name="employee_code" class="form-control" placeholder="従業員コード" value="<?php echo h(@$datum['employee_code']);?>"><br>

      <?php if(isset($datum['error_must_employee_name']) && true === $datum['error_must_employee_name']): ?>
          <span class="text-danger">従業員氏名が未入力です。<br></span>
      <?php endif; ?>
    
        <label>従業員氏名：</label>
        <input type="text" name="employee_name" class="form-control" placeholder="従業員氏名" value="<?php echo h(@$datum['employee_name']);?>"><br>

      <?php if(isset($datum['error_must_employee_section']) && true === $datum['error_must_employee_section']): ?>
          <span class="text-danger">所属部門が未入力です。<br></span>
      <?php endif; ?>

        <div class="form-group">
          <p>所属部門：</p>
          <div class="form-check form-check-inline mr-4">
            <input type="radio" name="employee_section" class="form-check-input" value="0"<?php if($datum['employee_section'] === 0) echo ' checked';?>>
            <label class="form-check-label">営業</label>
          </div>
          <div class="form-check form-check-inline mr-4">
            <input type="radio" name="employee_section" class="form-check-input" value="1"<?php if($datum['employee_section'] === 1) echo ' checked';?>>
            <label class="form-check-label">総務</label>
          </div>
          <div class="form-check form-check-inline mr-4">
            <input type="radio" name="employee_section" class="form-check-input" value="2"<?php if($datum['employee_section'] === 2) echo ' checked';?>>
            <label class="form-check-label">経理</label>
          </div>
          <div class="form-check form-check-inline mr-4">
            <input type="radio" name="employee_section" class="form-check-input" value="3"<?php if($datum['employee_section'] === 3) echo ' checked';?>>
            <label class="form-check-label">その他</label>
          </div>
        </div>

        <?php if(isset($datum['error_must_employee_role']) && true === $datum['error_must_employee_role']): ?>
          <span class="text-danger">役職区分が未入力です。<br></span>
      <?php endif; ?>

      <div class="form-group">
          <p>役職：</p>
          <div class="form-check form-check-inline mr-4">
            <input type="radio" name="employee_role" class="form-check-input" value="0"<?php if($datum['employee_role'] === 0) echo ' checked';?>>
            <label class="form-check-label">一般</label>
          </div>
          <div class="form-check form-check-inline mr-4">
            <input type="radio" name="employee_role" class="form-check-input" value="1"<?php if($datum['employee_role'] === 1) echo ' checked';?>>
            <label class="form-check-label">管理職</label>
          </div>
          <div class="form-check form-check-inline mr-4">
            <input type="radio" name="employee_role" class="form-check-input" value="2"<?php if($datum['employee_role'] === 2) echo ' checked';?>>
            <label class="form-check-label">役員</label>
          </div>
        </div>
        <div class="container">
          <div class="row">
            <div class="col-6">
            <a class="btn btn-lg btn-light btn-block" href ="./employee_list.php">戻る</a>
            </div>
            <div class="col-6">
              <button type="submit" class="btn btn-lg btn-primary btn-block">修正</button>
            </div>
          </div>
        </div>
  </form>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
  </body>
</html>