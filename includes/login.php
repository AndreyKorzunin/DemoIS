<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/captcha.php';

// Обработка формы авторизации
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'];
    $password = $_POST['password'];

    // Проверка капчи
    if (!isset($_POST['captcha_solution'])) {
        $error = "Пожалуйста, решите капчу-пазл перед авторизацией.";
    } else {
        $captchaSolution = json_decode($_POST['captcha_solution'], true);
        if (!checkCaptchaSolution($captchaSolution)) {
            $error = "Пазл решен неверно. Попробуйте еще раз.";
        } else {
            // Проверка блокировки
            if (isUserBlocked($login)) {
                $error = "Вы заблокированы. Обратитесь к администратору";
            } else {
                // Попытка авторизации
                $user = authenticate($login, $password);

                if ($user) {
                    // Успешная авторизация
                    $_SESSION['user'] = $user;
                    header('Location: ../admin/index.php');
                    exit();
                } else {
                    // Неудачная авторизация
                    $error = "Вы ввели неверный логин или пароль. Пожалуйста проверьте ещё раз введенные данные";
                }
            }
        }
    }
}

// Генерация капчи
$puzzle = generateCaptchaImage();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Авторизация </title>

</head>
<body>
<div class="login-container">
    <h2>Авторизация</h2>

    <?php if (isset($error)): ?>
        <div class="error-message"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="login">Логин:</label>
            <input type="text" id="login" name="login" required>
        </div>

        <div class="form-group">
            <label for="password">Пароль:</label>
            <input type="password" id="password" name="password" required>
        </div>


                КАПЧА
        </div>

        <button type="submit" class="btn">Войти</button>
    </form>
</div>

<script src="../assets/js/script.js"></script>
</body>
</html>