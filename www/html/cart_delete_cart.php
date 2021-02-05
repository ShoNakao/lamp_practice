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

  // $_POST['cart_id']を取得
  $cart_id = get_post('cart_id');

  // カート内の特定の商品を削除
  // 商品の削除に成功した場合
  if(delete_cart($db, $cart_id)){
    // 完了メッセージを定義
    set_message('カートを削除しました。');
    // 商品の削除に失敗した場合
  } else {
    // エラーメッセージを定義
    set_error('カートの削除に失敗しました。');
  }
  
}

// カート画面へリダイレクト
redirect_to(CART_URL);