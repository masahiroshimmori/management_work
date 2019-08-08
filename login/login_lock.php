<?php

function login($input_pass, $datum, $lock_table) {
    // 判定用フラグ
    $login_flg = false;
    // DBハンドルの取得
    $dbh = get_dbh();

    // employee_idが存在していたら、作業を続行する
    if (false === empty($datum)) {
        // ロックテーブルを読み込んで情報を把握する
        $sql = 'SELECT * FROM ' . $lock_table . ' WHERE employee_id=:employee_id;';
        $pre = $dbh->prepare($sql);
        // 値のバインド
        $pre->bindValue(':employee_id', $datum['employee_id'], PDO::PARAM_STR);
        // SQLの実行
        $r = $pre->execute(); // XXX
        // SELECTした内容の取得
        $lock_datum = $pre->fetch(PDO::FETCH_ASSOC);
        // とれてなければデフォルトの情報を入れる
        if (false === $lock_datum) {
            //
            $lock_datum['employee_id'] = $datum['employee_id'];
            $lock_datum['error_count'] = 0;
            $lock_datum['lock_time'] = '0000-00-00 00:00:00';
        }

        // 現在ロック中なら、時刻を確認
        if ('0000-00-00 00:00:00' !== $lock_datum['lock_time']) {
            // ロック時間が「現在以降」なら、ロックを一端外す
            if (time() > strtotime($lock_datum['lock_time'])) {
                $lock_datum['lock_time'] = '0000-00-00 00:00:00';
                $lock_datum['error_count'] = 0;
            }
        }

        // 最終的に「ロックされていなければ」以下の処理をする
        if ('0000-00-00 00:00:00' === $lock_datum['lock_time']) {
            // パスワードを比較して、その結果を代入する
            if (true === password_verify($input_pass, $datum['employee_password'])) {
                // countのリセット
                $lock_datum['error_count'] = 0;
                // ログインフラグを立てる
                $login_flg = true;
            } else {
                // countのインクリ
                ++ $lock_datum['error_count'];
                // 一定回数(一端、５回)連続でエラーなら、ロックを入れる(一時間)
                if (5 <= $lock_datum['error_count']) {
                    $lock_datum['lock_time'] = date('Y-m-d H:i:s', time() + 3600);
                    //ここにパスワードの入力に5回失敗したと書くといいが、今回はセキュリティーの面であえてエラーを出さない。
                }
            }
        }

        // ロックテーブルに情報を入れる
        // 
        $sql = 'REPLACE INTO ' . $lock_table . '(employee_id, error_count, lock_time) VALUES(:employee_id, :error_count, :lock_time);';
        $pre = $dbh->prepare($sql);
        // 値のバインド
        $pre->bindValue(':employee_id', $lock_datum['employee_id'], PDO::PARAM_STR);
        $pre->bindValue(':error_count', $lock_datum['error_count'], PDO::PARAM_INT);
        $pre->bindValue(':lock_time', $lock_datum['lock_time'], PDO::PARAM_STR);
        // SQLの実行
        $r = $pre->execute(); //
    }

    //
    return $login_flg;
}