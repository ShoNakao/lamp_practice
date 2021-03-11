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

$err_msg    = array();

try {
    // データベースに接続
    $dbh = new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    //
    // ユーザ名の重複を確認
    //
    // SQL文を作成
    $sql = 'SELECT user_name, create_datetime FROM ec_user';
    // SQL文を実行する準備
    $stmt = $dbh->prepare($sql);
    // SQLを実行
    $stmt->execute();
    // レコードの取得
    $rows = $stmt->fetchAll();
    // 1行ずつ結果を配列で取得
    foreach ($rows as $row) {
        $data[] = $row;
    }
} catch (PDOException $e) {
    // 接続失敗した場合
    $err_msg['db_connect'] = 'DBエラー：'.$e->getMessage();
}
?>

<!doctype html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <title>ユーザ管理ページ</title>
        <style>
            table {
                border-collapse: collapse;
            }
            
            th, td {
                border: 1px solid;
                width: 500px;
            }
        </style>
    </head>
    <body>
        <?php foreach ($err_msg as $value) { ?>
        <p><?php print $value; ?></p>
        <?php } ?>
        <h1>ラーメンOnline 管理ページ</h1>
        <a href="./tool.php">商品管理ページ</a><hr>
        <h2>ユーザ情報一覧</h2>
        <table>
            <tr>
                <th>ユーザ名</th>
                <th>登録日</th>
            </tr>
            <?php foreach ($data as $value)  { ?>
            <tr>
                <td><?php print $value['user_name'] ?></td>
                <td><?php print $value['create_datetime'] ?></td>
            </tr>
            <?php } ?>
        </table>
    </body>
</html>