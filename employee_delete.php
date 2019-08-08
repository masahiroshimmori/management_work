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

// ユーザ入力情報を保持する配列を準備する
$employee_delete_data = array();

// 「パラメタの一覧」を把握
$params = array('employee_id');
// データを取得する
foreach($params as $p) {
    $employee_delete_data[$p] = (string)@$_POST[$p];
}
// 確認
//var_dump($employee_delete_data);

// CSRFチェック
if (false === is_csrf_token()) {
    // 「CSRFトークンエラー」であることを配列に格納しておく
    $error_detail["error_csrf"] = true;
}

// エラーが出たら入力ページに遷移する
if (false === empty($error_detail)) {
    // エラー情報をセッションに入れて持ちまわる
    $_SESSION['output_buffer'] = $error_detail;

    // 一覧ページに遷移する
    header('Location: ./employee_list.php');
    exit;
}


// DBハンドルの取得
$dbh = get_dbh();

// INSERT文の作成と発行
// ------------------------------
// 準備された文(プリペアドステートメント)の用意
$sql = 'DELETE FROM  employee_users WHERE employee_id=:employee_id;';
$pre = $dbh->prepare($sql);

// 値のバインド
$pre->bindValue(':employee_id', $employee_delete_data['employee_id'], PDO::PARAM_INT);

// SQLの実行
$r = $pre->execute();
if (false != $r) {
    // 「削除成功した」メッセージを出力するためのフラグを持ちまわる
    $_SESSION['output_buffer']['delete_success'] = true;
}

// Listページに遷移する
header('Location: ./employee_list.php');