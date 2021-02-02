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


$item_id = get_post('item_id');
// カートに商品を追加。既にカートにある商品の場合は、購入数を１つプラス。
// カートへの追加が成功した場合
if(add_cart($db,$user['user_id'], $item_id)){
  // 完了メッセージを定義
  set_message('カートに商品を追加しました。');
  // カートへの追加が失敗した場合
} else {
  // エラーメッセージを定義
  set_error('カートの更新に失敗しました。');
}
// HOME画面へリダイレクト
redirect_to(HOME_URL);