<?php

// セッションの開始
ob_start();
session_start();
session_regenerate_id(true);

// 共通関数のinclude
require_once('./common_function.php');
require_once('./employee_data.php');

// 日付関数(date)を使うのでタイムゾーンの設定
date_default_timezone_set('Asia/Tokyo');

// 従業員編集情報を保持する配列を準備する
$employee_edit_data = array();

// エラー情報を保持する配列を準備する
$error_detail = array();

// 「パラメタの一覧」を把握
$params = array('employee_code', 'employee_name', 'employee_section', 'employee_role');
// データを取得する
foreach($params as $p) {
    $employee_edit_data[$p] = (string)@$_POST[$p];
// 「必須情報の未入力エラー」であることを配列に格納しておく
    if ('' === $employee_edit_data[$p]) {
        $error_detail['error_must_' . $p] = true;
    }
}

//帰り用にemployee_idはベット取得
$employee_id = (int)@$_POST['employee_id'];
// 確認
//var_dump($employee_edit_data);
//var_dump($$employee_id);

// 基本のエラーチェック(従業員コードは半角英数字であること)
$error_detail += validate_employee_update($employee_edit_data);


// CSRFチェック
if (false === is_csrf_token()) {
    // 「CSRFトークンエラー」であることを配列に格納しておく
    $error_detail["error_csrf"] = true;
}

// 確認
//var_dump($error_detail);

// エラーが出たら入力ページに遷移する
if (false === empty($error_detail)) {
    // エラー情報をセッションに入れて持ちまわる
    $_SESSION['output_buffer'] = $error_detail;

    // 入力値をセッションに入れて持ちまわる
    // XXX 「keyが重複しない」はずなので、加算演算子でOK
    $_SESSION['output_buffer'] += $employee_edit_data;
    // 編集ページに遷移する
    header('Location: ./employee_update.php?employee_id=' . rawurlencode($employee_id));    
    exit;
}

// DBハンドルの取得
$dbh = get_dbh();

// INSERT文の作成と発行
// ------------------------------
// 準備された文(プリペアドステートメント)の用意
$sql = 'UPDATE employee_users SET employee_code=:employee_code, employee_name=:employee_name, employee_section=:employee_section, employee_role=:employee_role, updated_at=:updated_at WHERE employee_id = :employee_id;';
$pre = $dbh->prepare($sql);

// 値のバインド
$pre->bindValue(':employee_id', $employee_id, PDO::PARAM_INT);
$pre->bindValue(':employee_code', $employee_edit_data['employee_code'], PDO::PARAM_STR);
$pre->bindValue(':employee_name', $employee_edit_data['employee_name'], PDO::PARAM_STR);
$pre->bindValue(':employee_section', (int)$employee_edit_data['employee_section'], PDO::PARAM_INT);
$pre->bindValue(':employee_role', (int)$employee_edit_data['employee_role'], PDO::PARAM_INT);
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
        $_SESSION['output_buffer'] += $employee_edit_data;
        // 入力ページに遷移する
        header('Location: ./employee_update.php?employee_id=' . rawurlencode($employee_id));
        exit;
    }
    // else

    echo 'システムでエラーが起きました';
    exit;
}

//登録したメッセージを出力するためのフラグを持ち回る
$_SESSION['output_buffer']['employee_update_success'] = true;
header('Location: ./employee_list.php');