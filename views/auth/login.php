<?php include '../views/layout/header.php'; ?>

<h2 style = "text-align: center">Вхід</h2>

<?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>

<form method="post">
    <input type="text" name="username" placeholder="Логін" required>
    <input type="password" name="password" placeholder="Пароль" required>
    <button type="submit">Увійти</button>
</form>

<p style = "text-align: center">Немає акаунту? <a href="index.php?action=register">Реєстрація</a></p>

<?php include '../views/layout/footer.php'; ?>