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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['process_kind']) === TRUE) {
        $process_kind = $_POST['process_kind'];
    }
}  

// 削除ボタンが押された場合
if (isset($process_kind) === TRUE && $process_kind === 'delete') {
    //ステータスの更新
    try {
      // データベースに接続
      $dbh = new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
      $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
      if (count($err_msg) === 0) {    
          // 変数を定義
          $delete_id = $_POST['item_id'];
          //
          // 在庫情報テーブルを更新
          //
          // SQL文を作成
          $sql = 'DELETE FROM ec_cart WHERE user_id = ? AND item_id = ?';
          // SQL文を実行する準備
          $stmt = $dbh->prepare($sql);
          // SQL文のプレースホルダーに値をバインド
          $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
          $stmt->bindValue(2, $delete_id, PDO::PARAM_INT);
          // SQLを実行
          $stmt->execute();
          $result_msg[] = '商品を削除しました。';
      }    
    } catch (PDOException $e) {
      // 接続失敗した場合
      $err_msg['db_connect'] = 'DBエラー：'.$e->getMessage();
    }

// 数量変更ボタンが押された場合
} else if (isset($process_kind) === TRUE && $process_kind === 'change_amount') {
    if (isset($_POST['change_amount']) === TRUE ) {
        $change_amount = trim($_POST['change_amount']);          //trimは文字列の先頭および末尾にあるホワイトスペースを取り除く
        $change_amount = htmlspecialchars($change_amount, ENT_QUOTES, 'UTF-8');
    }
    if ($change_amount === '') {
        $err_msg[] = 'エラー!：個数を入力してください。';
    } else if (preg_match("/^[0-9]+$/", $change_amount) !== 1) {
        $err_msg[] = 'エラー!：個数は半角数値で入力してください。';
    }
    
    // 数量の更新
    try {
        // データベースに接続
        $dbh = new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
          
        if (count($err_msg) === 0) {
            // 変数を定義
            $update_datetime = date('Y-m-d H:i:s');
            $update_id = $_POST['item_id'];
            //
            // cartテーブルを更新
            //
            // SQL文を作成
            $sql = 'UPDATE ec_cart SET amount = ?, update_datetime = ? WHERE user_id = ? AND item_id = ?';
            // SQL文を実行する準備
            $stmt = $dbh->prepare($sql);
            // SQL文のプレースホルダーに値をバインド
            $stmt->bindValue(1, $change_amount, PDO::PARAM_INT);
            $stmt->bindValue(2, $update_datetime, PDO::PARAM_STR);
            $stmt->bindValue(3, $user_id, PDO::PARAM_INT);
            $stmt->bindValue(4, $update_id, PDO::PARAM_INT);
            // SQLを実行
            $stmt->execute();
            $result_msg[] = '数量を変更しました。';
      }  
    } catch (PDOException $e) {
        // 接続失敗した場合
        $err_msg['db_connect'] = 'DBエラー：'.$e->getMessage();
    }  
}

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
        $sql = 'SELECT ec_cart.amount, ec_cart.item_id, ec_item_master.name, ec_item_master.prefecture, ec_item_master.area, ec_item_master.price, ec_item_master.img
            FROM ec_cart INNER JOIN ec_item_master ON ec_cart.item_id = ec_item_master.item_id
            WHERE ec_cart.user_id = ?';
        // SQL文を実行する準備
        $stmt = $dbh->prepare($sql);
        // SQL文のプレースホルダーに値をバインド
        $stmt->bindValue(1, $user_id, PDO::PARAM_STR);
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
        <?php foreach ($err_msg as $value) { ?>
            <p><?php print $value; ?></p>
        <?php } ?>
        <?php foreach ($result_msg as $value) { ?>
            <p><?php print $value; ?></p>
        <?php } ?>
        </section>
        <main>
            <p class="title">カート</p>
            <table>
                <tr>
                    <th colspan="4">価格</th>
                    <th colspan="2">数量</th>
                </tr>
                <?php foreach ($data as $value) { ?>
                <tr>
                    <td width="220px"><img src="<?php print $img_dir . $value['img']; ?>"></td>
                    <td width="220px">
                        <p><?php print $value['name']; ?></p>
                        <p><?php print $value['prefecture']; ?></p>
                        <p><?php print $value['area']; ?></p>
                    </td>
                    <form method="post" enctype="multipart/form-data">
                        <td width="60px">
                            <input type="hidden" name="item_id" value="<?php print $value['item_id'] ?>">
                            <input type="hidden" name="process_kind" value="delete">
                            <input type="image" class="img" src="img/icon/delete.png" alt="削除">
                        </td>
                    </form>
                    <td width="140px"><?php print $value['price']; ?>円</td>
                    <form method="post" enctype="multipart/form-data">
                        <td width="150px">
                            <input type="text" name="change_amount" value="<?php print $value['amount']; ?>">個
                            <input type="hidden" name="item_id" value="<?php print $value['item_id'] ?>">
                            <input type="hidden" name="process_kind" value="change_amount">
                        </td>
                        <td width="60px"><input type="image" class="img" src="img/icon/change.png" alr="変更"></td>
                    </form>
                </tr>
                <?php } ?>
            </table>
            <section class="total">
                <p>合計</p>
                <?php foreach ($data as $value) {
                    $total += $value['price'] * $value['amount'];
                } ?>
                <p><?php print $total; ?>円</p>
                <form method="post" action="./result.php">
                    <input type="image" src="img/icon/buy.png" alt="購入する">
                </form>
            </section>
        </main>
    </body>
</html>