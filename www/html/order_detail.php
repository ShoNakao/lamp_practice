<?php
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
// セッションに登録されたトークンを取得
$session_token = get_session('csrf_token');
// POSTで送信されたトークンを取得
$post_token = get_post('csrf_token');
// トークンのチェックが問題ない場合
if ($session_token !== '' && $post_token !== '' && $session_token === $post_token) {

  // ログインされていない場合(is_loginedからfalseが返ってきた場合)
  if(is_logined() === false){
    // ログインページへリダイレクト
    redirect_to(LOGIN_URL);
  }

  // dbに接続
  $db = get_db_connect();
  // user_idに紐付いたユーザ情報(user_id,name,password,type)を取得
  $user = get_login_user($db);
  // $_POST['order_id']を取得
  $order_id = get_post('order_id');

  // 管理者の場合
  if ($user['type'] === USER_TYPE_ADMIN){
    // order_idに紐付いた特定の購入履歴
    // (orders/order_id,order_datetime order_details/total)を取得
    $order = get_order($db, $order_id);
    // order_idに紐付いた購入明細
    // (order_details/order_price,order_amount,subtotal items.name)を取得
    $order_details = get_order_details($db, $order_id);
  } else {  
    // user_idとorder_idに紐付いた購入履歴
    // (orders/order_id,order_datetime order_details/total)を取得
    $order = get_user_order($db, $user['user_id'], $order_id);
    // user_idとorder_idに紐付いた購入明細
    // (order_details/order_price,order_amount,subtotal items.name)を取得
    $order_details = get_user_order_details($db, $user['user_id'], $order_id);
  }

}

// カートページのファイルを読み込む
include_once VIEW_PATH . 'order_detail_view.php';