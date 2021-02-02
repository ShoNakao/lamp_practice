<?php
// 関数を定義したファイルを読み込み
require_once MODEL_PATH . 'functions.php';
// dbに関するファイルを
require_once MODEL_PATH . 'db.php';

// DB利用

// 特定の商品の商品情報を取得
function get_item($db, $item_id){
  $sql = "
    SELECT
      item_id, 
      name,
      stock,
      price,
      image,
      status
    FROM
      items
    WHERE
      item_id = ?
  ";

  // sqlを実行してdbから１行の結果を取得
  return fetch_query($db, $sql, [$item_id]);
}

// 商品情報を取得
function get_items($db, $is_open = false){
  $sql = '
    SELECT
      item_id, 
      name,
      stock,
      price,
      image,
      status
    FROM
      items
  ';
  if($is_open === true){
    $sql .= '
      WHERE status = 1
    ';
  }
  // sqlを実行して結果を返す
  return fetch_all_query($db, $sql);
}

// 全ての商品情報を取得
function get_all_items($db){
  // 全ての商品情報を返す
  return get_items($db);
}

// 公開されている商品情報を取得
function get_open_items($db){
  // (item_id,name,stock,price,image,status)を返す
  return get_items($db, true);
}

// 登録する商品情報の妥当性チェック及び登録
function regist_item($db, $name, $price, $stock, $status, $image){
  // アップロードされた画像にバリデーションをかけ、
  // ファイル名をランダムな文字列に変換する
  $filename = get_upload_filename($image);
  // 各データにバリデーションをかける
  // 一つでもバリデーションに問題があった場合
  if(validate_item($name, $price, $stock, $filename, $status) === false){
    // FALSEを返す
    return false;
  }
  // 全てのバリデーションが正常だった場合、トランザクションでデータを登録
  return regist_item_transaction($db, $name, $price, $stock, $status, $image, $filename);
}

// トランザクションでデータを登録
function regist_item_transaction($db, $name, $price, $stock, $status, $image, $filename){
  // トランザクション開始
  $db->beginTransaction();
  // 商品データの登録と画像の保存が正常に完了した場合
  if(insert_item($db, $name, $price, $stock, $filename, $status) 
    && save_image($image, $filename)){
    // コミット処理
    $db->commit();
    // TRUEを返す
    return true;
  }
  // 途中でエラーが発生した場合ロールバック
  $db->rollback();
  // FALSEを返す
  return false;
}

// 商品データの登録
function insert_item($db, $name, $price, $stock, $filename, $status){
  // ステータスをopen,closeから1,0に変換
  $status_value = PERMITTED_ITEM_STATUSES[$status];
  $sql = "
    INSERT INTO
      items(
        name,
        price,
        stock,
        image,
        status
      )
    VALUES(?, ?, ?, ?, ?);
  ";
  // sqlを実行して成功した場合に true を、失敗した場合に false を返す
  return execute_query($db, $sql, [$name, $price, $stock, $filename, $status_value]);
}

// ステータスの更新
function update_item_status($db, $item_id, $status){
  $sql = "
    UPDATE
      items
    SET
      status = ?
    WHERE
      item_id = ?
    LIMIT 1
  ";
  // sqlを実行して成功した場合に true を、失敗した場合に false を返す
  return execute_query($db, $sql, [$status, $item_id]);
}

// 在庫数の更新
function update_item_stock($db, $item_id, $stock){
  $sql = "
    UPDATE
      items
    SET
      stock = ?
    WHERE
      item_id = ?
    LIMIT 1
  ";
  // sqlを実行して成功した場合に true を、失敗した場合に false を返す
  return execute_query($db, $sql, [$stock, $item_id]);
}

function destroy_item($db, $item_id){
  $item = get_item($db, $item_id);
  if($item === false){
    return false;
  }
  $db->beginTransaction();
  if(delete_item($db, $item['item_id'])
    && delete_image($item['image'])){
    $db->commit();
    return true;
  }
  $db->rollback();
  return false;
}

// 特定の商品の削除
function delete_item($db, $item_id){
  $sql = "
    DELETE FROM
      items
    WHERE
      item_id = ?
    LIMIT 1
  ";
  // sqlを実行して成功した場合に true を、失敗した場合に false を返す
  return execute_query($db, $sql, [$item_id]);
}


// 非DB

// ステータスが公開か非公開か判別
function is_open($item){
  // ステータスが公開の場合TRUE、非公開の場合FALSEを返す
  return $item['status'] === 1;
}

// 各データにバリデーションをかける
function validate_item($name, $price, $stock, $filename, $status){
  // 商品名にバリデーションをかける(TRUE or FALSE)
  $is_valid_item_name = is_valid_item_name($name);
  // 商品価格にバリデーションをかける(TRUE or FALSE)
  $is_valid_item_price = is_valid_item_price($price);
  // 商品在庫数にバリデーションをかける(TRUE or FALSE)
  $is_valid_item_stock = is_valid_item_stock($stock);
  // 商品画像ファイル名にバリデーションをかける(TRUE or FALSE)
  $is_valid_item_filename = is_valid_item_filename($filename);
  // 商品ステータスにバリデーションをかける(TRUE or FALSE)
  $is_valid_item_status = is_valid_item_status($status);
  // すべてのデータがTRUEの場合TRUE、一つでもFALSEの場合はFALSEを返す
  return $is_valid_item_name
    && $is_valid_item_price
    && $is_valid_item_stock
    && $is_valid_item_filename
    && $is_valid_item_status;
}

// 商品名にバリデーションをかける
function is_valid_item_name($name){
  $is_valid = true;
  // 文字数が妥当でない場合
  if(is_valid_length($name, ITEM_NAME_LENGTH_MIN, ITEM_NAME_LENGTH_MAX) === false){
    // エラーメッセージを定義
    set_error('商品名は'. ITEM_NAME_LENGTH_MIN . '文字以上、' . ITEM_NAME_LENGTH_MAX . '文字以内にしてください。');
    $is_valid = false;
  }
  // TRUE or FALSEを返す
  return $is_valid;
}

// 商品価格にバリデーションをかける(TRUE or FALSE)
function is_valid_item_price($price){
  $is_valid = true;
  // 価格が0以上の整数でない場合
  if(is_positive_integer($price) === false){
    // エラーメッセージを定義
    set_error('価格は0以上の整数で入力してください。');
    $is_valid = false;
  }
  // TRUE or FALSEを返す
  return $is_valid;
}

// 商品在庫数にバリデーションをかける(TRUE or FALSE)
function is_valid_item_stock($stock){
  $is_valid = true;
  // 在庫数が0以上の整数でない場合
  if(is_positive_integer($stock) === false){
    // エラーメッセージを定義
    set_error('在庫数は0以上の整数で入力してください。');
    $is_valid = false;
  }
  // TRUE or FALSEを返す
  return $is_valid;
}

// 画像ファイル名にバリデーションをかける(TRUE or FALSE)
function is_valid_item_filename($filename){
  $is_valid = true;
  // 画像ファイル名が空文字の場合
  if($filename === ''){
    $is_valid = false;
  }
  // TRUE or FALSEを返す
  return $is_valid;
}

// 商品ステータスにバリデーションをかける(TRUE or FALSE)
function is_valid_item_status($status){
  $is_valid = true;
  // open or close以外が定義されている場合
  if(isset(PERMITTED_ITEM_STATUSES[$status]) === false){
    $is_valid = false;
  }
  // TRUE or FALSEを返す
  return $is_valid;
}