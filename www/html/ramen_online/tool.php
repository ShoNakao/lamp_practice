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
 
$img_dir    = './img/product/';    // アップロードした画像ファイルの保存ディレクトリ
$data       = array();
$err_msg    = array();     // エラーメッセージ
$result_msg = array();    // 完了メッセージ 
$new_img_filename = '';   // アップロードした新しい画像ファイル名

$name  = '';
$area  = '';
$price = '';
$stock = '';
$prefecture = '';

// 都道府県を配列で定義
$prefectures = array(
'北海道',
'青森',
'岩手',
'宮城',
'秋田',
'山形',
'福島',
'茨城',
'栃木',
'群馬',
'埼玉',
'千葉',
'東京',
'神奈川',
'新潟',
'富山',
'石川',
'福井',
'山梨',
'長野',
'岐阜',
'静岡',
'愛知',
'三重',
'滋賀',
'京都',
'大阪',
'兵庫',
'奈良',
'和歌山',
'鳥取',
'島根',
'岡山',
'広島',
'山口',
'徳島',
'香川',
'愛媛',
'高知',
'福岡',
'佐賀',
'長崎',
'熊本',
'大分',
'宮崎',
'鹿児島',
'沖縄',
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 if (isset($_POST['process_kind']) === TRUE) {
    $process_kind = $_POST['process_kind'];
  }
}  

// 商品追加ボタンが押された場合
// ドリンク名・値段のチェック・定義 アップロード画像ファイルの保存
if (isset($process_kind) === TRUE && $process_kind === 'insert_item') {
  if (isset($_POST['name']) === TRUE ) {
    $name = trim($_POST['name']);        //trimは文字列の先頭および末尾にあるホワイトスペースを取り除く
    $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
  }
  if (isset($_POST['price']) === TRUE) {
    $price = trim($_POST['price']);        //trimは文字列の先頭および末尾にあるホワイトスペースを取り除く
    $price = htmlspecialchars($price, ENT_QUOTES, 'UTF-8');
  }
  if (isset($_POST['stock']) === TRUE) {
    $stock = trim($_POST['stock']);          //trimは文字列の先頭および末尾にあるホワイトスペースを取り除く
    $stock = htmlspecialchars($stock, ENT_QUOTES, 'UTF-8');
  }
  if (isset($_POST['status']) === TRUE) {
    $status = $_POST['status'];
    $status = htmlspecialchars($status, ENT_QUOTES, 'UTF-8');
  }
  if (isset($_POST['prefecture']) === TRUE) {
    if (preg_match("/^[0-9]$/", $_POST['prefecture']) === 1 || preg_match("/^[1-3][0-9]$/", $_POST['prefecture']) === 1 || preg_match("/^4[0-6]$/", $_POST['prefecture']) === 1 ) {
      $prefecture = $prefectures[$_POST['prefecture']];
      $prefecture = htmlspecialchars($prefecture, ENT_QUOTES, 'UTF-8');
    } else {
      $err_msg[] = 'エラー！：不正な値です。';
    }
  }
  if (isset($_POST['area']) === TRUE) {
    $area = $_POST['area'];
    $area = htmlspecialchars($area, ENT_QUOTES, 'UTF-8');
  }
  if ($name === '') {
    $err_msg[] = 'エラー!：商品名を入力してください。';
  }
  if ($price === '') {
    $err_msg[] = 'エラー!：価格を入力してください。';
  } else if (preg_match("/^[0-9]+$/", $price) !== 1) {
    $err_msg[] = 'エラー!：値段は半角数値で入力してください。';
  }
  if ($stock === '') {
    $err_msg[] = 'エラー!：個数を入力してください。';
  } else if (preg_match("/^[0-9]+$/", $stock) !== 1) {
    $err_msg[] = 'エラー!：個数は半角数値で入力してください。';
  }
  if ($status !== '0' && $status !== '1') {
    $err_msg[] = 'エラー！：不正な値です。';
  }
  if ($area === '') {
    $err_msg[] = 'エラー!：市区郡を入力してください。';
  }
  
  // エラーメッセージが0の場合画像の処理に入る
  if (count($err_msg) === 0) {
    // HTTP POST でファイルがアップロードされたかどうかチェック
    if (is_uploaded_file($_FILES['new_img']['tmp_name']) === TRUE) {
      // 画像の拡張子を取得
      $extension = pathinfo($_FILES['new_img']['name'], PATHINFO_EXTENSION);
      // 小文字に直す
      $extension = strtolower($extension);
      // 指定の拡張子であるかどうかチェック
      if ($extension === 'jpg' || $extension === 'jpeg' || $extension === 'png') {
        // 保存する新しいファイル名の生成（ユニークな値を設定する）
        $new_img_filename = sha1(uniqid(mt_rand(), true)). '.' . $extension;
        // 同名ファイルが存在するかどうかチェック
        if (is_file($img_dir . $new_img_filename) !== TRUE) {
          // アップロードされたファイルを指定ディレクトリに移動して保存
          if ((move_uploaded_file($_FILES['new_img']['tmp_name'], $img_dir . $new_img_filename) !== TRUE)) {
              $err_msg[] = 'ファイルアップロードに失敗しました';
          }
        } else {
          $err_msg[] = 'ファイルアップロードに失敗しました。再度お試しください。';
        }
      } else {
        $err_msg[] = 'ファイル形式が異なります。画像ファイルはJPEGまたはPNGのみ利用可能です。';
      }
    } else {
      $err_msg[] = 'ファイルを選択してください';
    }
  }

  // 情報の入力
  try {
    // データベースに接続
    $dbh = new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
   
    // トランザクション開始
    $dbh->beginTransaction();
    // エラーがなければ、アップロードした新しい画像ファイル名及び名前・値段・日時を保存
    if (count($err_msg) === 0) {
      try {
        // 現在日時を取得
        $datetime = date('Y-m-d H:i:s');
        //
        // ドリンク情報テーブルにデータ作成
        //
        // SQL文を作成
        $sql = 'INSERT INTO ec_item_master(name, prefecture, area, price, img, status, create_datetime, update_datetime) VALUES(?, ?, ?, ?, ?, ?, ?, ?)';
        // SQL文を実行する準備
        $stmt = $dbh->prepare($sql);
        // SQL文のプレースホルダに値をバインド
        $stmt->bindValue(1, $name, PDO::PARAM_STR);
        $stmt->bindValue(2, $prefecture, PDO::PARAM_STR);
        $stmt->bindValue(3, $area, PDO::PARAM_STR);
        $stmt->bindValue(4, $price, PDO::PARAM_INT);
        $stmt->bindValue(5, $new_img_filename, PDO::PARAM_STR);
        $stmt->bindValue(6, $status, PDO::PARAM_INT);
        $stmt->bindValue(7, $datetime, PDO::PARAM_STR);
        $stmt->bindValue(8, $datetime, PDO::PARAM_STR);
         // SQLを実行
        $stmt->execute();
        
        // drink_idの値を取得
        $id = $dbh->lastInsertId('item_id');
        
        //
        // 在庫情報テーブルにデータ作成
        //
        // SQL文を作成
        $sql = 'INSERT INTO ec_item_stock(item_id, stock, create_datetime, update_datetime) VALUES(?, ?, ?, ?)';
        // SQL文を実行する準備
        $stmt = $dbh->prepare($sql);
        // SQL文のプレースホルダーに値をバインド
        $stmt->bindValue(1, $id, PDO::PARAM_INT);
        $stmt->bindValue(2, $stock, PDO::PARAM_INT);
        $stmt->bindValue(3, $datetime, PDO::PARAM_STR);
        $stmt->bindValue(4, $datetime, PDO::PARAM_STR);
        // SQLを実行
        $stmt->execute();
        
        // コミット処理
        $dbh->commit();
        $result_msg[] = '商品を追加しました';
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

// 在庫数変更ボタンが押された場合
} else if (isset($process_kind) === TRUE && $process_kind === 'update_stock') {
    if (isset($_POST['update_stock']) === TRUE ) {
      $update_stock = trim($_POST['update_stock']);          //trimは文字列の先頭および末尾にあるホワイトスペースを取り除く
      $update_stock = htmlspecialchars($update_stock, ENT_QUOTES, 'UTF-8');
    }
    if ($update_stock === '') {
      $err_msg[] = 'エラー!：個数を入力してください。';
    } else if (preg_match("/^[0-9]+$/", $update_stock) !== 1) {
        $err_msg[] = 'エラー!：個数は半角数値で入力してください。';
    }
    
    // 在庫情報の更新
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
          // 在庫情報テーブルを更新
          //
          // SQL文を作成
          $sql = 'UPDATE ec_item_stock SET stock = ?, update_datetime = ? WHERE item_id = ?';
          // SQL文を実行する準備
          $stmt = $dbh->prepare($sql);
          // SQL文のプレースホルダーに値をバインド
          $stmt->bindValue(1, $update_stock, PDO::PARAM_INT);
          $stmt->bindValue(2, $update_datetime, PDO::PARAM_STR);
          $stmt->bindValue(3, $update_id, PDO::PARAM_INT);
          // SQLを実行
          $stmt->execute();
          $result_msg[] = '在庫数を更新しました';
      }  
    } catch (PDOException $e) {
      // 接続失敗した場合
      $err_msg['db_connect'] = 'DBエラー：'.$e->getMessage();
    }  

// ステータス変更ボタンが押された場合    
} else if (isset($process_kind) === TRUE && $process_kind === 'change_status') {
    if (isset($_POST['change_status']) === TRUE) {
        $update_status = $_POST['change_status'];
        $update_status = htmlspecialchars($update_status, ENT_QUOTES, 'UTF-8');
    }
    if ($update_status !== '0' && $update_status !== '1') {
        $err_msg[] = 'エラー！：不正な値です。';
    }
    //ステータスの更新
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
        // 在庫情報テーブルを更新
        //
        // SQL文を作成
        $sql = 'UPDATE ec_item_master SET status = ?, update_datetime = ? WHERE item_id = ?';
        // SQL文を実行する準備
        $stmt = $dbh->prepare($sql);
        // SQL文のプレースホルダーに値をバインド
        $stmt->bindValue(1, $update_status, PDO::PARAM_INT);
        $stmt->bindValue(2, $update_datetime, PDO::PARAM_STR);
        $stmt->bindValue(3, $update_id, PDO::PARAM_INT);
        // SQLを実行
        $stmt->execute();
        $result_msg[] = 'ステータスを更新しました';
        
        //
        // 非公開にする場合はカート内の商品を削除
        //
        if ($update_status === 0) {
          $sql = 'DELETE FROM ec_cart WHERE item_id = ?';
          // SQL文を実行する準備
          $stmt = $dbh->prepare($sql);
          // SQL文のプレースホルダーに値をバインド
          $stmt->bindValue(1, $update_id, PDO::PARAM_INT);
          // SQLを実行
          $stmt->execute();
        }
      }    
    } catch (PDOException $e) {
      // 接続失敗した場合
      $err_msg['db_connect'] = 'DBエラー：'.$e->getMessage();
    }

// 削除ボタンが押された場合
} else if (isset($process_kind) === TRUE && $process_kind === 'delete') {
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
          $sql = 'DELETE FROM ec_item_master WHERE item_id = ?';
          $sql_1 = 'DELETE FROM ec_item_stock WHERE item_id = ?';
          $sql_2 = 'DELETE FROM ec_cart WHERE item_id = ?';
          // SQL文を実行する準備
          $stmt = $dbh->prepare($sql);
          $stmt_1 = $dbh->prepare($sql_1);
          $stmt_2 = $dbh->prepare($sql_2);
          // SQL文のプレースホルダーに値をバインド
          $stmt->bindValue(1, $delete_id, PDO::PARAM_INT);
          $stmt_1->bindValue(1, $delete_id, PDO::PARAM_INT);
          $stmt_2->bindValue(1, $delete_id, PDO::PARAM_INT);
          // SQLを実行
          $stmt->execute();
          $stmt_1->execute();
          $stmt_2->execute();
          $result_msg[] = '商品を削除しました';
      }    
    } catch (PDOException $e) {
      // 接続失敗した場合
      $err_msg['db_connect'] = 'DBエラー：'.$e->getMessage();
    }
}

// テーブルの値の取得
try {
  // データベースに接続
  $dbh = new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
  //
  // 登録されている情報の取得
  //
  // SQL文を作成
  $sql = 'SELECT ec_item_master.item_id, ec_item_master.name, ec_item_master.prefecture, ec_item_master.area, ec_item_master.price, ec_item_master.img, ec_item_master.status,  ec_item_stock.stock
            FROM `ec_item_master` INNER JOIN ec_item_stock ON ec_item_master.item_id = ec_item_stock.item_id';
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
 
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>商品管理ページ</title>
  <style>
    
    .background_gray {
        background-color: gray;
    }
    table {
      width: 1500px;
      border-collapse: collapse;
    }
    table, tr, th, td {
      border: solid 1px;
      padding: 10px;
      text-align: center;
    }
    th, td {
        width: 300px;
    }
    div {
      margin: 10px;
    }
    .stock {
      text-align: right;
    }
    img {
        height: 100px;
    }
  </style>
</head>
<body>
<?php foreach ($err_msg as $value) { ?>
  <p><?php print $value; ?></p>
<?php } ?>
<?php foreach ($result_msg as $value) { ?>
  <p><?php print $value; ?></p>
<?php } ?>
  <h1>ラーメンOnline 管理ページ</h1>
  <a href="./user.php">ユーザ管理ページ</a><hr>
  <form method="post" enctype="multipart/form-data">
    <h2>新規商品追加</h2>
    <div><label>商品名：<input type="text" name="name" value="<?php if(count($err_msg) !== 0) print $name ?>"></label><br></div>
    <div><label>都道府県：
      <select name="prefecture">
      <?php
      foreach ($prefectures as $key => $prefecture) {
      ?>
      <option value="<?php print $key; ?>"><?php print $prefecture;?></option>
      <?php
      }
      ?>
      </select>
    </label><br></div>
    <div><label>市区郡：<input type="text" name="area" value="<?php if(count($err_msg) !== 0) print $area ?>"></label><br></div>
    <div><label>価格：<input type="text" name="price" value="<?php if(count($err_msg) !== 0) print $price ?>"></label><br></div>
    <div><label>個数：<input type="text" name="stock" value="<?php if(count($err_msg) !== 0) print $stock ?>"></label><br></div>
    <div><label>商品画像：<input type="file" name="new_img"></label></div>
    <div><label>ステータス：
      <select name="status">
        <option value="0">非公開</option>
        <option value="1">公開</option>
      </select>
    </label><br></div>
    <input type="hidden" name="process_kind" value="insert_item">
    <div><input type="submit" value="■□■□■商品を追加■□■□■"></div><hr>
  </form>
  <table>
    <h2>商品情報変更</h2>
    <p>商品一覧</p>
    <tr>
      <th>商品画像</th>
      <th>商品名</th>
      <th>都道府県</th>
      <th>市区郡</th>
      <th>価格</th>
      <th>在庫数</th>
      <th>ステータス</th>
      <th>操作</th>
    </tr>
<?php foreach ($data as $value)  { ?>
  <tr class="<?php if ($value['status'] === 0) print 'background_gray' ?>">
    <td><img src="<?php print $img_dir . $value['img']; ?>"></td>
    <td><?php print $value['name'] ?></td>
    <td><?php print $value['prefecture'] ?></td>
    <td><?php print $value['area'] ?></td>
    <td><?php print $value['price'] . '円' ?></td>
    <td><form method="post" enctype="multipart/form-data">
        <label><input class="stock" type="text" name="update_stock" value="<?php print $value['stock'] ?>">個 </label>
        <input type="hidden" name="item_id" value="<?php print $value['item_id'] ?>">
        <input type="hidden" name="process_kind" value="update_stock"><br>
        <input type="submit" value="変更">
    </form></td>
    <td><form method="post" enctype="multipart/form-data">
        <input type="hidden" name="item_id" value="<?php print $value['item_id'] ?>">
        <input type="hidden" name="change_status" value="<?php if ($value['status'] === 0) { print 1; } else { print 0; } ?>">
        <input type="hidden" name="process_kind" value="change_status">
        <input type="submit" value="<?php if ($value['status'] === 0) { print '非公開 → 公開'; } else { print '公開 → 非公開'; } ?>">
    </form></td>
    <td><form method="post" enctype="multipart/form-data">
        <input type="hidden" name="item_id" value="<?php print $value['item_id'] ?>">
        <input type="hidden" name="process_kind" value="delete">
        <input type="submit" value="削除">
    </form></td>
  <tr>
<?php } ?>
  </table>
</body>
</html>