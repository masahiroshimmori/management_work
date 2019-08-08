<?php

// セッションの開始
ob_start();
session_start();
session_regenerate_id(true);

// 共通関数のinclude
require_once('./common_function.php');
require_once('./validate_work_time.php');

// 日付関数(date)を使うのでタイムゾーンの設定
date_default_timezone_set('Asia/Tokyo');

// 労働時間編集情報を保持する配列を準備する
$work_update_data = array();

// エラー情報を保持する配列を準備する
$error_detail = array();

// 「パラメタの一覧」を把握
$params = array('start_time', 'end_time', 'break_time');
// データを取得する
foreach($params as $p) {
    $work_update_data[$p] = (string)@$_POST[$p];
}

//var_dump($work_update_data);

//帰り用にemployee_idはベット取得
$work_id = (int)@$_POST['work_id'];
// 確認
//var_dump($work_id);

// 基本のエラーチェック(時間のvalidation)
$error_detail = validate_work_time($work_update_data);

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
    $_SESSION['output_buffer'] += $work_update_data;
    // 編集ページに遷移する
    header('Location: ./work_update.php?work_id=' . rawurlencode($work_id));    
    exit;
}

// DBハンドルの取得
$dbh = get_dbh();

// INSERT文の作成と発行
// ------------------------------
// 準備された文(プリペアドステートメント)の用意
$sql = 'UPDATE work SET start_time=:start_time, end_time=:end_time, break_time=:break_time WHERE work_id = :work_id;';
$pre = $dbh->prepare($sql);

// 値のバインド
$pre->bindValue(':work_id', $work_id, PDO::PARAM_INT);
$pre->bindValue(':start_time', $work_update_data['start_time'], PDO::PARAM_STR);
$pre->bindValue(':end_time', $work_update_data['end_time'], PDO::PARAM_STR);
$pre->bindValue(':break_time', $work_update_data['break_time'], PDO::PARAM_STR);

// SQLの実行
$r = $pre->execute();

if (false === $r) {
    echo 'データ更新時にエラーが発生しました。';
    exit;
    }

//登録したメッセージを出力するためのフラグを持ち回る
$_SESSION['output_buffer']['work_update_success'] = true;
header('Location: ./work_list.php');