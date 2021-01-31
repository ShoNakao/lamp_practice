<?php
require_once '../conf/const.php';
require_once MODEL_PATH . 'functions.php';
require_once MODEL_PATH . 'user.php';
require_once MODEL_PATH . 'item.php';
require_once MODEL_PATH . 'cart.php';

session_start();

if(is_logined() === false){
  redirect_to(LOGIN_URL);
}

$db = get_db_connect();
$user = get_login_user($db);

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

// カート画面へリダイレクト
redirect_to(CART_URL);