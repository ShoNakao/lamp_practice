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

  // 管理者ではない場合
  if(is_admin($user) === false){
    //　ログインページへリダイレクト
    redirect_to(LOGIN_URL);
  }

  // $_POST['item_id']を取得
  $item_id = get_post('item_id');

// 商品と商品画像の削除に成功した場合
  if(destroy_item($db, $item_id) === true){
    // 完了のメッセージを定義
    set_message('商品を削除しました。');
  // 商品と商品画像の削除に失敗した場合
  } else {
    // エラーメッセージを定義
    set_error('商品削除に失敗しました。');
  }
}

// 管理者ページへリダイレクト
redirect_to(ADMIN_URL);