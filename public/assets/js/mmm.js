document.addEventListener('DOMContentLoaded', function () {
    function showLoginPopup() {
        document.getElementById('popup-login').style.display = 'block';
        document.getElementById('overlay').style.display = 'block';
    }

    // Функция для скрытия попапа
    function hideLoginPopup() {
        document.getElementById('popup-login').style.display = 'none';
        document.getElementById('overlay').style.display = 'none';
    }
    function showNavButtons() {
        var navButtons = document.getElementById('nav-buttons');
        if (navButtons) {
            navButtons.style.display = 'flex'; // Или 'block', если требуется
        } else {
            console.error('Блок навигации не найден');
        }
    }

    // Функция для скрытия блока навигации
    function hideNavButtons() {
        var navButtons = document.getElementById('nav-buttons');
        if (navButtons) {
            navButtons.style.display = 'none';
        } else {
            console.error('Блок навигации не найден');
        }
    }

    // Проверка, авторизован ли пользователь
    function checkAuthStatus() {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '/auth/check_auth', true);
        xhr.setRequestHeader('Content-Type', 'application/json'); // Указываем, что ожидаем JSON
        xhr.setRequestHeader('Accept', 'application/json'); // Указываем, что ожидаем JSON
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                var contentType = xhr.getResponseHeader('Content-Type');
                if (contentType && contentType.includes('application/json')) {
                    var data = JSON.parse(xhr.responseText);
                    if (data.authenticated) {
                        hideLoginPopup(); // Скрываем попап
                        showNavButtons(); // Показываем навигацию
                    } else {
                        showLoginPopup(); // Показываем попап
                        hideNavButtons(); // Скрываем навигацию
                    }
                } else {
                    console.error('Ожидался JSON, но получен другой тип контента:', contentType);
                }
            } else {
                console.error('Ошибка при проверке авторизации:', xhr.status, xhr.statusText);
            }
        };
        xhr.onerror = function() {
            console.error('Ошибка сети.');
        };
        xhr.send(JSON.stringify({})); // Отправляем пустой JSON объект
    }


    checkAuthStatus();

    // Отправка формы логина
    document.querySelector('.login-form').addEventListener('submit', function (e) {
        e.preventDefault(); // Отменяем стандартное поведение формы

        var formData = new FormData(this);
        var loginData = {
            login: formData.get('login'),
            password: formData.get('password'),
            remember: formData.get('remember') || false
        };

        console.log('Отправляемые данные:', loginData); // Выводим данные для проверки

        var xhr = new XMLHttpRequest();
        xhr.open('POST', '/auth/login', true);
        xhr.setRequestHeader('Content-Type', 'application/json'); // Указываем, что ожидаем JSON

        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                var contentType = xhr.getResponseHeader('Content-Type');
                console.log('Ответ от сервера:', xhr.responseText); // Выводим ответ сервера для проверки

                if (contentType && contentType.includes('application/json')) {
                    try {
                        var data = JSON.parse(xhr.responseText);
                        if (data.success) {
                            hideLoginPopup();
                            window.location.reload();
                        } else {
                            alert('Неверный логин или пароль.');
                        }
                    } catch (e) {
                        console.error('Ошибка при разборе JSON:', e);
                    }
                } else {
                    console.error('Ожидался JSON, но получен другой тип контента:', contentType);
                }
            } else {
                console.error('Ошибка при логине:', xhr.status, xhr.statusText);
            }
        };

        xhr.onerror = function() {
            console.error('Ошибка сети.');
        };

        xhr.send(JSON.stringify(loginData));
    });

    // Обработчик нажатия на кнопку выхода
    document.getElementById('logout-button').addEventListener('click', function() {
        fetch('/auth/logout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({}) // Отправляем пустой JSON объект
        })
        .then(response => response.json()) // Ожидаем JSON ответ
        .then(data => {
            if (data.success) {
                // Если выход прошел успешно, перенаправляем пользователя
                window.location.href = '/'; // Перенаправляем на главную страницу
            } else {
                alert('Не удалось выйти из системы. Попробуйте еще раз.');
            }
        })
        .catch(error => {
            console.error('Ошибка при выходе:', error);
            alert('Не удалось выйти из системы. Попробуйте еще раз.');
        });
    });


    $(document).ready(function() {

        $('#logobtn').on('click', function() {
            window.location.href = '/'; // Перенаправление на главную страницу сайта
        });

        $('#orderbtn').on('click', function() {
            window.location.href = '/orderlist';
        });

        // Обработка формы оплаты
        $('#payment-form').submit(function(e) {
            e.preventDefault();

            var $orderSummary = $('#order-summary');
            var $logoContainer = $('#logo-container');
            var orderId = $('input[name="order_id"]').val(); // Получаем ID заказа из формы

            // Проверяем, что orderId не пустой
            if (!orderId) {
                alert('ID заказа не найден.');
                return;
            }

            // Скрываем содержимое и показываем логотип
            $orderSummary.find('.order-summary-content').fadeOut(500, function() {
                $logoContainer.fadeIn(500);

                // Отправляем данные на сервер
                $.ajax({
                    type: 'POST',
                    url: '/update_payment', // Обновите URL, чтобы он соответствовал новому маршруту
                    contentType: 'application/json; charset=utf-8',
                    dataType: 'json',
                    data: JSON.stringify({ order_id: orderId }),
                    success: function(response) {
                        console.log('Response:', response); // отладочный вывод

                        // Обновляем #order-summary после завершения анимации
                        setTimeout(function() {
                            $logoContainer.fadeOut(500, function() {
                                // Обновляем содержимое #order-summary, если сервер вернул обновленные данные
                                if (response.success) {
                                    $orderSummary.load('/order/details/' + orderId + ' #order-summary > *'); // Замените URL на ваш маршрут для получения обновленного контента
                                }
                                $orderSummary.find('.order-summary-content').fadeIn(500); // Показываем содержимое снова
                            });
                        }, 5000);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', status, error); // отладочный вывод
                        alert('Ошибка при обработке оплаты. Попробуйте снова.');
                    }
                });
            });
        });
    });
});
