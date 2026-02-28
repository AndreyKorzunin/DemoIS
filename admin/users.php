<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

// Проверка авторизации
if (!isset($_SESSION['user'])) {
    header('Location: ../auth/login.php');
    exit();
}

// Проверка роли
if ($_SESSION['user']['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// добавлениe пользователя
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $login = $_POST['login'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (userExists($login)) {
        $error = "Пользователь с таким логином уже существует";
    } else {
        addUser($login, $password, $role);
        $success = "Пользователь успешно добавлен";
    }
}

// Обработка разблокировки
if (isset($_GET['unblock'])) {
    $id = $_GET['unblock'];
    unblockUser($id);
    $success = "Пользователь разблокирован";
}

$users = getAllUsers();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление пользователями </title>

</head>
<body>
<div class="admin-container">
    <h1>Управление пользователями</h1>

    <div class="menu">
        <a href="index.php">Вернуться в админ-панель</a>
    </div>

    <?php if (isset($error)): ?>
        <div class="error-message"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (isset($success)): ?>
        <div class="success-message"><?php echo $success; ?></div>
    <?php endif; ?>

    <h2>Добавить пользователя</h2>
    <form method="POST" action="">
        <div class="form-group">
            <label for="login">Логин:</label>
            <input type="text" id="login" name="login" required>
        </div>

        <div class="form-group">
            <label for="password">Пароль:</label>
            <input type="password" id="password" name="password" required>
        </div>

        <div class="form-group">
            <label for="role">Роль:</label>
            <select id="role" name="role">
                <option value="user">Пользователь</option>
                <option value="admin">Администратор</option>
            </select>
        </div>

        <button type="submit" name="add_user" class="btn">Добавить пользователя</button>
    </form>

    <h2>Список пользователей</h2>
    <table class="users-table">
        <thead>
        <tr>
            <th>ID</th>
            <th>Логин</th>
            <th>Роль</th>
            <th>Статус</th>
            <th>Действия</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo $user['id']; ?></td>
                <td><?php echo $user['login']; ?></td>
                <td><?php echo $user['role']; ?></td>
                <td><?php echo $user['is_blocked'] ? 'Заблокирован' : 'Активен'; ?></td>
                <td>
                    <?php if ($user['is_blocked']): ?>
                        <a href="?unblock=<?php echo $user['id']; ?>">Разблокировать</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>