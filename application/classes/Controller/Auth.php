<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Auth extends Controller {

    // Метод для обработки логина
    public function action_login() {

        $post_data = json_decode($this->request->body(), true); 

        // Извлечение данных
        $login = isset($post_data['login']) ? $post_data['login'] : '';
        $password = isset($post_data['password']) ? $post_data['password'] : '';
        $remember = isset($post_data['remember']) ? (bool) $post_data['remember'] : false;


        $errors = [];
        if (empty($login)) {
            $errors[] = 'Логин не может быть пустым.';
        }
        if (empty($password)) {
            $errors[] = 'Пароль не может быть пустым.';
        }

        if (!empty($errors)) {
            $this->response->headers('Content-Type', 'application/json');
            $this->response->body(json_encode([
                'success' => false,
                'errors' => $errors
            ]));
            return;
        }

        // Поиск пользователя в базе данных
        $user = ORM::factory('User')->where('login', '=', $login)->find();


        if ($user->loaded() && $user->check_password($password)) {
            // Устанавливаем куки для авторизованного пользователя
            Cookie::set('auth_user', $user->id, 3600); 

            $this->response->headers('Content-Type', 'application/json');
            $this->response->body(json_encode([
                'success' => true,
                'message' => 'Успешная авторизация.'
            ]));
        } else {
            $this->response->headers('Content-Type', 'application/json');
            $this->response->body(json_encode([
                'success' => false,
                'errors' => ['Неверный логин или пароль.']
            ]));
        }
    }

    // Метод для проверки авторизации
    public function action_check_auth() {
        // Получаем куки
        $user_id = Cookie::get('auth_user');
        $is_authenticated = $user_id ? true : false;

        $this->response->headers('Content-Type', 'application/json');
        $this->response->body(json_encode([
            'authenticated' => $is_authenticated
        ]));
    }

    // Метод для выхода
    public function action_logout() {
        // Удаляем куки
        Cookie::delete('auth_user');

        $this->response->headers('Content-Type', 'application/json');
        $this->response->body(json_encode([
            'success' => true,
            'message' => 'Вы успешно вышли из системы.'
        ]));
    }
}
