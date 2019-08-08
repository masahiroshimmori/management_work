<?php

function h($s){
  return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

// DB用関数
// ----------------------------
function get_dbh() {
  // データの設定
  $user = 'root';
  $pass = '';
  $dsn = 'mysql:dbname=management_work;host=localhost;charset=utf8mb4';
  // 接続オプションの設定
  $opt = array (
      PDO::ATTR_EMULATE_PREPARES => false,
  );
  // 「複文禁止」
  if (defined('PDO::MYSQL_ATTR_MULTI_STATEMENTS')) {
      $opt[PDO::MYSQL_ATTR_MULTI_STATEMENTS] = false;
  }
  // 接続
  try {
      $dbh = new PDO($dsn, $user, $pass, $opt);
  } catch (PDOException $e) {
      // 
      echo 'データベース接続時にエラーが起きました';
      exit;
  }
  //
  return $dbh;
}

// CSRF用共通関数
// ----------------------------
// tokenの作成とセッションへの設定
function create_csrf_token() {
  return _create_csrf_token('front');
}

// 処理本体
function _create_csrf_token($type) {
  // CSRF用のトークンの作成と設定
  $csrf_token = '';
  try {
      // XXX random_bytesはPHP7以降の関数だが、PHP5.2以降で使えるユーザランド実装( https://github.com/paragonie/random_compat )が存在する
      if(function_exists('random_bytes')) {
          $csrf_token = hash('sha512', random_bytes(128));
      } else if (is_readable('/dev/urandom')) {
          $csrf_token = hash('sha512', file_get_contents('/dev/urandom', false, NULL, 0, 128), false);
      } else if(function_exists('openssl_random_pseudo_bytes')) {
          $csrf_token = hash('sha512', openssl_random_pseudo_bytes(128));
      }
  } catch (Exception $e) {
      ; // XXX 後でまとめてエラーチェックするので一端ここでは未処理
  }
  if ('' === $csrf_token) {
      echo 'CSRFトークンが作成できないので終了します';
      exit; // XXX
  }


  // セッションに格納
  $_SESSION[$type]['csrf_token'][$csrf_token] = time();

  // CSRFトークンは5個まで(で後で追加するので、ここでは4個以下に)
  while (5 <= count(@$_SESSION[$type]['csrf_token'])) {
      array_shift($_SESSION[$type]['csrf_token']);
  }

  //
  return $csrf_token;
}

// tokenのチェック
function is_csrf_token() {
  return _is_csrf_token('front');
}

// 処理本体
function _is_csrf_token($type) {

  // CSRFトークンを把握
  $post_csrf_token = (string)@$_POST['csrf_token'];

  // セッションの中に「送られてきたトークン」が存在しなければ、false
  if (false === isset($_SESSION[$type]['csrf_token'][$post_csrf_token])) {
      return false;
  }

  // 寿命を把握して
  $ttl = $_SESSION[$type]['csrf_token'][$post_csrf_token];
  // 先にトークンは削除(使い捨てなので)
  unset($_SESSION[$type]['csrf_token'][$post_csrf_token]);
  // 寿命チェック(5分以内)
  if (time() >=  $ttl + 300) {
      return false;
  }
  // すべてのチェックでOKだったのでチェック成功
  return true;
}
