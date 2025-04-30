<?php include '../views/layout/header.php'; ?>

<h2 >Мій рейтинг</h2>

<ul>
    <li>Перемог: <?php echo $stats['wins']; ?></li>
    <li>Поразок: <?php echo $stats['losses']; ?></li>
    <li>Нічиї: <?php echo $stats['draws']; ?></li>
</ul>

<a href="index.php?action=start_game">Почати нову гру</a>

<?php include '../views/layout/footer.php'; ?>