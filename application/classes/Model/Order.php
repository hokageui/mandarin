<?php defined('SYSPATH') or die('No direct script access.');

class Model_Order extends ORM {
    protected $_table_name = 'orders';
    protected $_primary_key = 'id';

    // Связи с другими моделями
    protected $_belongs_to = array(
        'type' => array(
            'model'       => 'TshirtType',
            'foreign_key' => 'type_id',
        ),
        'color' => array(
            'model'       => 'Color',
            'foreign_key' => 'color_id',
        ),
        'size' => array(
            'model'       => 'Size',
            'foreign_key' => 'size_id',
        ),
        'material' => array(
            'model'       => 'Material',
            'foreign_key' => 'material_id',
        ),
        'transfer_type' => array(
            'model'       => 'TransferType',
            'foreign_key' => 'transfer_type_id',
        ),
    );

    protected $_rules = array(
        'type_id'         => array('not_empty' => NULL),
        'color_id'        => array('not_empty' => NULL),
        'size_id'         => array('not_empty' => NULL),
        'material_id'     => array('not_empty' => NULL),
        'transfer_type_id'=> array('not_empty' => NULL),
        'quantity'        => array('not_empty' => NULL, 'numeric' => NULL),
        'total_cost'      => array('not_empty' => NULL, 'numeric' => NULL),
        'production_days' => array('not_empty' => NULL, 'numeric' => NULL),
    );
}
?>
