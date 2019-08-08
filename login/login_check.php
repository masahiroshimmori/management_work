<?php
ob_start();
session_start();

require_once('login_lock.php');
require_once('../common_function.php');


// 日付関数(date)を(後で)使うのでタイムゾーンの設定
date_default_timezone_set('Asia/Tokyo');

// 従業員入力情報を保持する配列を準備する
$employee_input_data = array();
// エラー情報を保持する配列を準備する
$error_detail = array();

// 「パラメタの一覧」を把握
$params = array('employee_code', 'pass');
// データを取得する ＋ 必須入力のvalidate
foreach($params as $p) {
    $employee_input_data[$p] = (string)@$_POST[$p];
    if ('' === $employee_input_data[$p]) {
        $error_detail['error_must_' . $p] = true;
    }
}
// 確認
//var_dump($employee_input_data);

// エラーが出たら入力ページに遷移する
if (false === empty($error_detail)) {
    // エラー情報をセッションに入れて持ちまわる
    $_SESSION['output_buffer'] = $error_detail;

    // 入力ページに遷移する
    header('Location: ./login.php');
    exit;
}

// 比較用のパスワード情報取得 ＆ パスワード比較
// DBハンドルの取得
$dbh = get_dbh();

// ------------------------------
// 準備された文(プリペアドステートメント)の用意
$sql = 'SELECT * FROM employee_users WHERE employee_code=:employee_code;';
$pre = $dbh->prepare($sql);
// 値のバインド
$pre->bindValue(':employee_code', $employee_input_data['employee_code'], PDO::PARAM_STR);
// SQLの実行
$r = $pre->execute();
if (false === $r) {

    echo 'SQLでエラーが起きました';
    exit;
}
// SELECTした内容の取得

$datum = $pre->fetch(PDO::FETCH_ASSOC);
//var_dump($datum);

// ログイン処理(共通化)
$login_flg = login($employee_input_data['pass'], $datum, 'employee_login_lock');

//var_dump($login_flg);

// 最終的に「ログイン情報に不備がある」場合は、エラーとして突き返す

// エラーが出たら入力ページに遷移する
if (false === $login_flg) {
    // エラー情報をセッションに入れて持ちまわる
    $_SESSION['output_buffer']['error_invalid_login'] = true;
    // 従業員コードは保持する
    $_SESSION['output_buffer']['employee_code'] = $employee_input_data['employee_code'];

    // 入力ページに遷移する
    header('Location: ./login.php');
    exit;
}

// ここまで来たら「適切な情報でログインができている」

// セッションIDを張り替える：
session_regenerate_id(true);
// 「ログインできている」という情報をセッション内に格納する
$_SESSION['employee']['employee_id'] = $datum['employee_id'];
$_SESSION['employee']['employee_name'] = $datum['employee_name'];
$_SESSION['employee']['employee_code'] = $datum['employee_code'];
$_SESSION['employee']['employee_role'] = $datum['employee_role'];

// TopPage(認証後トップページ)に遷移させる
header('Location: ../index.php');