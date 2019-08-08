<?php

require_once('./common_function.php');

function update_employee_data($update_employee_id) {
    // エラーチェック
    if ('' === $update_employee_id) {
        return array();
    }
    // DBハンドルの取得
    $dbh = get_dbh();
    // SELECT文の作成と発行
    // ------------------------------
    // 準備された文(プリペアドステートメント)の用意
    $sql = 'SELECT * FROM employee_users WHERE employee_id = :employee_id;';
    $pre = $dbh->prepare($sql);
    // 値のバインド
    $pre->bindValue(':employee_id', $update_employee_id, PDO::PARAM_INT);
    // SQLの実行
    $r = $pre->execute();
    if (false === $r) {
        echo 'システムでエラーが起きました';
        exit;
    }
    // データを取得
    $data = $pre->fetchAll(PDO::FETCH_ASSOC);
    //var_dump($data);
    // 最低限程度のエラーチェック
    if (true === empty($data)) {
        return array();
    }
    // else
    $datum = $data[0]; // 「１件しかでてこない」はずなので、あらかじめ「１件分のデータ」を把握しておく
    //var_dump($datum);
    //
    return $datum;
}