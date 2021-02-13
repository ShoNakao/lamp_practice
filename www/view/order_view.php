<!DOCTYPE html>
<html lang="ja">
<head>
  <!-- <head>の共通部分を読み込み -->
  <?php include VIEW_PATH . 'templates/head.php'; ?>
  <title>購入履歴</title>
</head>
<body>
  <!-- headerの共通部分を読み込み -->
  <?php include VIEW_PATH . 'templates/header_logined.php'; ?>
  <h1>購入履歴</h1>
  <div class="container">
    <!-- messagesの共通部分を読み込み -->
    <?php include VIEW_PATH . 'templates/messages.php'; ?>
    <!-- 購入履歴が存在する場合 -->
    <?php if(count($orders) > 0){ ?>
      <table class="table table-bordered">
        <thead class="thead-light">
          <tr>
            <th>注文番号</th>
            <th>購入日時</th>
            <th>合計金額</th>
            <th>購入明細</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($orders as $order){ ?>
          <tr>
            <td><?php print(h($order['order_id']));?></td>
            <td><?php print(h($order['order_datetime'])); ?></td>
            <td><?php print(h(number_format($order['total']))); ?>円</td>
            <td>
              <form method="post" action="order_detail.php">
                <input type="submit" value="購入明細へ" class="btn btn-secondary">
                <input type="hidden" name="order_id" value="<?php print(h($order['order_id'])); ?>">
                <input type="hidden" name="csrf_token" value="<?php print $token; ?>">
              </form>
            </td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
    <?php } else { ?>
      <p>購入履歴はありません。</p>
    <?php } ?> 
  </div>
</body>
</html>