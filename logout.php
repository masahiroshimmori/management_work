<?php

// セッションの開始
ob_start();
session_start();

// セッションの認証情報を削除
unset($_SESSION['employee']);

// 非ログインTopPageに遷移
header('Location: ./login/login.php');