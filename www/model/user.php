<?php
require_once MODEL_PATH . 'functions.php';
require_once MODEL_PATH . 'db.php';

// ユーザidに紐づくユーザ情報をdbから取得
function get_user($db, $user_id){
  $sql = "
    SELECT
      user_id, 
      name,
      password,
      type
    FROM
      users
    WHERE
      user_id = {$user_id}
    LIMIT 1
  ";
  // sqlを実行して結果を返す
  return fetch_query($db, $sql);
}

// ユーザ名に紐づく各情報をdbから取得
function get_user_by_name($db, $name){
  $sql = "
    SELECT
      user_id, 
      name,
      password,
      type
    FROM
      users
    WHERE
      name = '{$name}'
    LIMIT 1
  ";

  return fetch_query($db, $sql);
}

// ログイン情報の照合及び取得
function login_as($db, $name, $password){
  // ユーザ名に紐づく各情報をdbから取得
  $user = get_user_by_name($db, $name);
  // dbにユーザ名が存在しない、またはパスワードが違う場合は
  if($user === false || $user['password'] !== $password){
    return false;
  }
  // ユーザ名・パスワードともに確認できた場合、セッションにユーザ名を登録
  set_session('user_id', $user['user_id']);
  return $user;
}

// ログインしているユーザの情報を取得
function get_login_user($db){
  // セッションからuser_idを取得
  $login_user_id = get_session('user_id');
  // ユーザidに紐づく各情報をdbから取得
  // (user_id,name,password,type)を返す
  return get_user($db, $login_user_id);
}

// 登録するユーザ情報の妥当性チェック及び登録
function regist_user($db, $name, $password, $password_confirmation) {
  // 登録するユーザ情報が妥当でない場合
  if( is_valid_user($name, $password, $password_confirmation) === false){
    // FALSEを返す
    return false;
  }
  // 登録するユーザ情報が妥当な場合、ユーザ情報の登録を行い
  // 登録できた場合はTRUE、できなかった場合はFALSEを返す
  return insert_user($db, $name, $password);
}

// 管理者かどうかの判別
function is_admin($user){
  // 管理者(1=1)の場合TRUE、一般ユーザの場合はFALSEを返す
  return $user['type'] === USER_TYPE_ADMIN;
}

// 入力情報の妥当性チェック
function is_valid_user($name, $password, $password_confirmation){
  // 短絡評価を避けるため一旦代入。
  // ユーザ名の妥当性チェック
  $is_valid_user_name = is_valid_user_name($name);
  // パスワードの妥当性チェック
  $is_valid_password = is_valid_password($password, $password_confirmation);
  // どちらも妥当な場合TRUE、片方でも妥当でない場合FALSE
  return $is_valid_user_name && $is_valid_password ;
}

// ユーザ名の妥当性チェック
function is_valid_user_name($name) {
  // 初期値をtrueに定義
  $is_valid = true;
  // 文字列が最小文字数以上かつ最大文字数以下でなければ
  if(is_valid_length($name, USER_NAME_LENGTH_MIN, USER_NAME_LENGTH_MAX) === false){
    // エラーメッセージを定義
    set_error('ユーザー名は'. USER_NAME_LENGTH_MIN . '文字以上、' . USER_NAME_LENGTH_MAX . '文字以内にしてください。');
    $is_valid = false;
  }
  // 文字列が半角英数字でない場合
  if(is_alphanumeric($name) === false){
    // エラーメッセージを定義
    set_error('ユーザー名は半角英数字で入力してください。');
    $is_valid = false;
  }
  // ユーザ名が妥当な場合TRUE,でない場合FALSE
  return $is_valid;
}

function is_valid_password($password, $password_confirmation){
  $is_valid = true;
  if(is_valid_length($password, USER_PASSWORD_LENGTH_MIN, USER_PASSWORD_LENGTH_MAX) === false){
    set_error('パスワードは'. USER_PASSWORD_LENGTH_MIN . '文字以上、' . USER_PASSWORD_LENGTH_MAX . '文字以内にしてください。');
    $is_valid = false;
  }
  if(is_alphanumeric($password) === false){
    set_error('パスワードは半角英数字で入力してください。');
    $is_valid = false;
  }
  if($password !== $password_confirmation){
    set_error('パスワードがパスワード(確認用)と一致しません。');
    $is_valid = false;
  }
  return $is_valid;
}

// ユーザ情報の登録
function insert_user($db, $name, $password){
  $sql = "
    INSERT INTO
      users(name, password)
    VALUES ('{$name}', '{$password}');
  ";
  // 登録できた場合はTRUE、できなかった場合はFALSEを返す
  return execute_query($db, $sql);
}

