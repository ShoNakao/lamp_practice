<?php
// 定数を定義したファイルを読み込み
require_once '../conf/const.php';
// 関数を定義したファイルを読み込み
require_once MODEL_PATH . 'functions.php';
// ユーザ情報に関するファイルを読み込み
require_once MODEL_PATH . 'user.php';
// 商品情報に関するファイルを読み込み
require_once MODEL_PATH . 'item.php';
// セッションを開始(ログインチェック)
session_start();
// ログインされていない場合(is_loginedからfalseが返ってきた場合)
if(is_logined() === false){
  // ログインページへリダイレクト
  redirect_to(LOGIN_URL);
}
// dbに接続
$db = get_db_connect();
// user_idに紐付いたユーザ情報(user_id,name,password,type)を取得
$user = get_login_user($db);
// 管理者ではない場合
if(is_admin($user) === false){
  //　ログインページへリダイレクト
  redirect_to(LOGIN_URL);
}
// 全ての商品情報を取得
$items = get_all_items($db);
// 管理者ページのファイルを読み込み
include_once VIEW_PATH . '/admin_view.php';
