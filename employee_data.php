<?php

// 共通関数のinclude
require_once('common_function.php');

// 何かしら使える可能性があるのでパスワードハッシュを共通化しておく

function employee_password_hash($pass) {
    return password_hash($pass, PASSWORD_DEFAULT);
}

/**
 * 与えられた配列をvalidateする:パスワード情報のみ
 *
 * validateがすべてOKなら空配列、NGな項目がある場合はerror_detailに値が入った配列を返す
 *
 */
function validate_employee_password($datum) {
    // エラー情報の詳細を入れるための配列を用意する
    $error_detail = array();

    // パスワードの長さチェック
    // 72文字を超える場合はエラー
    if (72 < strlen($datum['employee_password'])) {
        $error_detail['error_toolong_password'] = true;
    }
    return $error_detail;
}


//バリデーション（半角英数）
function validate_employee($datum) {
    if (1 !== preg_match('/^[a-zA-Z0-9]+$/', $datum['employee_code'])) {
        $error_detail["error_invalid_employee_code"] = true;
    }

    if (1 !== preg_match('/^[a-zA-Z0-9]+$/', $datum['employee_password'])) {
        $error_detail["error_invalid_employee_password"] = true;
    }
    return $error_detail;
}

//バリデーション（半角英数）
function validate_employee_update($datum) {
    $error_detail = array();
    if (1 !== preg_match('/^[a-zA-Z0-9]+$/', $datum['employee_code'])) {
        $error_detail["error_invalid_employee_code"] = true;
    }
    return $error_detail;

}
