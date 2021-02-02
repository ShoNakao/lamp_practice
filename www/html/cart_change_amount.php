<?php
// 定数を定義したファイルを読み込み
require_once '../conf/const.php';
// 関数を定義したファイルを読み込み
require_once MODEL_PATH . 'functions.php';
// ユーザ情報に関するファイルを読み込み
require_once MODEL_PATH . 'user.php';
// 商品情報に関するファイルを読み込み
require_once MODEL_PATH . 'item.php';
// カート情報に関するファイルを読み込み
require_once MODEL_PATH . 'cart.php';
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
// POSTで送信された値を取得して変数に定義('cart_id')
$cart_id = get_post('cart_id');
// POSTで送信された値を取得して変数に定義('amount')
$amount = get_post('amount');
// カートの購入数の更新が成功した場合
if(update_cart_amount($db, $cart_id, $amount)){
  // 完了メッセージを定義
  set_message('購入数を更新しました。');
// カートの購入数の更新が失敗した場合  
} else {
  // エラーメッセージを定義
  set_error('購入数の更新に失敗しました。');
}
// カート画面へリダイレクト
redirect_to(CART_URL);