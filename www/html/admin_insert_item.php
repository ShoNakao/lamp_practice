<?php
// 定数を定義したファイルを読み込み
require_once '../conf/const.php';
// 関数を定義したファイルを読み込み
require_once MODEL_PATH . 'functions.php';
// ユーザ情報に関するファイルを読み込み
require_once MODEL_PATH . 'user.php';
// 商品情報に関するファイルを読み込み
require_once MODEL_PATH . 'item.php';
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
// 管理者ではない場合
if(is_admin($user) === false){
  //　ログインページへリダイレクト
  redirect_to(LOGIN_URL);
}
// $_POST['name']を取得
$name = get_post('name');
// $_POST['price']を取得
$price = get_post('price');
// $_POST['status']を取得
$status = get_post('status');
// $_POST['stock']を取得
$stock = get_post('stock');
// アップロードした画像のファイル名を取得
$image = get_file('image');
// 登録する商品情報の妥当性チェック及び登録
// 登録に成功した場合
if(regist_item($db, $name, $price, $stock, $status, $image)){
  // 完了のメッセージを定義
  set_message('商品を登録しました。');
}else {
  // バリデーションに問題があった場合、または登録に失敗した場合
  // エラーメッセージを定義
  set_error('商品の登録に失敗しました。');
}

// 管理者ページへリダイレクト
redirect_to(ADMIN_URL);