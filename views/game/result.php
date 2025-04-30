<?php include '../views/layout/header.php'; ?>

<h2 style = "text-align: center">Результат</h2>

<p style = "text-align: center"><?php echo htmlspecialchars(Session::get('message')); ?></p>

<a href="index.php?action=start_game">Грати знову</a>

<?php include '../views/layout/footer.php'; ?>