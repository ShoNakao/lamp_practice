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
// 公開されている商品の情報(item_id,name,stock,price,image,status)を取得
$items = get_open_items($db);
// 購入数の多い商品1~3位の情報
// (order_details/item_id items/name,stock,price,image,status amount_total)を取得
$ranks = get_ranks($db);
// トークンを生成
$token = get_random_string();
// SESSIONにトークンを登録
set_session('csrf_token', $token);
// 商品一覧ページのファイルを読み込む
include_once VIEW_PATH . 'index_view.php';