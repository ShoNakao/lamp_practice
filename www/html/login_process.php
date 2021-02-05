<?php
// 定数を定義したファイルを読み込み
require_once '../conf/const.php';
// 関数を定義したファイルを読み込み
require_once MODEL_PATH . 'functions.php';
// ユーザ情報に関するファイルを読み込み
require_once MODEL_PATH . 'user.php';
// セッションを開始(ログインチェック)
session_start();
// セッションに登録されたトークンを取得
$session_token = get_session('csrf_token');
// POSTで送信されたトークンを取得
$post_token = get_post('csrf_token');
// トークンのチェックが問題ない場合
if ($session_token !== '' && $post_token !== '' && $session_token === $post_token) {

  // ログインされている場合(is_loginedからtrueが返ってきた場合)
  if(is_logined() === true){
    // ホームへリダイレクトさせる
    redirect_to(HOME_URL);
  }
  // ユーザ名を$nameとして定義
  $name = get_post('name');
  // パスワードを$passwordとして定義
  $password = get_post('password');
  // dbに接続
  $db = get_db_connect();

  // ログイン情報の照合及び取得
  $user = login_as($db, $name, $password);
  // ログイン情報がdbに存在しない場合
  if( $user === false){
    // エラーメッセージを定義
    set_error('ログインに失敗しました。');
    // ログイン画面にリダイレクト
    redirect_to(LOGIN_URL);
  }
  // ログイン完了のメッセージを定義
  set_message('ログインしました。');
  // 管理者の場合
  if ($user['type'] === USER_TYPE_ADMIN){
    // 管理者用のページへリダイレクト
    redirect_to(ADMIN_URL);
  }
  // 一般ユーザの場合ホームへリダイレクト
  redirect_to(HOME_URL);

}

// ログイン画面にリダイレクト
redirect_to(LOGIN_URL);