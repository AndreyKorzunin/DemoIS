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


if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];

    if ($action === 'block') {
        blockUser($id);
        $success = "Пользователь заблокирован";
    } elseif ($action === 'unblock') {
        unblockUser($id);
        $success = "Пользователь разблокирован";
    }

    header("Location: users.php");
    exit();
}

$users = getAllUsers();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Управление пользователями </title>
    <style>
        body {
            font-family: sans-serif;
            margin: 20px;
            background: #f9f9f9;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 4px;
            box-shadow: 0 1px 5px rgba(0,0,0,0.05);
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }


        .form-group {
            margin: 10px 0;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, select {
            width: 100%;
            padding: 6px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }


        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 8px 10px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #f2f2f2;
            font-weight: bold;
        }
        tr:hover {
            background: #fafafa;
        }
        .status-active { color: #28a745; }
        .status-blocked { color: #dc3545; }


        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-primary { background: #007bff; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-sm {
            padding: 4px 8px;
            font-size: 12px;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 15px;
            color: #007bff;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Управление пользователями</h1>

    <a href="index.php" class="back-link">← Вернуться в админ-панель</a>

    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <div style="margin-bottom: 25px;">
        <h3>Добавить пользователя</h3>
        <form method="POST">
            <div class="form-group">
                <label>Логин:</label>
                <input type="text" name="login" required>
            </div>
            <div class="form-group">
                <label>Пароль:</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Роль:</label>
                <select name="role">
                    <option value="user">Пользователь</option>
                    <option value="admin">Администратор</option>
                </select>
            </div>
            <button type="submit" name="add_user" class="btn btn-primary">Добавить</button>
        </form>
    </div>
    <h3>Список пользователей</h3>
    <table>
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
                <td><?php echo (int)$user['id']; ?></td>
                <td><?php echo htmlspecialchars($user['login']); ?></td>
                <td><?php echo ucfirst(htmlspecialchars($user['role'])); ?></td>
                <td>
                    <?php if ($user['is_blocked']): ?>
                        <span class="status-blocked">Заблокирован</span>
                    <?php else: ?>
                        <span class="status-active">Активен</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($user['is_blocked']): ?>
                        <a href="?action=unblock&id=<?php echo $user['id']; ?>"
                           class="btn btn-success btn-sm"
                           onclick="return confirm('Разблокировать <?php echo htmlspecialchars($user['login']); ?>?')">
                            Разблокировать
                        </a>
                    <?php else: ?>
                        <a href="?action=block&id=<?php echo $user['id']; ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Заблокировать <?php echo htmlspecialchars($user['login']); ?>?')">
                            Заблокировать
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>