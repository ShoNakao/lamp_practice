<?php 
// 関数を定義したファイルを読み込み
require_once MODEL_PATH . 'functions.php';
// dbに関するファイルを
require_once MODEL_PATH . 'db.php';
// 購入履歴に関するファイルを読み込み
require_once MODEL_PATH . 'order.php';

// 特定のユーザのカートに入っている全ての商品情報をdbから取得
function get_user_carts($db, $user_id){
  $sql = "
    SELECT
      items.item_id,
      items.name,
      items.price,
      items.stock,
      items.status,
      items.image,
      carts.cart_id,
      carts.user_id,
      carts.amount
    FROM
      carts
    JOIN
      items
    ON
      carts.item_id = items.item_id
    WHERE
      carts.user_id = ?
  ";
  // sqlを実行して結果を返す
  return fetch_all_query($db, $sql, [$user_id]);
}

// 特定のユーザのカートに入っている特定の商品の情報をdbから取得
function get_user_cart($db, $user_id, $item_id){
  $sql = "
    SELECT
      items.item_id,
      items.name,
      items.price,
      items.stock,
      items.status,
      items.image,
      carts.cart_id,
      carts.user_id,
      carts.amount
    FROM
      carts
    JOIN
      items
    ON
      carts.item_id = items.item_id
    WHERE
      carts.user_id = ?
    AND
      items.item_id = ?
  ";
  // sqlを実行して結果を返す
  return fetch_query($db, $sql, [$user_id, $item_id]);

}

// カートに商品を追加
// 既にカートにある商品の場合は、購入数を１つプラス
function add_cart($db, $user_id, $item_id ) {
  // 特定のユーザのカートに入っている特定の商品の情報をdbから取得
  $cart = get_user_cart($db, $user_id, $item_id);
  // カートが空またはデータベースでエラーが起きた場合は
  if($cart === false){
    // カートに商品を追加
    return insert_cart($db, $user_id, $item_id);
  }
  // カートにある商品の購入数を1つプラス
  return update_cart_amount($db, $cart['cart_id'], $cart['amount'] + 1);
}

// カートに商品を追加
function insert_cart($db, $user_id, $item_id, $amount = 1){
  $sql = "
    INSERT INTO
      carts(
        item_id,
        user_id,
        amount
      )
    VALUES(?, ?, ?)
  ";
  // sqlを実行して成功した場合に true を、失敗した場合に false を返す
  return execute_query($db, $sql, [$item_id, $user_id, $amount]);
}

// カートの購入数を更新
function update_cart_amount($db, $cart_id, $amount){
  $sql = "
    UPDATE
      carts
    SET
      amount = ?
    WHERE
      cart_id = ?
    LIMIT 1
  ";
  // sqlを実行して成功した場合に true を、失敗した場合に false を返す
  return execute_query($db, $sql, [$amount, $cart_id]);
}

// カート内の特定の商品を削除
function delete_cart($db, $cart_id){
  $sql = "
    DELETE FROM
      carts
    WHERE
      cart_id = ?
    LIMIT 1
  ";
  // sqlを実行して成功した場合に true を、失敗した場合に false を返す
  return execute_query($db, $sql, [$cart_id]);
}

// カート内の商品を購入する
function purchase_carts($db, $carts){
  // カート内の商品が購入できない場合
  if(validate_cart_purchase($carts) === false){
    // FALSEを返す
    return false;
  }

  // カート内の商品が購入できる場合
  // トランザクション開始
  $db->beginTransaction();
  // 購入履歴データを登録
  if (insert_order($db, $carts[0]['user_id']) === false){
    // FALSEを返す
    return false;
  }  
  // order_idの値を取得
  $order_id = $db->lastInsertId('order_id');
  // 繰り返し処理で購入の処理
  foreach($carts as $cart){
    // 在庫数の更新及び購入明細データの登録に失敗した場合
    if(update_item_stock($db, $cart['item_id'], $cart['stock'] - $cart['amount']) === false
      || insert_order_detail($db, $order_id, $cart['item_id'], $cart['price'], $cart['amount']) === false){

      // エラーメッセージを定義
      // set_error($cart['name'] . 'の購入に失敗しました。');

      // ロールバック処理(取り消し)
      $db->rollback();
      // FALSEを返す
      return false;
    }
  }
  // コミット処理
  $db->commit();
  // 購入したユーザのカート内の全商品を削除
  delete_user_carts($db, $carts[0]['user_id']);
}

// 特定のユーザのカート内の全商品を削除
function delete_user_carts($db, $user_id){
  $sql = "
    DELETE FROM
      carts
    WHERE
      user_id = ?
  ";
  // sqlを実行
  execute_query($db, $sql, [$user_id]);
}

// 合計金額を計算
function sum_carts($carts){
  $total_price = 0;
  foreach($carts as $cart){
    $total_price += $cart['price'] * $cart['amount'];
  }
  // 合計金額を返す
  return $total_price;
}

// カート内の商品が購入できるかバリデーション
function validate_cart_purchase($carts){
  // カートに商品が入っていない場合
  if(count($carts) === 0){
    // エラーメッセージを定義
    set_error('カートに商品が入っていません。');
    // FALSEを返す
    return false;
  }
  // カートに商品が入っている場合
  foreach($carts as $cart){
    // ステータスが非公開の場合
    if(is_open($cart) === false){
      // エラーメッセージを定義
      set_error($cart['name'] . 'は現在購入できません。');
    }
    // 購入数に対して在庫数が不足している場合
    if($cart['stock'] - $cart['amount'] < 0){
      // エラーメッセージを定義
      set_error($cart['name'] . 'は在庫が足りません。購入可能数:' . $cart['stock']);
    }
  }
  // エラーメッセージが定義されている場合
  if(has_error() === true){
    // FALSEを返す
    return false;
  }
   // エラーメッセージが定義されていない場合TRUEを返す
  return true;
}

