<?php
class Model_Tshirt extends ORM {
    protected $_table_name = 'tshirts'; // Название таблицы
    protected $_primary_key = 'id'; // Первичный ключ

    protected $_belongs_to = array(
        'size' => array(
            'model' => 'Size',
            'foreign_key' => 'size_id',
        ),
        'type' => array(
            'model' => 'TshirtType',
            'foreign_key' => 'type_id',
        ),
    );
}
?>
