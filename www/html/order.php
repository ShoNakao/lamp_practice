<?php
// iframeの読み込みを禁止
header('X-FRAME-OPTIONS: DENY');
// 定数を定義したファイルを読み込み
require_once '../conf/const.php';
// 関数を定義したファイルを読み込み
require_once MODEL_PATH . 'functions.php';
// ユーザ情報に関するファイルを読み込み
require_once MODEL_PATH . 'user.php';
// 購入履歴に関するファイルを読み込み
require_once MODEL_PATH . 'order.php';

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
// 管理者の場合
if ($user['type'] === USER_TYPE_ADMIN){
  // 全ての購入履歴を取得
  $orders = get_orders($db);
// 一般ユーザの場合
} else {
  // user_idに紐付いた購入履歴
  // (orders/order_id,order_datetime order_details/total)を取得
  $orders = get_user_orders($db, $user['user_id']);
}
// トークンを生成
$token = get_random_string();
// SESSIONにトークンを登録
set_session('csrf_token', $token);
// カートページのファイルを読み込む
include_once VIEW_PATH . 'order_view.php';