<!DOCTYPE html>
<html lang="ja">
<head>
  <!-- <head>の共通部分を読み込み -->
  <?php include VIEW_PATH . 'templates/head.php'; ?>
  <title>購入明細</title>
  <!-- 購入明細画面に関係するCSSを読み込み -->
  <link rel="stylesheet" href="<?php print(STYLESHEET_PATH . 'order_detail.css'); ?>">
</head>
<body>
  <!-- headerの共通部分を読み込み -->
  <?php include VIEW_PATH . 'templates/header_logined.php'; ?>
  <h1>購入明細</h1>
  <div class="container">
    <!-- messagesの共通部分を読み込み -->
    <?php include VIEW_PATH . 'templates/messages.php'; ?>
    <!-- 購入明細が存在する場合(適正なアクセスの場合) -->
    <?php if(count($order_details) > 0){ ?>
      <table class="table table-bordered">
        <caption>
          <?php print('【注文番号：' . h($order['order_id']));?>
          <?php print('　購入日時：' . h($order['order_datetime'])); ?>
          <?php print('　合計金額：' . h(number_format($order['total'])) . '円】'); ?>
        </caption>
        <thead class="thead-light">
          <tr>
            <th>商品名</th>
            <th>商品価格</th>
            <th>購入数</th>
            <th>小計</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($order_details as $order_detail){ ?>
          <tr>
            <td><?php print(h($order_detail['name']));?></td>
            <td><?php print(h(number_format($order_detail['order_price']))); ?>円</td>
            <td><?php print(h($order_detail['order_amount'])); ?>個</td>
            <td><?php print(h(number_format($order_detail['subtotal']))); ?>円</td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
    <?php } else { ?>
      <p>※不正なリクエストです！</p>
    <?php } ?> 
  </div>
</body>
</html>