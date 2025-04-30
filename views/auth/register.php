<?php include '../views/layout/header.php'; ?>

<h2 style = "text-align: center">Реєстрація</h2>

<?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>

<form method="post">
    <input type="text" name="username" placeholder="Логін" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Пароль" required>
    <button type="submit">Зареєструватися</button>
</form>

<p style = "text-align: center">Вже є акаунт? <a href="index.php?action=login">Увійти</a></p>

<?php include '../views/layout/footer.php'; ?>