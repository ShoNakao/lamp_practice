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
$prefecture   = array();
$total        = 0;

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

// 購入履歴情報取得
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
    // 購入履歴内のアクセスしたユーザが購入した商品情報を取得
    //
    // SQL文を作成
    $sql = 'SELECT ec_history.item_id, ec_history.amount, ec_history.create_datetime, ec_item_master.name, ec_item_master.prefecture, ec_item_master.price, ec_item_master.img
        FROM ec_history INNER JOIN ec_item_master ON ec_history.item_id = ec_item_master.item_id
        WHERE ec_history.user_id = ?';
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
} catch (PDOException $e) {
    // 接続失敗した場合
    $err_msg['db_connect'] = 'DBエラー：'.$e->getMessage();
}

// 都道府県制覇数を計算
foreach ($data as $value) {
    $prefecture[] = $value['prefecture'];
}
$unique = array_unique($prefecture);
$prefecture_count = count($unique);

// $unique内の都道府県のキーを取得


?>

<?php foreach ($unique as $value) {
                $key = array_search($value, $prefectures); 
}
$ai = 1;
            ?>
<!doctype html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <title>ラーメンOnline</title>
        <link rel="stylesheet" href="html5reset-1.6.1.css">
        <link rel="stylesheet" href="ecsite_history.css">
    </head>
    <body>
        <header>
            <div>
                <div id="header_left">
                    <a href="./product_list.php"><p id="ai">ラーメンOnline</p></a>
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
        </section>
        <main>
            <p class="title">購入履歴</p>
            <p class="complete">制覇数　<?php print $prefecture_count ?>/47</p>
            <section>
                <table class="prefecture">
                    <tr>
                        <th class="background-color-th">北海道</th>
                        <td>
                            <p class="<?php if(in_array('北海道',$unique)) { print 'complete_item'; } ?>">北海道</p>
                        </td>
                    </tr>
                    <tr>
                        <th>東北</th>
                        <td class="background-color-td">
                            <span class="<?php if(in_array('青森',$unique)) { print 'complete_item'; } ?>">青森 </span>
                            <span class="<?php if(in_array('岩手',$unique)) { print 'complete_item'; } ?>">岩手 </span>
                            <span class="<?php if(in_array('秋田',$unique)) { print 'complete_item'; } ?>">秋田 </span>
                            <span class="<?php if(in_array('宮城',$unique)) { print 'complete_item'; } ?>">宮城 </span>
                            <span class="<?php if(in_array('山形',$unique)) { print 'complete_item'; } ?>">山形 </span>
                            <span class="<?php if(in_array('福島',$unique)) { print 'complete_item'; } ?>">福島 </span>
                        </td>
                    </tr>
                    <tr>
                        <th class="background-color-th">関東</th>
                        <td>
                            <span class="<?php if(in_array('茨城',$unique)) { print 'complete_item'; } ?>">茨城 </span>
                            <span class="<?php if(in_array('栃木',$unique)) { print 'complete_item'; } ?>">栃木 </span>
                            <span class="<?php if(in_array('群馬',$unique)) { print 'complete_item'; } ?>">群馬 </span>
                            <span class="<?php if(in_array('埼玉',$unique)) { print 'complete_item'; } ?>">埼玉 </span>
                            <span class="<?php if(in_array('千葉',$unique)) { print 'complete_item'; } ?>">千葉 </span>
                            <span class="<?php if(in_array('神奈川',$unique)) { print 'complete_item'; } ?>">神奈川 </span>
                            <span class="<?php if(in_array('東京',$unique)) { print 'complete_item'; } ?>">東京 </span>
                        </td>
                    </tr>
                    <tr>
                        <th>中部</th>
                        <td class="background-color-td">
                            <span class="<?php if(in_array('新潟',$unique)) { print 'complete_item'; } ?>">新潟 </span>
                            <span class="<?php if(in_array('富山',$unique)) { print 'complete_item'; } ?>">富山 </span>
                            <span class="<?php if(in_array('石川',$unique)) { print 'complete_item'; } ?>">石川 </span>
                            <span class="<?php if(in_array('福井',$unique)) { print 'complete_item'; } ?>">福井 </span>
                            <span class="<?php if(in_array('山梨',$unique)) { print 'complete_item'; } ?>">山梨 </span>
                            <span class="<?php if(in_array('長野',$unique)) { print 'complete_item'; } ?>">長野 </span>
                            <span class="<?php if(in_array('岐阜',$unique)) { print 'complete_item'; } ?>">岐阜 </span>
                            <span class="<?php if(in_array('静岡',$unique)) { print 'complete_item'; } ?>">静岡 </span>
                            <span class="<?php if(in_array('愛知',$unique)) { print 'complete_item'; } ?>">愛知 </span>
                        </td>
                    </tr>
                    <tr>
                        <th class="background-color-th">近畿</th>
                        <td>
                            <span class="<?php if(in_array('三重',$unique)) { print 'complete_item'; } ?>">三重 </span>
                            <span class="<?php if(in_array('滋賀',$unique)) { print 'complete_item'; } ?>">滋賀 </span>
                            <span class="<?php if(in_array('京都',$unique)) { print 'complete_item'; } ?>">京都 </span>
                            <span class="<?php if(in_array('大阪',$unique)) { print 'complete_item'; } ?>">大阪 </span>
                            <span class="<?php if(in_array('兵庫',$unique)) { print 'complete_item'; } ?>">兵庫 </span>
                            <span class="<?php if(in_array('奈良',$unique)) { print 'complete_item'; } ?>">奈良 </span>
                            <span class="<?php if(in_array('和歌山',$unique)) { print 'complete_item'; } ?>">和歌山 </span>
                        </td>
                    </tr>
                    <tr>
                        <th>中国</th>
                        <td class="background-color-td">
                            <span class="<?php if(in_array('鳥取',$unique)) { print 'complete_item'; } ?>">鳥取 </span>
                            <span class="<?php if(in_array('島根',$unique)) { print 'complete_item'; } ?>">島根 </span>
                            <span class="<?php if(in_array('岡山',$unique)) { print 'complete_item'; } ?>">岡山 </span>
                            <span class="<?php if(in_array('広島',$unique)) { print 'complete_item'; } ?>">広島 </span>
                            <span class="<?php if(in_array('山口',$unique)) { print 'complete_item'; } ?>">山口 </span>
                        </td>
                    </tr>
                    <tr>
                        <th class="background-color-th">四国</th>
                        <td>
                            <span class="<?php if(in_array('徳島',$unique)) { print 'complete_item'; } ?>">徳島 </span>
                            <span class="<?php if(in_array('香川',$unique)) { print 'complete_item'; } ?>">香川 </span>
                            <span class="<?php if(in_array('愛媛',$unique)) { print 'complete_item'; } ?>">愛媛 </span>
                            <span class="<?php if(in_array('高知',$unique)) { print 'complete_item'; } ?>">高知 </span>
                        </td>
                    </tr>
                    <tr>
                        <th>九州</th>
                        <td class="background-color-td">
                            <span class="<?php if(in_array('福岡',$unique)) { print 'complete_item'; } ?>">福岡 </span>
                            <span class="<?php if(in_array('佐賀',$unique)) { print 'complete_item'; } ?>">佐賀 </span>
                            <span class="<?php if(in_array('長崎',$unique)) { print 'complete_item'; } ?>">長崎 </span>
                            <span class="<?php if(in_array('熊本',$unique)) { print 'complete_item'; } ?>">熊本 </span>
                            <span class="<?php if(in_array('大分',$unique)) { print 'complete_item'; } ?>">大分 </span>
                            <span class="<?php if(in_array('宮崎',$unique)) { print 'complete_item'; } ?>">宮崎 </span>
                            <span class="<?php if(in_array('鹿児島',$unique)) { print 'complete_item'; } ?>">鹿児島 </span>
                            <span class="<?php if(in_array('沖縄',$unique)) { print 'complete_item'; } ?>">沖縄 </span>
                        </td>
                    </tr>
                </table>
            </section>
            <table class="history">
                <?php foreach ($data as $value) { ?>
                <tr>
                    <td width="220px"><img src="<?php print $img_dir . $value['img']; ?>"></td>
                    <td width="140px"><?php print $value['name']; ?></td>
                    <td width="140px"><?php print $value['prefecture']; ?></td>
                    <td width="140px"><?php print date('Y/m/d', strtotime($value['create_datetime'])); ?></td>
                    <td width="150px"><?php print $value['price']; ?>円</td>
                    <td width="60px"><?php print $value['amount']; ?>個</td>
                </tr>
                <?php } ?>
            </table>    
        </main>
    </body>
</html>