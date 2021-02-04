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

  // $_POST['item_id']を取得
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
}

// HOME画面へリダイレクト
redirect_to(HOME_URL);