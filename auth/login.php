<?php


session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/captcha.php';


$shuffle_files = generateCaptcha();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'];
    $password = $_POST['password'];
    $captchaResult = isset($_POST['captcha_result']) ? $_POST['captcha_result'] : '';


    if (!checkCaptcha($captchaResult)) {
        $error = "Пазл решен неверно. Попробуйте еще раз.";
    } else {

        if (isUserBlocked($login)) {
            $error = "Вы заблокированы. Обратитесь к администратору";
        } else {

            $user = authenticate($login, $password);

            if ($user) {

                $_SESSION['user'] = $user;
                header('Location: ../admin/index.php');
                exit();
            } else {

                $error = "Вы ввели неверный логин или пароль. Пожалуйста проверьте ещё раз введенные данные";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Авторизация </title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .captcha-container {
            margin: 20px 0;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        .captcha-instructions {
            margin-bottom: 10px;
            font-weight: bold;
            color: #333;
        }
        .captcha-images {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        .captcha-images img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border: 2px solid #ccc;
            cursor: pointer;
            border-radius: 5px;
            transition: all 0.3s;
        }
        .captcha-images img:hover {
            border-color: #3498db;
            transform: scale(1.05);
        }
        .captcha-images img.active {
            border-color: #27ae60 !important;
            opacity: 0.6;
            pointer-events: none;
        }
        .captcha-message {
            height: 20px;
            font-weight: bold;
            margin-bottom: 10px;
            text-align: center;
        }
        .captcha-message.ok { color: #27ae60; }
        .captcha-message.err { color: #e74c3c; }
        .captcha-input {
            display: none;
        }
    </style>
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


        <div class="form-group captcha-container">
            <div class="captcha-instructions">Собери пазл в правильном порядке (1-2-3-4)</div>
            <div class="captcha-message" id="captchaMsg"></div>

            <div class="captcha-images">
                <?php foreach ($shuffle_files as $file): ?>
                    <img src="../assets/captcha/<?php echo $file; ?>"
                         data-id="<?php echo pathinfo($file, PATHINFO_FILENAME); ?>"
                         class="cap-img">
                <?php endforeach; ?>
            </div>

            <input type="hidden" name="captcha_result" id="captchaResult" class="captcha-input" value="">
        </div>

        <button type="submit" class="btn">Войти</button>
    </form>
</div>

<script>
    let step = 1;
    let success = false;
    let maxSteps = 4;

    const images = document.querySelectorAll('.cap-img');
    const msg = document.getElementById('captchaMsg');
    const input = document.getElementById('captchaResult');

    images.forEach(img => {
        img.addEventListener('click', function() {
            // Если уже выбрана - игнор
            if (this.classList.contains('active')) return;

            let val = parseInt(this.getAttribute('data-id'));

            if (val === step) {

                this.classList.add('active');
                step++;
                msg.textContent = "";


                if (step > maxSteps) {
                    msg.textContent = "Верно!";
                    msg.className = "captcha-message ok";
                    input.value = "ok";
                    success = true;


                    setTimeout(() => {
                        alert("Капча пройдена! Теперь можно авторизоваться.");
                    }, 500);
                }
            } else {

                msg.textContent = "Неверный порядок! Начни заново.";
                msg.className = "captcha-message err";


                step = 1;
                success = false;
                input.value = "";


                images.forEach(img => img.classList.remove('active'));


                setTimeout(() => {
                    if (!success) msg.textContent = "";
                }, 2000);
            }
        });
    });

    // Проверка при отправке формы
    document.querySelector('form').addEventListener('submit', function(e) {
        if (!success) {
            e.preventDefault();
            alert("Сначала пройди капчу!");
        }
    });
</script>
</body>
</html>