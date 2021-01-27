<?php

function dd($var){
  var_dump($var);
  exit();
}

// 引数で渡されたurlにリダイレクト
function redirect_to($url){
  header('Location: ' . $url);
  exit;
}

function get_get($name){
  if(isset($_GET[$name]) === true){
    return $_GET[$name];
  };
  return '';
}

// POSTで送信された値の取得
function get_post($name){
  // POSTで値が送信されている場合
  if(isset($_POST[$name]) === true){
    // 値をを戻り値として返す
    return $_POST[$name];
  };
  // POSTで値が送信されていない場合、空を戻り値として返す
  return '';
}

// 送信されたファイル情報を取得
function get_file($name){
  // ファイルが存在する場合
  if(isset($_FILES[$name]) === true){
    // ファイル名を返す
    return $_FILES[$name];
  };
  // ファイルが存在しない場合、空の配列を返す
  return array();
}

// 引数をキーとするセッションの取得
function get_session($name){
  // 引数をキーとするセッションが存在する場合(ログインされている場合)
  if(isset($_SESSION[$name]) === true){
    // 戻り値として引数をキーとするセッションの値を返す
    return $_SESSION[$name];
  };
  // 引数をキーとするセッションが存在しない場合(ログインされていない場合)、空を返す
  return '';
}

// セッションにユーザ名を登録
function set_session($name, $value){
  $_SESSION[$name] = $value;
}

// エラーメッセージを定義
function set_error($error){
  $_SESSION['__errors'][] = $error;
}

function get_errors(){
  $errors = get_session('__errors');
  if($errors === ''){
    return array();
  }
  set_session('__errors',  array());
  return $errors;
}

function has_error(){
  return isset($_SESSION['__errors']) && count($_SESSION['__errors']) !== 0;
}

// 完了メッセージを定義
function set_message($message){
  $_SESSION['__messages'][] = $message;
}

function get_messages(){
  $messages = get_session('__messages');
  if($messages === ''){
    return array();
  }
  set_session('__messages',  array());
  return $messages;
}

// ログインチェック
function is_logined(){
  // ユーザ名を引数として渡して、
  // ログインされている場合はtrue、ログインされていない場合はfalseを戻り値として返す
  return get_session('user_id') !== '';
}

// アップロードされた画像にバリデーションをかけ、
// ファイル名をランダムな文字列に変換する
function get_upload_filename($file){
  // アップロードされた画像にバリデーションをかける
  // 不正なアップロードまたは.jpg.png以外の場合
  if(is_valid_upload_image($file) === false){
    // 空文字を返す
    return '';
  }
  // 画像を読み、型を定義する
  $mimetype = exif_imagetype($file['tmp_name']);
  // 方に対応する拡張子を定義
  $ext = PERMITTED_IMAGE_TYPES[$mimetype];
  // 20バイト分のランダムな文字列を取得してファイル名にして、拡張子を付ける
  return get_random_string() . '.' . $ext;
}

// 20バイト分のランダムな文字列を取得する
function get_random_string($length = 20){
  // 20バイト分のランダムな文字列を返す
  return substr(base_convert(hash('sha256', uniqid()), 16, 36), 0, $length);
}

// 画像の保存
function save_image($image, $filename){
  // 仮アップロードされている画像ファイルを特定のディレクトリへ移動
  return move_uploaded_file($image['tmp_name'], IMAGE_DIR . $filename);
}

function delete_image($filename){
  if(file_exists(IMAGE_DIR . $filename) === true){
    unlink(IMAGE_DIR . $filename);
    return true;
  }
  return false;
  
}


// 文字数の妥当性チェック
function is_valid_length($string, $minimum_length, $maximum_length = PHP_INT_MAX){
  // 文字数を取得
  $length = mb_strlen($string);
  // 文字列が最小文字数以上かつ最大文字数以下ならTRUE,でなければFALSE
  return ($minimum_length <= $length) && ($length <= $maximum_length);
}

// 半角英数字の正規表現によるバリデーション
function is_alphanumeric($string){
  // 半角英数字の場合はTRUE,でない場合はFALSEを返す
  return is_valid_format($string, REGEXP_ALPHANUMERIC);
}

// 0以上の整数の正規表現によるバリデーション
function is_positive_integer($string){
  // 0以上の整数の場合はTRUE,でない場合はFALSEを返す
  return is_valid_format($string, REGEXP_POSITIVE_INTEGER);
}

// 正規表現でバリデーション
function is_valid_format($string, $format){
  // 正規表現にマッチする場合はTRUE,しない場合はFALSEを返す
  return preg_match($format, $string) === 1;
}

// アップロードされた画像にバリデーションをかける
function is_valid_upload_image($image){
  // HTTP POST でファイルがアップロードされていない場合(セキュリティチェック)
  if(is_uploaded_file($image['tmp_name']) === false){
    // エラーメッセージを定義
    set_error('ファイル形式が不正です。');
    // FALSEを返す
    return false;
  }
  // HTTP POST でファイルがアップロードされている場合
  // 画像を読み、型を定義する
  $mimetype = exif_imagetype($image['tmp_name']);
  // JPEGもしくはPNGでない場合
  if( isset(PERMITTED_IMAGE_TYPES[$mimetype]) === false ){
    // エラーメッセージを定義
    set_error('ファイル形式は' . implode('、', PERMITTED_IMAGE_TYPES) . 'のみ利用可能です。');
    // FALSEを返す
    return false;
  }
  // TRUEを返す
  return true;
}

// HTMLエスケープをする
function h($str){
  // HTMLエスケープをした値を返す
  return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
