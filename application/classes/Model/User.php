<?php defined('SYSPATH') or die('No direct script access.');

class Model_User extends ORM {
    protected $_table_name = 'users'; // Имя таблицы
    protected $_primary_key = 'id'; // Первичный ключ

    public function check_password($password) {
        return password_verify($password, $this->password); // bcrypt
    }
}

