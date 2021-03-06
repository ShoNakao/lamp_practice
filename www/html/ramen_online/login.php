<?php
if ($_SERVER['SERVER_ADDR'] === '118.27.7.241') {
    $host     = 'localhost';
    $username = 'root';        // MySQLのユーザ名（マイページのアカウント情報を参照）
    $password = 'bYa3mzOz4';       // MySQLのパスワード（マイページのアカウント情報を参照）
    $dbname   = 'ec_site';   // MySQLのDB名(このコースではMySQLのユーザ名と同じです）
    $charset  = 'utf8';   // データベースの文字コード
} else {
    $host     = 'mysql';
    $username = 'testuser';        // MySQLのユーザ名（マイページのアカウント情報を参照）
    $password = 'password';       // MySQLのパスワード（マイページのアカウント情報を参照）
    $dbname   = 'sample';   // MySQLのDB名(このコースではMySQLのユーザ名と同じです）
    $charset  = 'utf8';   // データベースの文字コード
}
 
// MySQL用のDSN文字列
$dsn = 'mysql:dbname='.$dbname.';host='.$host.';charset='.$charset;

$data_name   = array();
$data_pass = array();
$data      = array();
$err_msg   = array();     // エラーメッセージ

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ユーザ名を定義
    if (isset($_POST['name']) === TRUE) {
        $user_name = trim($_POST['name']);
        $user_name = htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8');
    }
    // ユーザパスワードを定義
    if (isset($_POST['pass']) === TRUE) {
        $user_pass = $_POST['pass'];
        $user_pass = htmlspecialchars($user_pass, ENT_QUOTES, 'UTF-8');
    }
    // ユーザ名,パスワードをチェック
    $user_regex = '/^[a-zA-Z0-9]{6,8}$/';
    // バリデーション実行
    if (preg_match($user_regex, $user_name) !== 1 ) {
        $err_msg[] = 'エラー!：ユーザー名は半角英数字6～8文字で入力してください。';
    }
    if (preg_match($user_regex, $user_pass) !== 1 ) {
        $err_msg[] = 'エラー!：パスワードは半角英数字6～8文字で入力してください。';
    }
    try {
        // データベースに接続
        $dbh = new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
      
        if (count($err_msg) === 0) {
            //
            // ユーザ名の登録を確認
            //
            // SQL文を作成
            $sql = 'SELECT user_name FROM ec_user WHERE user_name = ?';
            // SQL文を実行する準備
            $stmt = $dbh->prepare($sql);
            // SQL文のプレースホルダーに値をバインド
            $stmt->bindValue(1, $user_name, PDO::PARAM_STR);
            // SQLを実行
            $stmt->execute();
            // レコードの取得
            $rows = $stmt->fetchAll();
            // 1行ずつ結果を配列で取得
            foreach ($rows as $row) {
                $data_name[] = $row;
            }
            if (count($data_name) !== 1) {
                $err_msg[] = 'エラー!：ユーザー名に誤りがあ�����ます。';
            }
        }
        
        if (count($err_msg) === 0) {
            //
            // パスワードの登録を確認
            //
            // SQL文を作成
            $sql = 'SELECT password FROM ec_user WHERE user_name = ?';
            // SQL文を実行する準備
            $stmt = $dbh->prepare($sql);
            // SQL文のプレースホルダーに値をバインド
            $stmt->bindValue(1, $user_name, PDO::PARAM_STR);
            // SQLを実行
            $stmt->execute();
            // レコードの取得
            $rows = $stmt->fetchAll();
            // 1行ずつ結果を配列で取得
            foreach ($rows as $row) {
                if ($row['password'] !== $user_pass) {
                    $err_msg[] = 'エラー!：パスワードに誤りがあります。';
                }
                $data_pass[] = $row;
            }
        }
        
        if (count($err_msg) === 0) {
            session_start();
            // ユーザ名をCookieに保存
            setcookie('user_name', $user_name);
            // ユーザ名とパスワードに応じたuser_idをデータベースから取得
            // SQL文を作成
            $sql = 'SELECT user_id FROM ec_user WHERE user_name = ? AND password = ?';
            // SQL文を実行する準備
            $stmt = $dbh->prepare($sql);
            // SQL文のプレースホルダーに値をバインド
            $stmt->bindValue(1, $user_name, PDO::PARAM_STR);
            $stmt->bindValue(2, $user_pass, PDO::PARAM_STR);
            // SQLを実行
            $stmt->execute();
            // レコードの取得
            $rows = $stmt->fetchAll();
            // 1行ずつ結果を配列で取得
            foreach ($rows as $row) {
                $data[] = $row;
            }
            // 登録データを取得できたか確認
            if (isset($data[0]['user_id'])) {
                // セッション変数にuser_idを保存
                $_SESSION['user_id'] = $data[0]['user_id'];
                // ログイン済みユーザのホームページへリダイレクト
                header("Location: ./product_list.php");
                exit;
            } else {
                // ログインページへリダイレクト
                header('Location: ./login.php');
                exit;
            }
        }
    } catch (PDOException $e) {
      // 接続失敗した場合
      $err_msg['db_connect'] = 'DBエラー：'.$e->getMessage();
    }
}    


?>

<!doctype html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <title>ラーメンOnline</title>
        <link rel="stylesheet" href="html5reset-1.6.1.css">
        <link rel="stylesheet" href="ecsite_login.css">
    </head>
    <body>
        <header>
            <div>
                <div id="header_left">
                    <a href="./product_list.php"><p>ラーメンOnline</p></a>
                </div>
            </div>
        </header>
        <main>
            <form method="post" enctype="multipart/form-data">
                <div class="main-form-container">
                    <label>ユーザー名</label><input type="text" name="name">
                </div>
                <div class="main-form-container">
                    <label>パスワード</label><input type="password" name="pass">
                </div>    
                <input class="img-login" type="image" src="img/icon/login.png" alt="ログイン">
                <a href="./registration.php">新規登録</a>
            </form>
            <?php foreach ($err_msg as $value) { ?>
                <p><?php print $value; ?></p>
            <?php } ?>
        </main>
    </body>
</html>