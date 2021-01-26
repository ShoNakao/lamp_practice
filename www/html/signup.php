<?php
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
// サインアップのファイルを読み込む
include_once VIEW_PATH . 'signup_view.php';



