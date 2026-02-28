<?php
// Функции аутентификации
require_once '../config/database.php';

// Проверка авторизации
function authenticate($login, $password) {
    global $conn;
        $query = "SELECT * FROM users WHERE login = '$login' AND password = '$password'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);

        // Сброс счетчика неудачных попыток
        $updateQuery = "UPDATE users SET block_count = 0, is_blocked = 0, last_login_attempt = NOW() WHERE id = " . $user['id'];
        mysqli_query($conn, $updateQuery);

        return $user;
    } else {
        // Увеличение счетчика неудачных попыток
        $updateQuery = "UPDATE users SET block_count = block_count + 1, last_login_attempt = NOW() WHERE login = '$login'";
        mysqli_query($conn, $updateQuery);

        // Проверка блокировки
        $checkQuery = "SELECT block_count FROM users WHERE login = '$login'";
        $checkResult = mysqli_query($conn, $checkQuery);
        $row = mysqli_fetch_assoc($checkResult);

        if ($row['block_count'] >= 3) {
            $blockQuery = "UPDATE users SET is_blocked = 1 WHERE login = '$login'";
            mysqli_query($conn, $blockQuery);
            return false;
        }

        return false;
    }
}

// Проверка, заблокирован ли пользователь
function isUserBlocked($login) {
    global $conn;

    $query = "SELECT is_blocked FROM users WHERE login = '$login'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);

    return $row['is_blocked'] == 1;
}

// Проверка существования пользователя
function userExists($login) {
    global $conn;

    $query = "SELECT COUNT(*) as count FROM users WHERE login = '$login'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);

    return $row['count'] > 0;
}

// Добавление пользователя
function addUser($login, $password, $role) {
    global $conn;

    $query = "INSERT INTO users (login, password, role) VALUES ('$login', '$password', '$role')";
    mysqli_query($conn, $query);
}

// Обновление пользователя
function updateUser($id, $login, $password, $role, $is_blocked, $block_count) {
    global $conn;

    $query = "UPDATE users SET login = '$login', password = '$password', role = '$role', is_blocked = '$is_blocked', block_count = '$block_count' WHERE id = $id";
    mysqli_query($conn, $query);
}

// Получение всех пользователей
function getAllUsers() {
    global $conn;

    $query = "SELECT * FROM users";
    $result = mysqli_query($conn, $query);
    $users = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }

    return $users;
}

// Снятие блокировки
function unblockUser($id) {
    global $conn;

    $query = "UPDATE users SET is_blocked = 0, block_count = 0 WHERE id = $id";
    mysqli_query($conn, $query);
}
?>