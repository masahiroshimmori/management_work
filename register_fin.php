<?php
ob_start();
session_start();
session_regenerate_id(true);

// validate_employee_password用
require_once('employee_data.php');
require_once('common_function.php');


// 日付関数(date)を(後で)使うのでタイムゾーンの設定
date_default_timezone_set('Asia/Tokyo');

// ユーザ入力情報を保持する配列を準備する
$employee_input_data = array();

// 「パラメタの一覧」を把握
$params = array('employee_id', 'employee_code', 'employee_name', 'employee_password', 'employee_section', 'employee_role');
// データを取得する
foreach($params as $p) {
    $employee_input_data[$p] = (string)@$_POST[$p];
}

// 確認
//var_dump($employee_input_data);

// ユーザ入力のvalidate
// --------------------------------------
// パスワードチェック（長さチェック12文字以下）
$error_detail = validate_employee_password($employee_input_data['employee_password']);

// 従業員コードのチェック（半角英数）
$error_detail = validate_employee($employee_input_data['employee_code']);
$error_detail = validate_employee($employee_input_data['employee_password']);

// 必須入力のチェック
foreach($params as $p) {
    // 空文字(未入力)なら
    if ('' === $employee_input_data[$p]) {
        // 「必須情報の未入力エラー」であることを配列に格納しておく
        $error_detail["error_must_{$p}"] = true;
    }
}
// roleは、おおざっぱに値をそろえておく(0-2の間のみを許容、なので)
$employee_input_data['employee_role'] = abs($employee_input_data['employee_role']) % 3;
// sectionは、おおざっぱに値をそろえておく(0-3の間のみを許容、なので)
$employee_input_data['employee_section'] = abs($employee_input_data['employee_section']) % 4;

// エラーが出たら入力ページに遷移する
if (false === empty($error_detail)) {
    // エラー情報をセッションに入れて持ちまわる
    $_SESSION['output_buffer'] = $error_detail;

    // 入力値も持ちまわる
    $_SESSION['output_buffer'] += $employee_input_data;

    // 入力ページに遷移する
    header('Location: ./register.php');
    exit;
}


// DBハンドルの取得
$dbh = get_dbh();

// INSERT文の作成と発行
// ------------------------------
// 準備された文(プリペアドステートメント)の用意
$sql = 'INSERT INTO employee_users(employee_code, employee_name, employee_password, employee_section, employee_role, created_at, updated_at) VALUES(:employee_code, :employee_name, :employee_password, :employee_section, :employee_role, :created_at, :updated_at);';
$pre = $dbh->prepare($sql);

// 値のバインド
$pre->bindValue(':employee_code', $employee_input_data['employee_code'], PDO::PARAM_STR);
$pre->bindValue(':employee_name', $employee_input_data['employee_name'], PDO::PARAM_STR);
// パスワードは「password_hash関数」
$pre->bindValue(':employee_password', employee_password_hash($employee_input_data['employee_password']), PDO::PARAM_STR);
$pre->bindValue(':employee_section', (int)$employee_input_data['employee_section'], PDO::PARAM_INT);
$pre->bindValue(':employee_role', (int)$employee_input_data['employee_role'], PDO::PARAM_INT);
$pre->bindValue(':created_at', date(DATE_ATOM), PDO::PARAM_STR);
$pre->bindValue(':updated_at', date(DATE_ATOM), PDO::PARAM_STR);

// SQLの実行
$r = $pre->execute();
if (false === $r) {
    // 「Duplicate entry 'employee_id' for key 'PRIMARY'」なら、入力画面に突き返す：普通に起きうるエラーなので
    $e = $pre->errorInfo();
//var_dump($e);
    if (0 === strncmp($e[2], 'Duplicate entry', strlen('Duplicate entry'))) {
        // エラー情報をセッションに入れて持ちまわる
        $_SESSION['output_buffer']['error_overlap_employee_code'] = true;
        // 入力値も持ちまわる
        $_SESSION['output_buffer'] += $employee_input_data;
        // 入力ページに遷移する
        header('Location: ./register.php');
        exit;
    }
    // else

    echo 'システムでエラーが起きました';
    exit;
}

// 「登録した」メッセージを出力するためのフラグを持ちまわる
$_SESSION['output_buffer']['register_success'] = true;

// Listページに遷移する
header('Location: ./employee_list.php');
