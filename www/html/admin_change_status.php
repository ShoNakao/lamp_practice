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
  // $_POST['changes_to']を取得
  $changes_to = get_post('changes_to');

  // 送信されたステータスが公開の場合
  if($changes_to === 'open'){
    // ステータスの更新(OPENへ)
    update_item_status($db, $item_id, ITEM_STATUS_OPEN);
    // 完了のメッセージを定義
    set_message('ステータスを変更しました。');
  // 送信されたステータスが非公開の場合
  }else if($changes_to === 'close'){
    // ステータスの更新(CLOSEへ)
    update_item_status($db, $item_id, ITEM_STATUS_CLOSE);
    // 完了のメッセージを定義
    set_message('ステータスを変更しました。');
  // 不正な値が送信された場合(open,close以外の場合)
  }else {
    // エラーメッセージを定義
    set_error('不正なリクエストです。');
  }
}
// 管理者ページへリダイレクト
redirect_to(ADMIN_URL);