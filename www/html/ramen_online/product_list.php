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
$data_check   = '';
$data         = array();
$data_rank    = array();
$err_msg      = array();     // エラーメッセージ
$result_msg   = array();
$same_product = array();

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

foreach ($prefectures as $value) {
    $key = array_search($value, $prefectures);
        ${'data' . $key} = array();
}

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
    // buy_item_idの定義とエラーチェック
    if (isset($_POST['buy_item_id']) === TRUE) {
        $buy_item_id = htmlspecialchars($_POST['buy_item_id'], ENT_QUOTES, 'UTF-8');
        if (preg_match("/^[0-9]+$/", $buy_item_id) !== 1) {
            $err_msg[] = 'エラー!：不正な値です';
        }
    }
    
    // 在庫及び商品の有無を確認
    try {
    // データベースに接続
    $dbh = new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
      
    if (count($err_msg) === 0) {
        //
        // 在庫数及び商品の有無を取得
        //
        // SQL文を作成
        $sql = 'SELECT ec_item_master.item_id, ec_item_master.status, ec_item_stock.stock
            FROM ec_item_master INNER JOIN ec_item_stock ON ec_item_master.item_id = ec_item_stock.item_id
            WHERE ec_item_master.item_id = ?';
        // SQL文を実行する準備
        $stmt = $dbh->prepare($sql);
        // SQL文のプレースホルダーに値をバインド
        $stmt->bindValue(1, $buy_item_id, PDO::PARAM_STR);
        // SQLを実行
        $stmt->execute();
        // レコードの取得
        $rows = $stmt->fetchAll();
        // 1行ずつ結果を配列で取得
        foreach ($rows as $row) {
            $data_check = $row;
        }
        
        if (isset($data_check['item_id']) === false) {
            $err_msg[] = 'エラー!：商品が存在しません。';
        } else {
            if ($data_check['status'] !== 1) {
                $err_msg[] = 'エラー!：現在販売できません。';
            }
            if ($data_check['stock'] === 0) {
                $err_msg[] = 'エラー!：売り切れました。';
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
        
        if (count($err_msg) === 0) {
            //
            // カート内に同じ商品がないか確認
            //
            // SQL文を作成
            $sql = 'SELECT item_id FROM ec_cart WHERE user_id =? AND item_id = ?';
            // SQL文を実行する準備
            $stmt = $dbh->prepare($sql);
            // SQL文のプレースホルダーに値をバインド
            $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
            $stmt->bindValue(2, $buy_item_id, PDO::PARAM_STR);
            // SQLを実行
             $stmt->execute();
            // レコードの取得
            $rows = $stmt->fetchAll();
            // 1行ずつ結果を配列で取得
            foreach ($rows as $row) {
                $same_product[] = $row;
            }
            
            // カート内に同じユーザが注文した同じ商品がなければ
            if (count($same_product) === 0) {
                // 変数を定義
                $create_datetime = date('Y-m-d H:i:s');
                //
                // カートに商品追加
                //
                // SQL文を作成
                $sql = 'INSERT INTO ec_cart(user_id, item_id, amount, create_datetime, update_datetime) VALUES(?, ?, ?, ?, ?)';
                // SQL文を実行する準備
                $stmt = $dbh->prepare($sql);
                // SQL文のプレースホルダーに値をバインド
                $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
                $stmt->bindValue(2, $buy_item_id, PDO::PARAM_INT);
                $stmt->bindValue(3, '1', PDO::PARAM_INT);
                $stmt->bindValue(4, $create_datetime, PDO::PARAM_STR);
                $stmt->bindValue(5, $create_datetime, PDO::PARAM_STR);
                // SQLを実行
                $stmt->execute();
                $result_msg[] = 'カートに商品を追加しました。';
                
            // カート内に同じユーザが注文した同じ商品があれば 
            }  else if (count($same_product) !== 0) {
                // 変数を定義
                $create_datetime = date('Y-m-d H:i:s');
                //
                // カートに商品追加
                //
                // SQL文を作成
                $sql = 'UPDATE ec_cart SET amount = amount + 1, update_datetime = ? WHERE user_id =? AND item_id = ?';
                // SQL文を実行する準備
                $stmt = $dbh->prepare($sql);
                // SQL文のプレースホルダーに値をバインド
                $stmt->bindValue(1, $create_datetime, PDO::PARAM_STR);
                $stmt->bindValue(2, $user_id, PDO::PARAM_INT);
                $stmt->bindValue(3, $buy_item_id, PDO::PARAM_INT);
                // SQLを実行
                $stmt->execute();
                $result_msg[] = 'カートに商品を追加しました。';
            }
        }    
    } catch (PDOException $e) {
      // 接続失敗した場合
      $err_msg['db_connect'] = 'DBエラー：'.$e->getMessage();
    }   
}

// ランキング情報を取得
try {
    // データベースに接続
    $dbh = new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
      
    if (count($err_msg) === 0) {

        //
        // item_id毎の売れた個数を合計して、上位３つを配列で取得
        //
        // SQL文を作成
        $sql = 'SELECT item_id, SUM(amount) FROM ec_history GROUP BY item_id ORDER BY SUM(amount) DESC';
        // SQL文を実行する準備
        $stmt = $dbh->prepare($sql);
        // SQLを実行
        $stmt->execute();
        // レコードの取得
        $rows = $stmt->fetchAll();
        // 結果を変数で定義
        foreach ($rows as $row) {
            $data[] = $row;
        }
        // 上から３つのみ取得
        $data = array_slice($data, 0,3);
        
        //
        // 上位３つの商品情報を取得
        //
        foreach ($data as $value) {
            // SQL文を作成
            $sql =$sql = 'SELECT ec_item_master.item_id, ec_item_master.name, ec_item_master.prefecture, ec_item_master.area, ec_item_master.price, ec_item_master.img, ec_item_master.status, ec_item_stock.stock
                FROM ec_item_master INNER JOIN ec_item_stock ON ec_item_master.item_id = ec_item_stock.item_id
                WHERE ec_item_master.status = 1 AND ec_item_master.item_id = ?';
            // SQL文を実行する準備
            $stmt = $dbh->prepare($sql);
            // SQL文のプレースホルダーに値をバインド
            $stmt->bindValue(1, $value['item_id'], PDO::PARAM_INT);
            // SQLを実行
            $stmt->execute();
            // レコードの取得
            $rows = $stmt->fetchAll();
            // 結果を変数で定義
            foreach ($rows as $row) {
                $data_rank[] = $row;
            }
        }
    }
} catch (PDOException $e) {
    // 接続失敗した場合
    $err_msg['db_connect'] = 'DBエラー：'.$e->getMessage();
}    

// 商品リストを取得
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
    // 商品情報を取得
    //
    // SQL文を作成
    foreach ($prefectures as $value) {
        $sql = 'SELECT ec_item_master.item_id, ec_item_master.name, ec_item_master.prefecture, ec_item_master.area, ec_item_master.price, ec_item_master.img, ec_item_master.status, ec_item_stock.stock
            FROM ec_item_master INNER JOIN ec_item_stock ON ec_item_master.item_id = ec_item_stock.item_id
            WHERE ec_item_master.status = 1 AND ec_item_master.prefecture = ?';
        // SQL文を実行する準備
        $stmt = $dbh->prepare($sql);
        // SQL文のプレースホルダーに値をバインド
        $stmt->bindValue(1, $value, PDO::PARAM_STR);
        // SQLを実行
        $stmt->execute();
        // レコードの取得
        $rows = $stmt->fetchAll();
        // 1行ずつ結果を配列で取得
        $key = array_search($value, $prefectures);
        foreach ($rows as $row) {
            ${'data' . $key}[] = $row;
        }
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
        <link rel="stylesheet" href="ecsite_list.css">
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
            <article>
                <?php if (count($data) >= 3) { ?> 
                    <p class="title">人気ラーメンTOP3★</p>
                    <section class="section-container-rank-number">
                    <?php for ($i=1; $i <= 3; $i++) { ?>    
                        <div class="div-container-rank-number">
                            <p><?php print $i; ?>位</p>
                        </div>
                    <?php } ?>
                <?php } ?>
                </section>
                <section class="section-container-rank-product">
                <?php foreach ($data_rank as $value) { ?>    
                    <div class="div-container-rank-product">
                        <p><?php print $value['prefecture']; ?></p>
                    </div>
                <?php } ?>
                </section>
                <section>
                    <?php foreach ($data_rank as $value) { ?>
                    <div class="section-container-product">
                        <p>商品名：<?php print $value['name']; ?></p>
                        <p>市区郡：<?php print $value['area']; ?></p>
                        <img src="<?php print $img_dir . $value['img']; ?>">
                        <div class="item">
                            <p><?php print $value['price'] . '円'; ?></p>
                            <form method="post" enctype="multipart/form-data">
                                <input type="hidden" name="buy_item_id" value="<?php print $value['item_id']; ?>">
                                <?php if ($value['stock'] !== 0) { ?>
                                    <input type="image" src="img/icon/cart_add.png" alt="送信する">
                                <?php } else { ?>
                                    <p>売り切れ</p>
                                <?php } ?>
                            </form>
                        </div>
                    </div>
                    <?php } ?>
                </section>
                <p class="title">都道府県</p>
                <section>
                    <table>
                        <tr>
                            <th class="background-color-th">北海道</th>
                            <td><a href="#0">北海道</a></td>
                        </tr>
                        <tr>
                            <th>東北</th>
                            <td class="background-color-td"><a href="#1">青森</a> <a href="#2">岩手</a> <a href="#3">秋田</a> <a href="#4">宮城</a> <a href="#5">山形</a> <a href="#6">福島</a></td>
                        </tr>
                        <tr>
                            <th class="background-color-th">関東</th>
                            <td><a href="#7">茨城</a> <a href="#8">栃木</a> <a href="#9">群馬</a> <a href="#10">埼玉</a> <a href="#11">千葉</a> <a href="#12">神奈川</a> <a href="#13">東京</a></td>
                        </tr>
                        <tr>
                            <th>中部</th>
                            <td class="background-color-td"><a href="#14">新潟</a> <a href="#15">富山</a> <a href="#16">石川</a> <a href="#17">福井</a> <a href="#18">山梨</a> <a href="#19">長野</a> <a href="#20">岐阜</a> <a href="#21">静岡</a> <a href="#22">愛知</a></td>
                        </tr>
                        <tr>
                            <th class="background-color-th">近畿</th>
                            <td><a href="#23">三重</a> <a href="#24">滋賀</a> <a href="#25">京都</a> <a href="#26">大阪</a> <a href="#27">兵庫</a> <a href="#28">奈良</a> <a href="#29">和歌山</a></td>
                        </tr>
                        <tr>
                            <th>中国</th>
                            <td class="background-color-td"><a href="#30">鳥取</a> <a href="#31">島根</a> <a href="#32">岡山</a> <a href="#33">広島</a> <a href="#34">山口</a></td>
                        </tr>
                        <tr>
                            <th class="background-color-th">四国</th>
                            <td><a href="#35">徳島</a> <a href="#36">香川</a> <a href="#37">愛媛</a> <a href="#38">高知</a></td>
                        </tr>
                        <tr>
                            <th>九州</th>
                            <td class="background-color-td"><a href="#39">福岡</a> <a href="#40">佐賀</a> <a href="#41">長崎</a> <a href="#42">熊本</a> <a href="#43">大分</a> <a href="#44">宮崎</a> <a href="#45">鹿児島</a> <a href="#46">沖縄</a></td>
                        </tr>
                    </table>
                </section>
                <p class="title">商品一覧</p>
                <?php foreach ($prefectures as $value_pre) { ?>
                <?php $key = array_search($value_pre, $prefectures); ?>
                <p id="<?php print $key; ?>" class="prefecture"><?php print $value_pre; ?></p>
                <section>
                    <?php foreach (${'data' . $key} as $value) { ?>
                    <div class="section-container-product">
                        <p>商品名：<?php print $value['name']; ?></p>
                        <p>市区郡：<?php print $value['area']; ?></p>
                        <img src="<?php print $img_dir . $value['img']; ?>">
                        <div class="item">
                            <p><?php print $value['price'] . '円'; ?></p>
                            <form method="post" enctype="multipart/form-data">
                                <input type="hidden" name="buy_item_id" value="<?php print $value['item_id']; ?>">
                                <?php if ($value['stock'] !== 0) { ?>
                                    <input type="image" src="img/icon/cart_add.png" alt="カートに追加">
                                <?php } else { ?>
                                    <p>売り切れ</p>
                                <?php } ?>
                            </form>
                        </div>
                    </div>
                    <?php } ?>
                </section>
                <?php } ?>
            </article>
        </main>
    </body>
</html>