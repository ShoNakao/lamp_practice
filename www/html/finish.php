<?php
// iframeの読み込みを禁止
header('X-FRAME-OPTIONS: DENY');

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
  // // 特定のユーザのカートに入っている全ての商品情報をdbから取得
  // (items/item_id,name,price,stock,status,image carts/cart_id,user_id,amount)を取得
  $carts = get_user_carts($db, $user['user_id']);

  // カート内の商品が購入できなかった場合
  if(purchase_carts($db, $carts) === false){
    // エラーメッセージを定義
    set_error('商品が購入できませんでした。');
    // カート画面へリダイレクト
    redirect_to(CART_URL);
  } 

  // 合計金額を計算
  $total_price = sum_carts($carts);
}

// 購入完了ページのファイルを読み込み
include_once '../view/finish_view.php';