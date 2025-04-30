<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Хрестики-Нолики</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header>
    <h1>Хрестики-Нолики</h1>
    <?php if (\Session::get('user_id')): ?>
        <nav>
            <a href="index.php?action=start_game">Нова гра</a> |
            <a href="index.php?action=logout">Вийти</a> |
            <a href="index.php?action=stats">Статистика</a>
        </nav>
    <?php endif; ?>
</header>
<main>