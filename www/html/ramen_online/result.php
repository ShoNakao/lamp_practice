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

// アップロードした画像ファイルの保存ディレクトリ
$img_dir    = './img/product/';

// 初期化
$user_name    = '';
$data         = array();
$err_msg      = array();     // エラーメッセージ
$result_msg   = array();
$total        = 0;

// セッションスタート
session_start();
// セッション変数からuser_id取得
if (isset($_SESSION['user_id'])) {
  $user_id = $_SESSION['user_id'];
} else {
  // 非ログインの場合、ログインページへリダイレクト
  header('Location: ./login.php');
  exit;
}

// 購入ボタンが押された場合
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // cart内情報取得
    try {
        // データベースに接続
        $dbh = new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        
        //
        // ユーザidに応じた、ユーザ名を取得
        //
        // SQL文を作成
        $sql = 'SELECT user_name FROM ec_user WHERE user_id = ?';
        // SQL文を実行する準備
        $stmt = $dbh->prepare($sql);
        // SQL文のプレースホルダーに値をバインド
        $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
        // SQLを実行
        $stmt->execute();
        // レコードの取得
        $rows = $stmt->fetchAll();
        // 結果を変数で定義
        $user_name = $rows[0]['user_name'];
        
        //
        // カート内の商品情報を取得
        //
        // SQL文を作成
        $sql = 'SELECT ec_cart.amount, ec_cart.item_id, ec_item_master.name, ec_item_master.prefecture, ec_item_master.area, ec_item_master.price, ec_item_master.img, ec_item_stock.stock
            FROM ec_cart INNER JOIN ec_item_master ON ec_cart.item_id = ec_item_master.item_id INNER JOIN ec_item_stock ON ec_item_master.item_id = ec_item_stock.item_id
            WHERE ec_cart.user_id = ?';
        // SQL文を実行する準備
        $stmt = $dbh->prepare($sql);
        // SQL文のプレースホルダーに値をバインド
        $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
        // SQLを実行
        $stmt->execute();
        // レコードの取得
        $rows = $stmt->fetchAll();
        // 1行ずつ結果を配列で取得
        foreach ($rows as $row) {
            $data[] = $row;
        }
        if (count($data) === 0) {
            $err_msg[] = '!!カートに商品がありません!!';
        } else {
            foreach ($data as $value) {
                if ($value['stock'] === 0) {
                    $err_msg[] = 'エラー!：' .$value['name']. 'は売り切れました。他の商品を購入する場合は' .$value['name']. 'を削除してください。';
                } else if ($value['stock'] < $value['amount']) {
                    $err_msg[] = 'エラー!：在庫が不足しています。(在庫数：' .$value['stock']. ')';
                }
            }
        }
        
    } catch (PDOException $e) {
        // 接続失敗した場合
        $err_msg['db_connect'] = 'DBエラー：'.$e->getMessage();
    }
    
    try {
        // データベースに接続
        $dbh = new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        
        // トランザクション開始
        $dbh->beginTransaction();
        // エラーがなければ
        if (count($err_msg) === 0) {
            try {
                // 現在日時を取得
                $datetime = date('Y-m-d H:i:s');
                //
                // 購入商品をカートから削除
                //
                // SQL文を作成
                $sql = 'DELETE FROM ec_cart WHERE user_id = ?';
                // SQL文を実行する準備
                $stmt = $dbh->prepare($sql);
                // SQL文のプレースホルダに値をバインド
                $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
                // SQLを実行
                $stmt->execute();
                
                //
                // ストックテーブルのストックを減らす
                //
                // SQL文を作成
                foreach ($data as $value) {
                    $sql = 'UPDATE ec_item_stock SET stock = stock-? WHERE item_id = ?';
                    // SQL文を実行する準備
                    $stmt = $dbh->prepare($sql);
                    // SQL文のプレースホルダーに値をバインド
                    $stmt->bindValue(1, $value['amount'], PDO::PARAM_INT);
                    $stmt->bindValue(2, $value['item_id'], PDO::PARAM_INT);
                    // SQLを実行
                    $stmt->execute();
                }
                
                //
                // ヒストリーテーブルに購入商品を追加
                //
                // SQL文を作成
                foreach ($data as $value) {
                    $sql = 'INSERT INTO ec_history(item_id, user_id, amount, create_datetime) VALUES(?, ?, ?, ?)';
                    // SQL文を実行する準備
                    $stmt = $dbh->prepare($sql);
                    // SQL文のプレースホルダーに値をバインド
                    $stmt->bindValue(1, $value['item_id'], PDO::PARAM_INT);
                    $stmt->bindValue(2, $user_id, PDO::PARAM_INT);
                    $stmt->bindValue(3, $value['amount'], PDO::PARAM_INT);
                    $stmt->bindValue(4, $datetime, PDO::PARAM_STR);
                    // SQLを実行
                    $stmt->execute();
                }
                // コミット処理
                $dbh->commit();
                
                $result_msg[] = '商品を購入しました。';
            } catch (PDOException $e) {
                // ロールバック処理(取消)
                $dbh->rollback();
                // 例外をスロー
                throw $e;
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
        <link rel="stylesheet" href="ecsite_cart.css">
    </head>
    <body>
        <header>
            <div>
                <div id="header_left">
                    <a href="./product_list.php"><p>ラーメンOnline</p></a>
                </div>
                <div id="header_right">
                    <p>ユーザー名：<?php print $user_name ?></p>
                    <a href="./cart.php"><img src="img/icon/cart.png" alt="カート"></a>
                    <a href="./history.php">購入履歴</a>
                    <a href="./logout.php">ログアウト</a>
                </div>
            </div>
        </header>
        <section class="msg">
        <?php if (count($err_msg) !== 0) { ?>
        <?php foreach ($err_msg as $value) { ?>
            <p><?php print $value; ?></p>
        <?php } ?>
        <?php exit; ?>
        <?php } ?>
        <?php foreach ($result_msg as $value) { ?>
            <p class="result.msg"><?php print $value; ?></p>
        <?php } ?>

        </section>
        <main>
            <table>
                <tr>
                    <th colspan="3">価格</th>
                    <th colspan="1">数量</th>
                </tr>
                <?php foreach ($data as $value) { ?>
                <tr>
                    <td width="220px"><img src="<?php print $img_dir . $value['img']; ?>"></td>
                    <td width="280px">
                        <p><?php print $value['name']; ?></p>
                        <p><?php print $value['prefecture']; ?></p>
                        <p><?php print $value['area']; ?></p>
                    </td>
                    <td width="140px"><?php print $value['price']; ?>円</td>
                    <td width="150px"><?php print $value['amount']; ?>個</td>
                </tr>
                <?php } ?>
            </table>
            <section class="total">
                <p>合計</p>
                <?php foreach ($data as $value) {
                    $total += $value['price'] * $value['amount'];
                } ?>
                <p><?php print $total; ?>円</p>
            </section>
        </main>
    </body>
</html>