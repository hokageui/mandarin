<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ваши заказы</title>
    <link rel="stylesheet" type="text/css" href="/public/assets/styles/style.css">
    <style>
        .container-order {
            width: 100%; /* Контейнер занимает всю доступную ширину */
            display: flex;
            flex-direction: column;
            align-items: center; /* Центрирование содержимого по горизонтали */
            padding: 0 20px; /* Отступы по бокам для предотвращения выхода за границы */
            box-sizing: border-box; /* Учитываем паддинги и границы в ширину */
        }
        h1 {
            width: 100%; /* Ширина заголовка на всю доступную ширину */
            max-width: 600px; /* Максимальная ширина заголовка */
            margin: 5px 0; /* Отступы сверху и снизу */
            padding: 0; /* Убираем паддинги */
            text-align: left; /* Выравнивание текста по левому краю */
            box-sizing: border-box; /* Учитываем границы и паддинги в ширину */
        }
        .order-list {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0;
            margin: 0;
            width: 100%; /* Ширина на всю доступную ширину */
            max-width: 600px; /* Максимальная ширина контейнера */
        }
        .order-item {
            position: relative;
            border: 1px solid #ddd;
            padding: 15px;
            margin: 10px 0; /* Отступы сверху и снизу */
            width: 100%; /* Ширина элемента заказа 100% от родителя (макс 600px) */
            max-width: 600px; /* Фиксированная максимальная ширина элемента заказа */
            text-align: left;
            background-color: #fafafa;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            overflow: hidden;
            transition: box-shadow 0.2s ease;
            box-sizing: border-box; /* Учитываем границы и паддинги в ширину */
        }
        .order-item::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            width: 0;
            background-color: #ff5800;
            transition: width 0.6s ease; /* Увеличиваем продолжительность анимации */
        }
        .order-item:hover::after {
            width: 100%;
        }
        .order-item p {
            margin: 5px 0;
        }
        .payment-status {
            padding: 5px 10px;
            border-radius: 4px;
            color: white;
            text-align: center;
            margin-left: 15px;
            flex-shrink: 0;
            font-weight: bold;
        }
        .paid {
            background-color: #8ca464;
        }
        .unpaid {
            background-color: #ff5800;
        }
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 20px 0;
            width: 100%; /* Ширина на всю доступную ширину */
            max-width: 600px; /* Максимальная ширина пагинации */
        }
        .pagination button,
        .pagination .page-number {
            background-color: #f0f0f0;
            border: none;
            padding: 10px 15px;
            margin: 0 5px;
            cursor: pointer;
            border-radius: 4px;
            font-size: 14px;
            color: #333;
            transition: background-color 0.3s, transform 0.2s;
        }
        .pagination button:hover,
        .pagination .page-number.active {
            background-color: #e0e0e0;
        }
        .pagination button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
<div class="container-order">
    <h1>Ваши заказы</h1>
    <div class="order-list" id="order-list">
        <!-- Заказы будут загружены с помощью jQuery -->
    </div>

    <div class="pagination" id="pagination-controls">
        <!-- Кнопки пагинации будут загружены с помощью jQuery -->
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
    $(document).ready(function() {
        const orders = <?php echo json_encode($orders); ?>;
        const ordersPerPage = 10;
        let currentPage = 1;

        function renderOrders(page) {
            const start = (page - 1) * ordersPerPage;
            const end = Math.min(start + ordersPerPage, orders.length);
            $('#order-list').empty();

            for (let i = start; i < end; i++) {
                const order = orders[i];
                const paymentClass = order.payment == 1 ? 'paid' : 'unpaid';
                $('#order-list').append(`
                <div class="order-item" data-id="${order.id}">
                    <div>
                        <p><strong>ID заказа:</strong> ${order.id}</p>
                        <p><strong>Дата создания:</strong> ${order.created_at}</p>
                        <p><strong>Общая стоимость:</strong> ${order.total_cost} руб.</p>
                    </div>
                    <div class="payment-status ${paymentClass}">
                        ${order.payment == 1 ? 'Оплачено' : 'Не оплачено'}
                    </div>
                </div>
            `);
            }

            updatePagination(page);
        }

        function updatePagination(page) {
            const totalPages = Math.ceil(orders.length / ordersPerPage);
            let paginationHtml = '';

            if (page > 1) {
                paginationHtml += `<button class="pagination-btn" data-page="${page - 1}">&laquo; Предыдущая</button>`;
            }

            for (let i = 1; i <= totalPages; i++) {
                paginationHtml += `<div class="page-number ${i === page ? 'active' : ''}" data-page="${i}">${i}</div>`;
            }

            if (page < totalPages) {
                paginationHtml += `<button class="pagination-btn" data-page="${page + 1}">Следующая &raquo;</button>`;
            }

            $('#pagination-controls').html(paginationHtml);
        }

        $(document).on('click', '.page-number', function() {
            const page = $(this).data('page');
            if (page && page !== currentPage) {
                currentPage = page;
                renderOrders(currentPage);
            }
        });

        $(document).on('click', '.pagination-btn', function() {
            const page = $(this).data('page');
            if (page && page !== currentPage) {
                currentPage = page;
                renderOrders(currentPage);
            }
        });

        $(document).on('click', '.order-item', function() {
            const id = $(this).data('id');
            window.location.href = `/order/${id}`;
        });

        renderOrders(currentPage);
    });
</script>
</body>
</html>
