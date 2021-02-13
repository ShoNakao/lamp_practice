<?php 
// 関数を定義したファイルを読み込み
require_once MODEL_PATH . 'functions.php';
// dbに関するファイルを
require_once MODEL_PATH . 'db.php';

// 特定の購入履歴を取得
function get_order($db, $order_id){
  $sql = " 
    SELECT
      orders.order_id,
      orders.order_datetime,
      sum(order_details.order_price * order_details.order_amount) as total
    FROM
      orders
    JOIN
      order_details
    ON
      orders.order_id = order_details.order_id
    WHERE
      orders.order_id = ?
    GROUP BY
      orders.order_id
  ";
  // sqlを実行して結果を返す
  return fetch_query($db, $sql, [$order_id]);
}

// 特定のユーザの特定の購入履歴を取得
function get_user_order($db, $user_id, $order_id){
  $sql = " 
    SELECT
      orders.order_id,
      orders.order_datetime,
      sum(order_details.order_price * order_details.order_amount) as total
    FROM
      orders
    JOIN
      order_details
    ON
      orders.order_id = order_details.order_id
    WHERE
      orders.user_id = ?
    AND
      orders.order_id = ?
    GROUP BY
      orders.order_id
  ";
  // sqlを実行して結果を返す
  return fetch_query($db, $sql, [$user_id, $order_id]);
}

// 全ての購入履歴を取得
function get_orders($db){
  $sql = "
    SELECT
      orders.order_id,
      orders.order_datetime,
      sum(order_details.order_price * order_details.order_amount) as total
    FROM
      orders
    JOIN
      order_details
    ON
      orders.order_id = order_details.order_id
    GROUP BY
      orders.order_id
    ORDER BY
      orders.order_id DESC
  ";
  // sqlを実行して結果を返す
  return fetch_all_query($db, $sql);
}

// 特定のユーザの購入履歴を全て取得
function get_user_orders($db, $user_id){
  $sql = " 
    SELECT
      orders.order_id,
      orders.order_datetime,
      sum(order_details.order_price * order_details.order_amount) as total
    FROM
      orders
    JOIN
      order_details
    ON
      orders.order_id = order_details.order_id
    WHERE
      orders.user_id = ?
    GROUP BY
      orders.order_id
    ORDER BY
      orders.order_id DESC
  ";
  // sqlを実行して結果を返す
  return fetch_all_query($db, $sql, [$user_id]);
}

// 特定の注文の購入明細を取得
function get_order_details($db, $order_id){
  $sql = "
    SELECT
      order_details.order_price,
      order_details.order_amount,
      items.name,
      order_details.order_price * order_details.order_amount as subtotal
    FROM
      orders
    JOIN
      order_details
    ON
      orders.order_id = order_details.order_id
    JOIN
      items
    ON
      order_details.item_id = items.item_id
    WHERE
      orders.order_id = ?
  ";
  // sqlを実行して結果を返す
  return fetch_all_query($db, $sql, [$order_id]);
}

// 特定のユーザの特定の注文の購入明細を取得
function get_user_order_details($db, $user_id, $order_id){
  $sql = "
    SELECT
      order_details.order_price,
      order_details.order_amount,
      items.name,
      order_details.order_price * order_details.order_amount as subtotal
    FROM
      orders
    JOIN
      order_details
    ON
      orders.order_id = order_details.order_id
    JOIN
      items
    ON
      order_details.item_id = items.item_id
    WHERE
      orders.user_id = ?
    AND
      orders.order_id = ?
  ";
  // sqlを実行して結果を返す
  return fetch_all_query($db, $sql, [$user_id, $order_id]);
}

// 購入履歴データを登録
function insert_order($db, $user_id){
  $sql = "
    INSERT INTO
      orders(
        user_id
      )
    VALUES(?);
  ";
  // sqlを実行して成功した場合に true を、失敗した場合に false を返す
  return execute_query($db, $sql, [$user_id]);
}

// 購入明細データを登録
function insert_order_detail($db, $order_id, $item_id, $order_price, $order_amount){
  $sql = "
    INSERT INTO
      order_details(
        order_id,
        item_id,
        order_price,
        order_amount
      )
    VALUES(?, ?, ?, ?);
  ";
  // sqlを実行して成功した場合に true を、失敗した場合に false を返す
  return execute_query($db, $sql, [$order_id, $item_id, $order_price, $order_amount]);
}

?>
