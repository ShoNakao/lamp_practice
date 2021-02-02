<?php
// iframeの読み込みを禁止
header('X-FRAME-OPTIONS: DENY');

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
// // 特定のユーザのカートに入っている全ての商品情報をdbから取得
// (items/item_id,name,price,stock,status,image carts/cart_id,user_id,amount)を取得
$carts = get_user_carts($db, $user['user_id']);

// カート内の商品を購入する
if(purchase_carts($db, $carts) === false){
  set_error('商品が購入できませんでした。');
  redirect_to(CART_URL);
} 

$total_price = sum_carts($carts);

include_once '../view/finish_view.php';