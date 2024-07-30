<?php defined('SYSPATH') or die('No direct script access.');

class Controller_OrderPayment extends Controller {

    public function action_update_payment()
    {
        $input = json_decode($this->request->body(), true);
        $order_id = isset($input['order_id']) ? intval($input['order_id']) : null;

        // лог с id
        error_log('Received order_id: ' . $order_id);

        $this->response->headers('Content-Type', 'application/json; charset=utf-8');

        if ($order_id === null || $order_id <= 0) {
            $this->response->status(400); // Устанавливаем статус ошибки
            $this->response->body(json_encode(['error' => 'ID заказа не указан или некорректен.']));
            return;
        }

        // Проверяем, существует ли заказ
        $order_model = ORM::factory('Order', $order_id);

        if (!$order_model->loaded()) {
            $this->response->status(404); // Устанавливаем статус не найдено
            $this->response->body(json_encode(['error' => 'Заказ не найден.']));
            return;
        }

        // Обновляем статус оплаты
        $order_model->payment = 1;
        $order_model->save();

        $this->response->status(200); // Устанавливаем статус успеха
        $this->response->body(json_encode(['success' => 'Статус оплаты обновлен.']));
    }
}
