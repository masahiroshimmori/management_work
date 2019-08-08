<?php
ob_start();
session_start();
require_once('../common_function.php');

// セッションに入っている情報を確認する
//var_dump($_SESSION);

$login_data = array();
if(true === isset($_SESSION['output_buffer'])){
    $login_data = $_SESSION['output_buffer'];
}

if(true === isset($_SESSION['login_alert'])){
    $login_alert = $_SESSION['login_alert'];
}

//ログイン状態の場合はindexページへ飛ばす
if(true === isset($_SESSION['employee']['employee_id'])){
    header('Location: ../index.php');
}

//var_dump($login_data);

unset($_SESSION['output_buffer']);
unset($_SESSION['login_alert']);

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <title>Document</title>
</head>
<body>
<div class="jumbotron-fluid bg-info py-2 px-5 mb-5">
      <div class="container">
        <div class="row">
          <div class="col-6">
            <h3 class="text-white">出勤・退勤管理システム</h3>            
          </div>
          <div class="col-6">
            <p class="text-white text-right"></p>            
          </div>
        </div>
      </div>
    </div>
<div class="container">
    <div class="row">

    <form action="login_check.php" method="post" class="mx-auto">

        <?php if(isset($login_alert['login_alert']) && true === $login_alert['login_alert']): ?>
        <span class="text-danger">ログインが必要です。<br></span>
        <?php endif; ?>

        <?php if(isset($login_data['error_invalid_login']) && true === $login_data['error_invalid_login']): ?>
            <span class="text-danger">社員コードまたはパスワードに誤りがあります。<br></span>
        <?php endif; ?>

        <?php if(isset($login_data['error_must_employee_code']) && true === $login_data['error_must_employee_code']): ?>
            <span class="text-danger">社員コードが未入力です。<br></span>
        <?php endif; ?>
                <label>社員コード：</label>
                <input type="text" name="employee_code" class="form-control" placeholder="社員コード" value="<?php echo h(@$login_data['employee_code']); ?>"><br>
                
        <?php if(isset($login_data['error_must_pass']) && true === $login_data['error_must_pass']): ?>
            <span class="text-danger">パスワードが未入力です。<br></span>
        <?php endif; ?>
                <label>パスワード：</label>
                <input type="password" name="pass" class="form-control" placeholder="パスワード" value=""><br>   
                <br>
                <button type="submit" class="btn btn-lg btn-primary btn-block">ログイン</button>
    </form>
    </div><!--row-->
</div><!--container-->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</body>
</html>