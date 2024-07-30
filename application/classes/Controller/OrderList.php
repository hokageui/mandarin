<?php defined('SYSPATH') or die('No direct script access.');

class Controller_OrderList extends Controller {

    public function action_index()
    {
        // Загружаем все заказы
        $orders = ORM::factory('Order')
            ->order_by('created_at', 'DESC') // Последние заказы первыми
            ->find_all();

        // Подготавливаем данные для представления
        $orders_data = array_map(function($order) {
            return [
                'id' => $order->id,
                'created_at' => $order->created_at, // Добавляем дату создания
                'type' => $this->get_type_name($order->type_id),
                'color' => $this->get_color_name($order->color_id),
                'size' => $this->get_size_name($order->size_id),
                'material' => $this->get_material_name($order->material_id),
                'quantity' => $order->quantity,
                'total_cost' => $order->total_cost,
                'payment' => $order->payment,
            ];
        }, $orders->as_array());

        $view = View::factory('order_list')
            ->set('orders', $orders_data);

        // Используем шаблон для отображения
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
}
