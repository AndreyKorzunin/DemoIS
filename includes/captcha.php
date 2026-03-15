<?php


session_start();


function generateCaptcha() {
    // Массив с правильными именами файлов
    $files = ['1.png', '2.png', '3.png', '4.png'];

    // Копируем массив и мешаем его, чтобы картинки не по порядку были
    $shuffle_files = $files;
    shuffle($shuffle_files);

    // Записываем правильный порядок в сессию
    $_SESSION['correct_order'] = $files;

    return $shuffle_files;
}


function checkCaptcha($captchaResult) {
    return $captchaResult === 'ok';
}


function resetCaptcha() {
    unset($_SESSION['correct_order']);
}
?>