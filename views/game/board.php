<?php include '../views/layout/header.php'; ?>

<!-- Виведення назви гри та інформації про поточний хід -->
<h2 style="text-align: center">
    Гра: <?php echo htmlspecialchars(Session::get('current_turn')); ?> ходить
</h2>

<?php
// Отримання даних з сесії
$board = Session::get('board'); // Ігрове поле

$size = count($board); // Розмір поля

$chat = Session::get('chat') ?? []; // Історія чату

// Налаштування масштабу клітинки
$cellSize = isset($_GET['scale']) ? (int)$_GET['scale'] : 80;

// Обмеження масштабу
if ($cellSize < 20) {
    $cellSize = 20;
}

if ($cellSize > 150) {
    $cellSize = 150;
}

?>

<!-- Форма для вибору масштабу клітинки -->
<form method="get" action="index.php">
    <input type="hidden" name="action" value="play">

    <label for="scale">Розмір клітинки (px):</label>

    <select name="scale" id="scale" onchange="this.form.submit()">
        <option value="50" <?php if ($cellSize == 50) echo 'selected'; ?>>50px</option>
        <option value="80" <?php if ($cellSize == 80) echo 'selected'; ?>>80px</option>
        <option value="100" <?php if ($cellSize == 100) echo 'selected'; ?>>100px</option>
        <option value="120" <?php if ($cellSize == 120) echo 'selected'; ?>>120px</option>
    </select>
</form>

<!-- Вікно чату -->
<div style="position: fixed; bottom: 20px; right: 20px; width: 300px; background-color: #f9f9f9; border: 1px solid #ddd; padding: 10px; max-height: 300px; overflow-y: scroll;">
    <h3>Чат</h3>

    <!-- Історія повідомлень -->
    <div style="border: 1px solid #000; padding: 10px; max-height: 200px; overflow-y: scroll; margin-bottom: 10px;">
        <?php foreach ($chat as $message) { ?>
            <p>
                <strong><?php echo htmlspecialchars($message['user']); ?>:</strong>
                <?php echo htmlspecialchars($message['message']); ?>
                <em><?php echo date('H:i', $message['timestamp']); ?></em>
            </p>
        <?php } ?>
    </div>

    <!-- Форма надсилання повідомлення -->
    <form method="post" action="index.php?action=send_message">
        <textarea name="message" required placeholder="Ваше сообщение" rows="3" style="width: 100%;"></textarea><br>
        <button type="submit">Отправить сообщение</button>
    </form>

    <!-- Форма очищення чату -->
    <form method="post" action="index.php?action=clear_chat" style="margin-top: 10px;">
        <button type="submit">Очистить чат</button>
    </form>
</div>

<!-- Таймер ходу -->
<div id="timer" style="font-size: 20px; font-weight: bold; color: red; margin-bottom: 10px;">
    Залишилось часу: <span id="time-left">10</span> сек.
</div>

<br><br>

<!-- Скрипт таймера -->
<script>
    // Отримання часу початку ходу
    const turnStartTime = <?= Session::get('turn_start_time') ?>;

    // Елемент для виводу часу
    const timerDisplay = document.getElementById('time-left');

    // Ліміт часу на хід
    const turnLimit = 10;

    // Звук закінчення часу
    const timerEndSound = new Audio('move.mp3');

    // Функція оновлення таймера
    function updateTimer() {
        const now = Math.floor(Date.now() / 1000);
        const elapsed = now - turnStartTime;
        const remaining = Math.max(0, turnLimit - elapsed);

        timerDisplay.textContent = remaining;

        if (remaining <= 0) {
            clearInterval(interval);
            timerEndSound.play();
            location.reload();
        }
    }

    updateTimer(); // Одразу оновлюємо
    const interval = setInterval(updateTimer, 1000); // Щосекунди оновлюємо
</script>

<!-- CSS стилі ігрового поля -->
<style>
    .board {
        border-collapse: collapse;
        margin: 20px auto;
    }

    .board td {
        width: <?php echo $cellSize; ?>px;
        height: <?php echo $cellSize; ?>px;
        border: 1px solid #000;
        text-align: center;
        font-size: <?php echo round($cellSize / 2); ?>px;
    }

    .board a {
        display: block;
        width: 100%;
        height: 100%;
        text-decoration: none;
        color: inherit;
    }
</style>

<!-- Ігрове поле -->
<table class="board">
    <?php
    // Вивід рядів і клітинок
    foreach ($board as $i => $row) {

        echo "<tr>";

        foreach ($row as $j => $cell) {

            echo "<td>";

            if ($cell === '') {
                // Клітинка пуста — робимо її клікабельною
                echo "<a href='index.php?action=play&row=$i&col=$j&scale=$cellSize'>&nbsp;</a>";
            } else {
                // Клітинка зайнята — виводимо символ
                echo htmlspecialchars($cell);
            }

            echo "</td>";
        }

        echo "</tr>";
    }
    ?>
</table>

<?php
include '../views/layout/footer.php';
?>
