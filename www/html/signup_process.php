<?php
// 定数を定義したファイルを読み込み
require_once '../conf/const.php';
// 関数を定義したファイルを読み込み
require_once MODEL_PATH . 'functions.php';
// ユーザ情報に関するファイルを読み込み
require_once MODEL_PATH . 'user.php';
// セッションを開始(ログインチェック)
session_start();
// ログインされている場合(is_loginedからtrueが返ってきた場合)
if(is_logined() === true){
  // ホーム画面へリダイレクト
  redirect_to(HOME_URL);
}

// サインアップページで入力したユーザ名の定義
$name = get_post('name');
// サインアップページで入力したパスワードの定義
$password = get_post('password');
// サインアップページで入力した確認用パスワードの定義
$password_confirmation = get_post('password_confirmation');
// dbに接続
$db = get_db_connect();

// 入力したユーザ情報の登録
try{
  // 登録するユーザ情報の妥当性チェック及び登録
  $result = regist_user($db, $name, $password, $password_confirmation);
  // 登録するユーザ情報が妥当でない場合、または登録に失敗した場合
  if( $result=== false){
    // エラーメッセージを定義
    set_error('ユーザー登録に失敗しました。');
    // サインアップページへリダイレクト
    redirect_to(SIGNUP_URL);
  }
// 途中でエラーが発生した場合  
}catch(PDOException $e){
  // エラーメッセージを定義
  set_error('ユーザー登録に失敗しました。');
  // サインアップページへリダイレクト
  redirect_to(SIGNUP_URL);
}
// 完了メッセージを定義
set_message('ユーザー登録が完了しました。');
// ログイン情報の照合及び取得
login_as($db, $name, $password);
// ホーム画面へリダイレクト
redirect_to(HOME_URL);