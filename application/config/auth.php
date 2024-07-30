<?php defined('SYSPATH') or die('No direct script access.');

return array(
    'driver' => 'orm', // Драйвер для аутентификации, используемый модулем auth
    'hash_method' => 'bcrypt', // Метод хеширования паролей (bcrypt, sha256 и т.д.)
    'lifetime' => 1209600, // Время жизни куки в секундах (2 недели)
    'cookie' => array(
        'name' => 'auth_user', // Имя куки
        'encrypted' => false, // Опция шифрования куки, если нужно
        'lifetime' => 1209600, // Время жизни куки (2 недели)
        'path' => '/', // Путь для куки
        'domain' => '', // Домен для куки
        'secure' => false, // Устанавливать куку только через HTTPS
        'httponly' => true, // Устанавливать куку только для HTTP (не доступна через JavaScript)
        'samesite' => 'Lax' // Опция для SameSite атрибута куки
    ),
    'login_url' => '/login', // URL для перенаправления при попытке доступа к защищённым ресурсам
    'logout_url' => '/logout', // URL для перенаправления после выхода
);
