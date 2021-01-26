<?php
// 定数を定義したファイルを読み込み
require_once '../conf/const.php';
// 関数を定義したファイルを読み込み
require_once MODEL_PATH . 'functions.php';
// セッションを開始(ログインチェック)
session_start();
// 空の配列に上書き
$_SESSION = array();
// セッションに保存されているCookieの設定情報を読み込み
$params = session_get_cookie_params();
// sessionに利用しているクッキーの有効期限を過去に設定することで無効化
setcookie(session_name(), '', time() - 42000,
  $params["path"], 
  $params["domain"],
  $params["secure"], 
  $params["httponly"]
);
// セッションIDを無効化
session_destroy();
// ログインページへリダイレクト
redirect_to(LOGIN_URL);

