<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Calculator extends Controller_Template {

    public $template = 'template';

    public function action_index() {
        try {

            $types = ORM::factory('TshirtType')->find_all();
            $colors = ORM::factory('Color')->find_all();
            $sizes = ORM::factory('Size')->find_all();
            $materials = ORM::factory('Material')->find_all();
            $transfer_types = ORM::factory('TransferType')->find_all();

            // Подготовка представления для калькулятора
            $calculator_view = View::factory('calculator/form')
                ->set('types', $types)
                ->set('colors', $colors)
                ->set('sizes', $sizes)
                ->set('materials', $materials)
                ->set('transfer_types', $transfer_types);

            // Установка содержимого для шаблона
            $this->template->title = 'Калькулятор стоимости футболок';
            $this->template->content = $calculator_view;

        } catch (Exception $e) {

            error_log($e->getMessage());
            $this->template->content = 'An error occurred while loading the page.';
        }
    }

    public function action_sizes() {
        $this->auto_render = false;

        // Валидация входных данных
        $type_id = $this->request->post('type_id');
        $type_id = (int)$type_id; 

        if ($type_id <= 0) {
            $this->response->headers('Content-Type', 'application/json');
            $this->response->body(json_encode(['error' => 'Invalid Type ID']));
            return;
        }

        try {
            $tshirts = ORM::factory('Tshirt')
                ->where('type_id', '=', $type_id)
                ->find_all();

            $size_ids = array_unique(array_column($tshirts->as_array(), 'size_id'));

            $sizes = ORM::factory('Size')
                ->where('id', 'IN', $size_ids)
                ->find_all();

            $sizes_array = [];

            foreach ($sizes as $size) {
                $sizes_array[] = [
                    'id' => $size->id,
                    'size' => $size->size
                ];
            }

            $this->response->headers('Content-Type', 'application/json');
            $this->response->body(json_encode($sizes_array));
        } catch (Exception $e) {

            error_log($e->getMessage());
            $this->response->headers('Content-Type', 'application/json');
            $this->response->body(json_encode(['error' => 'An error occurred while fetching sizes']));
        }
    }

    public function action_calculate() {
        $this->auto_render = false;

        // Получение и фильтрация POST данных
        $post_data = $this->request->post();
        $post_data = $this->sanitize_input($post_data);

        // Список всех возможных сторон
        $sides = ['breast', 'back', 'right_shoulder', 'left_shoulder'];
        $sides_data = [];

        // Проверка и сбор данных для каждой стороны
        foreach ($sides as $side) {
            if (isset($post_data['sides'][$side]) && $post_data['sides'][$side] == 1) {
                if (isset($post_data[$side]) && is_array($post_data[$side])) {
                    $width = isset($post_data[$side]['width']) ? (float)$post_data[$side]['width'] : null;
                    $height = isset($post_data[$side]['height']) ? (float)$post_data[$side]['height'] : null;

                    if ($width !== null && $height !== null && $width >= 10 && $height >= 10) {
                        $sides_data[$side] = [
                            'width' => $width,
                            'height' => $height,
                        ];
                    } else {
                        error_log("Width or height less than 10mm for side: $side");
                    }
                } else {
                    error_log("Missing width or height for side: $side");
                }
            } else {
                error_log("Side not selected or incorrect value for: $side");
            }
        }

        // Отладка собранных данных
        error_log('Sides Data: ' . print_r($sides_data, true));

        if (empty($sides_data)) {
            $this->response->headers('Content-Type', 'application/json');
            $this->response->body(json_encode(['status' => 'error', 'message' => 'Please provide valid dimensions for at least one side']));
            return;
        }

        $type_id = isset($post_data['type']) ? (int)$post_data['type'] : null;
        $color_id = isset($post_data['color']) ? (int)$post_data['color'] : null;
        $size_id = isset($post_data['size']) ? (int)$post_data['size'] : null;
        $material_id = isset($post_data['material']) ? (int)$post_data['material'] : null;
        $transfer_type = isset($post_data['transfer_type']) ? (int)$post_data['transfer_type'] : null;
        $quantity = isset($post_data['quantity']) ? (int)$post_data['quantity'] : 1;

        if ($transfer_type === null || $type_id === null || $color_id === null || $size_id === null || $material_id === null) {
            $this->response->headers('Content-Type', 'application/json');
            $this->response->body(json_encode(['status' => 'error', 'message' => 'Missing required parameters']));
            return;
        }

        // Получаем цену футболки из базы данных
        $shirt_price = $this->get_shirt_price($type_id, $color_id, $size_id, $material_id);
        if ($shirt_price === null) {
            $this->response->headers('Content-Type', 'application/json');
            $this->response->body(json_encode(['status' => 'error', 'message' => 'Shirt not found']));
            return;
        }

        $price_per_sq_mm = $this->get_transfer_price_per_sq_mm($transfer_type);
        if ($price_per_sq_mm === null) {
            $this->response->headers('Content-Type', 'application/json');
            $this->response->body(json_encode(['status' => 'error', 'message' => 'Transfer type not found']));
            return;
        }

        // Рассчитываем общую стоимость переноса
        $total_transfer_cost = 0.0;
        foreach ($sides_data as $data) {
            $area_sq_mm = $data['width'] * $data['height'];
            $total_transfer_cost += $area_sq_mm * $price_per_sq_mm;
        }

        // Общая стоимость переноса для тиража
        $total_transfer_cost *= $quantity;

        // Стоимость футболок
        $total_shirt_cost = $shirt_price * $quantity;

        // Общая стоимость
        $total_cost = $total_transfer_cost + $total_shirt_cost;

        // Округляем сумму до целого рубля в большую сторону
        $total_cost = ceil($total_cost);

        // Срок выполнения
        $production_days = ceil($quantity / 60);

        // Формируем ответ
        $response = [
            'status' => 'success',
            'price' => number_format($total_cost, 0, ',', ' ') . ' руб.',
            'production_time' => $production_days,
            'quantity' => $quantity,
        ];

        $this->response->headers('Content-Type', 'application/json');
        $this->response->body(json_encode($response));
    }

    public function action_save_order() {
        $this->auto_render = false;

        $post_data = $this->request->post();
        $post_data = $this->sanitize_input($post_data);

        $sides = ['breast', 'back', 'right_shoulder', 'left_shoulder'];
        $sides_data = [];

        foreach ($sides as $side) {
            if (isset($post_data['sides'][$side]) && $post_data['sides'][$side] == 1) {
                if (isset($post_data[$side]) && is_array($post_data[$side])) {
                    $width = isset($post_data[$side]['width']) ? (float)$post_data[$side]['width'] : null;
                    $height = isset($post_data[$side]['height']) ? (float)$post_data[$side]['height'] : null;

                    if ($width !== null && $height !== null && $width >= 10 && $height >= 10) {
                        $sides_data[$side] = [
                            'width' => $width,
                            'height' => $height,
                        ];
                    } else {
                        error_log("Width or height less than 10mm for side: $side");
                    }
                } else {
                    error_log("Missing width or height for side: $side");
                }
            } else {
                error_log("Side not selected or incorrect value for: $side");
            }
        }

        error_log('Sides Data: ' . print_r($sides_data, true));

        if (empty($sides_data)) {
            $this->response->headers('Content-Type', 'application/json');
            $this->response->body(json_encode(['status' => 'error', 'message' => 'Please provide valid dimensions for at least one side']));
            return;
        }

        $type_id = isset($post_data['type']) ? (int)$post_data['type'] : null;
        $color_id = isset($post_data['color']) ? (int)$post_data['color'] : null;
        $size_id = isset($post_data['size']) ? (int)$post_data['size'] : null;
        $material_id = isset($post_data['material']) ? (int)$post_data['material'] : null;
        $transfer_type = isset($post_data['transfer_type']) ? (int)$post_data['transfer_type'] : null;
        $quantity = isset($post_data['quantity']) ? (int)$post_data['quantity'] : 1;

        if ($transfer_type === null || $type_id === null || $color_id === null || $size_id === null || $material_id === null) {
            $this->response->headers('Content-Type', 'application/json');
            $this->response->body(json_encode(['status' => 'error', 'message' => 'Missing required parameters']));
            return;
        }

        // Получаем цену футболки из базы данных
        $shirt_price = $this->get_shirt_price($type_id, $color_id, $size_id, $material_id);
        if ($shirt_price === null) {
            $this->response->headers('Content-Type', 'application/json');
            $this->response->body(json_encode(['status' => 'error', 'message' => 'Shirt not found']));
            return;
        }

        $price_per_sq_mm = $this->get_transfer_price_per_sq_mm($transfer_type);
        if ($price_per_sq_mm === null) {
            $this->response->headers('Content-Type', 'application/json');
            $this->response->body(json_encode(['status' => 'error', 'message' => 'Transfer type not found']));
            return;
        }

        // Рассчитываем общую стоимость переноса
        $total_transfer_cost = 0.0;
        foreach ($sides_data as $data) {
            $area_sq_mm = $data['width'] * $data['height'];
            $total_transfer_cost += $area_sq_mm * $price_per_sq_mm;
        }

        // Общая стоимость переноса для тиража
        $total_transfer_cost *= $quantity;

        // Стоимость футболок
        $total_shirt_cost = $shirt_price * $quantity;

        // Общая стоимость
        $total_cost = $total_transfer_cost + $total_shirt_cost;

        // Округляем сумму до целого рубля в большую сторону
        $total_cost = ceil($total_cost);

        // Срок выполнения
        $production_days = ceil($quantity / 60);

        // Создание нового заказа
        $order = ORM::factory('Order');
        $order->type_id = $type_id;
        $order->color_id = $color_id;
        $order->size_id = $size_id;
        $order->material_id = $material_id;
        $order->transfer_type_id = $transfer_type;
        $order->quantity = $quantity;
        $order->total_cost = $total_cost;
        $order->production_time = $production_days;
        $order->print_sides = json_encode($sides_data); // сохраняем данные о сторонах
        $order->save();

        // Формируем ответ
        $response = [
            'status' => 'success',
            'price' => number_format($total_cost, 0, ',', ' ') . ' руб.',
            'production_time' => $production_days,
            'quantity' => $quantity,
            'order_id' => $order->id, // Возвращаем ID заказа
        ];

        $this->response->headers('Content-Type', 'application/json');
        $this->response->body(json_encode($response));
    }


    private function sanitize_input(array $data): array {
        foreach ($data as &$value) {
            if (is_array($value)) {
                $value = $this->sanitize_input($value);
            } else {
                $value = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
            }
        }
        return $data;
    }

    private function get_shirt_price(int $type_id, int $color_id, int $size_id, int $material_id): ?float {
        try {
            $shirt = ORM::factory('Tshirt')
                ->where('type_id', '=', $type_id)
                ->and_where('color_id', '=', $color_id)
                ->and_where('size_id', '=', $size_id)
                ->and_where('material_id', '=', $material_id)
                ->find();

            if ($shirt->loaded()) {
                return (float) $shirt->price;
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
        return null;
    }

    private function get_transfer_price_per_sq_mm(int $transfer_type): ?float {
        try {
            $transfer = ORM::factory('TransferType')
                ->where('id', '=', $transfer_type)
                ->find();

            if ($transfer->loaded()) {
                return (float) $transfer->price_per_sq_mm;
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
        return null;
    }

}

?>
