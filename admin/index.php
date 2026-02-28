<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';


if (!isset($_SESSION['user'])) {
    header('Location: ../auth/login.php');
    exit();
}


if ($_SESSION['user']['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель </title>

</head>
<body>
<div class="admin-container">
    <h1>Админ-панель</h1>

    <div class="menu">
        <a href="users.php">Управление пользователями</a>
    </div>

    <div class="welcome-message">
        <p>Добро пожаловать, <?php echo $_SESSION['user']['login']; ?>!</p>
        <a href="../logout.php">Выйти</a>
    </div>
</div>
</body>
</html>