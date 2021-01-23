<?php
// 各定数が定義されている

// modelフォルダのパスを定義
define('MODEL_PATH', $_SERVER['DOCUMENT_ROOT'] . '/../model/');
// viewフォルダのパスを定義
define('VIEW_PATH', $_SERVER['DOCUMENT_ROOT'] . '/../view/');


define('IMAGE_PATH', '/assets/images/');
define('STYLESHEET_PATH', '/assets/css/');  // cssフォルダのパスを定義
define('IMAGE_DIR', $_SERVER['DOCUMENT_ROOT'] . '/assets/images/' );

define('DB_HOST', 'mysql');
define('DB_NAME', 'sample');
define('DB_USER', 'testuser');
define('DB_PASS', 'password');
define('DB_CHARSET', 'utf8');

define('SIGNUP_URL', '/signup.php');
define('LOGIN_URL', '/login.php');      // ログイン画面(login.php)のパスを定義
define('LOGOUT_URL', '/logout.php');
define('HOME_URL', '/index.php');       // HOME(index.php)のパスを定義
define('CART_URL', '/cart.php');
define('FINISH_URL', '/finish.php');
define('ADMIN_URL', '/admin.php');      // 管理者用ページ(admin.php)のパスを定義

define('REGEXP_ALPHANUMERIC', '/\A[0-9a-zA-Z]+\z/');
define('REGEXP_POSITIVE_INTEGER', '/\A([1-9][0-9]*|0)\z/');


define('USER_NAME_LENGTH_MIN', 6);
define('USER_NAME_LENGTH_MAX', 100);
define('USER_PASSWORD_LENGTH_MIN', 6);
define('USER_PASSWORD_LENGTH_MAX', 100);

define('USER_TYPE_ADMIN', 1);           // 管理者は1
define('USER_TYPE_NORMAL', 2);          // 一般ユーザは2

define('ITEM_NAME_LENGTH_MIN', 1);
define('ITEM_NAME_LENGTH_MAX', 100);

define('ITEM_STATUS_OPEN', 1);
define('ITEM_STATUS_CLOSE', 0);

define('PERMITTED_ITEM_STATUSES', array(
  'open' => 1,
  'close' => 0,
));

define('PERMITTED_IMAGE_TYPES', array(
  IMAGETYPE_JPEG => 'jpg',
  IMAGETYPE_PNG => 'png',
));