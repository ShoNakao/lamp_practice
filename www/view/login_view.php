<!DOCTYPE html>
<html lang="ja">
<head>
  <!-- <head>の共通部分を読み込み -->
  <?php include VIEW_PATH . 'templates/head.php'; ?>
  <title>ログイン</title>
  <!-- login画面に関係するCSSを読み込み -->
  <link rel="stylesheet" href="<?php print(STYLESHEET_PATH . 'login.css'); ?>">
</head>
<body>
  <!-- headerの共通部分を読み込み -->
  <?php include VIEW_PATH . 'templates/header.php'; ?>
  <div class="container">
    <h1>ログイン</h1>
    <!-- messagesの共通部分を読み込み -->
    <?php include VIEW_PATH . 'templates/messages.php'; ?>

    <form method="post" action="login_process.php" class="login_form mx-auto">
      <div class="form-group">
        <label for="name">名前: </label>
        <input type="text" name="name" id="name" class="form-control">
      </div>
      <div class="form-group">
        <label for="password">パスワード: </label>
        <input type="password" name="password" id="password" class="form-control">
      </div>
      <!-- Bootstrapからボタンを利用 -->
      <input type="submit" value="ログイン" class="btn btn-primary">
    </form>
  </div>
</body>
</html>