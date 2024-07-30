<!DOCTYPE html>
<html>
<head>
    <title>Детали заказа</title>
    <link rel="stylesheet" type="text/css" href="/public/assets/styles/style.css">
</head>
<body>
<div class="container-order">
    <h1>Детали заказа</h1>
    <div class="order-summary" id="order-summary">
        <!-- Содержимое блока -->
        <div class="order-summary-content">
            <!-- Содержимое заказа -->
            <div class="order-detail">
                <h3>Тип:</h3> <p><?php echo htmlspecialchars($order['type']); ?></p>
            </div>
            <div class="order-detail">
                <h3>Цвет:</h3> <p><?php echo htmlspecialchars($order['color']); ?></p>
            </div>
            <div class="order-detail">
                <h3>Размер:</h3> <p><?php echo htmlspecialchars($order['size']); ?></p>
            </div>
            <div class="order-detail">
                <h3>Материал:</h3> <p><?php echo htmlspecialchars($order['material']); ?></p>
            </div>
            <div class="order-detail">
                <h3>Количество:</h3> <p><?php echo htmlspecialchars($order['quantity']); ?></p>
            </div>
            <div class="order-detail">
                <h3>Общая стоимость:</h3> <p><?php echo htmlspecialchars($order['total_cost']); ?> руб.</p>
            </div>
            <div class="order-detail-side">
                <h3 class="print-sides-header">Стороны нанесения:</h3>
                <div class="print-sides">
                    <?php foreach ($order['print_sides'] as $side): ?>
                        <p><span class="marker">•</span> <?php echo htmlspecialchars($side['name']); ?>: <?php echo htmlspecialchars($side['dimensions']); ?></p>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php if ($order['payment'] == 0): ?>
                <form id="payment-form" class="payment-form">
                    <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['id']); ?>">
                    <button type="submit" class="pay-button">Оплатить</button>
                </form>
            <?php else: ?>
                <p class="payment-status">Оплачено</p> <!-- Обновлено -->
            <?php endif; ?>
        </div>

        <div id="logo-container">
            <img src="/public/assets/image/logo1.png" alt="Mandarin Logo" id="logo" />
        </div>
    </div>
</div>
</body>
</html>
