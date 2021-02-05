<?php
// iframeの読み込みを禁止
header('X-FRAME-OPTIONS: DENY');

// 定数を定義したファイルを読み込み
require_once '../conf/const.php';
// 関数を定義したファイルを読み込み
require_once MODEL_PATH . 'functions.php';
// セッションを開始(ログインチェック)
session_start();
// ログインされている場合(is_loginedからtrueが返ってきた場合)
if(is_logined() === true){
  // ホーム画面へリダイレクト
  redirect_to(HOME_URL);
}
// トークンを生成
$token = get_random_string();
// SESSIONにトークンを登録
set_session('csrf_token', $token);
// サインアップのファイルを読み込む
include_once VIEW_PATH . 'signup_view.php';



