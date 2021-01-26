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
// user_idに紐付いたユーザ情報
// (items/item_id,name,price,stock,status,image carts/cart_id,user_id,amount)を取得
$carts = get_user_carts($db, $user['user_id']);
// 合計金額を取得
$total_price = sum_carts($carts);
// カートページのファイルを読み込む
include_once VIEW_PATH . 'cart_view.php';