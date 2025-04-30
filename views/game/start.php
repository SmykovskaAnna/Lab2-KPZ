<?php include '../views/layout/header.php'; ?>

<!-- Заголовок сторінки -->
<h2 style="text-align: center">Створення нової гри</h2>

<!-- Основна форма створення гри -->
<form method="post">
    
    <!-- Вибір режиму гри -->
    <label>Тип гри:</label><br>
    <select name="mode" required>
        <option value="bot">Проти бота</option>
        <option value="user">Проти гравця</option>
    </select>

    <br>

    <!-- Вибір розміру поля -->
    <label>Розмір поля:</label><br>
    <select name="size" required>
        <option value="3">3x3</option>
        <option value="4">4x4</option>
        <option value="5">5x5</option>
    </select>

    <br>

    <!-- Вибір складності бота -->
    <label>Складність бота:</label><br>
    <select name="difficulty" required>
        <option value="easy">Легкий</option>
        <option value="medium">Середній</option>
        <option value="hard">Високий</option>
    </select>

    <br>

    <!-- Вибір символу гравця -->
    <label for="player_symbol">Оберіть ваш символ:</label><br>
    <select name="player_symbol" id="player_symbol" required>
        <option value="X">X</option>
        <option value="O">O</option>
        <option value="@">@</option>
        <option value="#">#</option>
        <option value="*">*</option>
        <option value="*">=</option>
        <option value="*">+</option>
        <option value="*">-</option>
    </select>

    <br><br>

    <!-- Кнопка для запуску гри -->
    <button type="submit">Почати гру</button>

</form>

<br>

<?php include '../views/layout/footer.php'; ?>