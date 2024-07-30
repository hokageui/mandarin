<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Order extends Controller {

    public function action_details()
    {
        $order_id = $this->request->param('id');

        if (empty($order_id)) {
            $this->response->body('<p>ID заказа не указан.</p>');
            return;
        }

        $order_model = ORM::factory('Order', $order_id);

        if (!$order_model->loaded()) {
            $this->response->body('<p>Заказ не найден.</p>');
            return;
        }

        $order = $order_model->as_array();
        $order['type'] = $this->get_type_name($order['type_id']);
        $order['color'] = $this->get_color_name($order['color_id']);
        $order['size'] = $this->get_size_name($order['size_id']);
        $order['material'] = $this->get_material_name($order['material_id']);
        $order['print_sides'] = $this->get_print_sides($order['print_sides']);

        $view = View::factory('order')
            ->set('order', $order);

        $template = View::factory('template')
            ->set('content', $view);

        $this->response->body($template);
    }

    private function get_type_name($type_id)
    {
        $type = ORM::factory('TshirtType', $type_id);
        return $type->loaded() ? $type->type : 'Неизвестный тип';
    }

    private function get_color_name($color_id)
    {
        $color = ORM::factory('Color', $color_id);
        return $color->loaded() ? $color->color : 'Неизвестный цвет';
    }

    private function get_size_name($size_id)
    {
        $size = ORM::factory('Size', $size_id);
        return $size->loaded() ? $size->size : 'Неизвестный размер';
    }

    private function get_material_name($material_id)
    {
        $material = ORM::factory('Material', $material_id);
        return $material->loaded() ? $material->material : 'Неизвестный материал';
    }

    private function get_print_sides($json_sides)
    {
        $sides = json_decode($json_sides, true);
        $result = [];

        $translations = [
            'left_shoulder' => 'Левое плечо',
            'right_shoulder' => 'Правое плечо',
            'breast' => 'Грудь',
            'back' => 'Спина',
        ];

        if (is_array($sides)) {
            foreach ($sides as $side_name => $dimensions) {
                $name = isset($translations[$side_name]) ? $translations[$side_name] : 'Неизвестная сторона';
                $formatted_dimensions = "{$dimensions['width']}x{$dimensions['height']}";
                $result[] = [
                    'name' => $name,
                    'dimensions' => $formatted_dimensions
                ];
            }
        }

        return $result;
    }
}
