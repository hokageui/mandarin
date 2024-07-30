$(document).ready(function() {
    var isRequestInProgress = false;

    function loadSizes(typeId) {
        if (isRequestInProgress) return;

        isRequestInProgress = true;
        $.ajax({
            url: '/calculator/sizes',
            type: 'POST',
            data: { type_id: typeId },
            headers: {
                'Accept': 'application/json, text/javascript, */*; q=0.01'
            },
            success: function(response) {
                isRequestInProgress = false;
                var sizeContainer = $('#size-container');
                sizeContainer.empty();
                if (Array.isArray(response)) {
                    response.forEach(function(size, index) {
                        sizeContainer.append(
                            `<label>
                                <input type="radio" name="size" value="${size.id}" ${index === 0 ? 'checked' : ''}> 
                                ${size.size}
                            </label><br>`
                        );
                    });
                } else {
                    console.error('Response is not an array:', response);
                }
            },
            error: function(xhr, status, error) {
                isRequestInProgress = false;
                console.error('Ошибка при загрузке размеров:', error);
            }
        });
    }

    var defaultTypeId = $('input[name="type"]:checked').val();
    loadSizes(defaultTypeId);

    $('.tshirt-type-label').click(function() {
        $(this).find('.tshirt-type').prop('checked', true);
        var typeId = $(this).find('.tshirt-type').val();
        loadSizes(typeId);
    });

    $('#calculate-btn').click(function() {
        if (isRequestInProgress) return;

        isRequestInProgress = true;

        var invalidInputs = false;
        var sidesSelected = false;
        var errorMessage = $('#error-message');
        errorMessage.hide().text('');

        var sides = {};
        var isQuantityInvalid = false;
        var quantityInput = $('#quantity');
        var quantity = quantityInput.val();
        var quantityError = quantityInput.next('.quantity-error');
        quantityError.hide();

        if (quantity === '0' || quantity === '') {
            quantityError.text('Тираж не может быть равен 0').show();
            isQuantityInvalid = true;
        }

        $('.print-checkbox:checked').each(function() {
            sidesSelected = true;
            var side = $(this).data('side');
            var widthInput = $(this).closest('.checkbox-wrapper').find('.print-width');
            var heightInput = $(this).closest('.checkbox-wrapper').find('.print-height');

            var width = widthInput.val();
            var height = heightInput.val();
            var widthError = widthInput.next('.error-text');
            var heightError = heightInput.next('.error-text');
            widthError.hide();
            heightError.hide();

            var valid = true;

            if (width && height) {
                width = parseInt(width, 10);
                height = parseInt(height, 10);

                if (side === 'breast' || side === 'back') {
                    if (width < 10) {
                        widthError.text('Мин. ширина нанесения 10мм').show();
                        valid = false;
                    } else if (width > 418) {
                        widthError.text('Макс. ширина нанесения 418мм').show();
                        valid = false;
                    }

                    if (height < 10) {
                        heightError.text('Мин. высота нанесения 10мм').show();
                        valid = false;
                    } else if (height > 295) {
                        heightError.text('Макс. высота нанесения 295мм').show();
                        valid = false;
                    }

                    if (valid) {
                        sides[`sides[${side}]`] = 1;
                        sides[`${side}[width]`] = width;
                        sides[`${side}[height]`] = height;
                    }
                } else if (side === 'right_shoulder' || side === 'left_shoulder') {
                    if (width < 10) {
                        widthError.text('Мин. ширина нанесения 10мм').show();
                        valid = false;
                    } else if (width > 160) {
                        widthError.text('Макс. ширина нанесения 160мм').show();
                        valid = false;
                    }

                    if (height < 10) {
                        heightError.text('Мин. высота нанесения 10мм').show();
                        valid = false;
                    } else if (height > 180) {
                        heightError.text('Макс. высота нанесения 180мм').show();
                        valid = false;
                    }

                    if (valid) {
                        sides[`sides[${side}]`] = 1;
                        sides[`${side}[width]`] = width;
                        sides[`${side}[height]`] = height;
                    }
                }
            } else {
                if (!width) widthError.text('Заполните ширину').show();
                if (!height) heightError.text('Заполните высоту').show();
                valid = false;
            }

            if (!valid) {
                invalidInputs = true;
            }
        });

        if (isQuantityInvalid) {
            errorMessage.show().text('Заполните корректно поле тираж и параметры нанесения.');
            isRequestInProgress = false;
            return;
        }

        if (!sidesSelected) {
            errorMessage.show().text('Выберите стороны нанесения.');
            isRequestInProgress = false;
            return;
        }

        if (invalidInputs) {
            errorMessage.show().text('Убедитесь, что ширина и высота нанесения корректны.');
            isRequestInProgress = false;
            return;
        }

        var formData = $('#calculator-form').serializeArray();
        $.each(sides, function(key, value) {
            formData.push({ name: key, value: value });
        });

        $.ajax({
            url: '/calculator/calculate',
            type: 'POST',
            data: $.param(formData),
            contentType: 'application/x-www-form-urlencoded',
            headers: {
                'Accept': 'application/json, text/javascript, */*; q=0.01'
            },
            success: function(response) {
                isRequestInProgress = false;
                if (response.status === 'success') {
                    // Обновляем и показываем контейнер с результатом
                    var resultHtml = `
<div class="result-container">
    <p class="tirage-text">Отдельный тираж, ${response.quantity} экз.</p>
    <p class="print-text">Печать на футболках</p>
    <p class="production-time-text">Печать за ${response.production_time} рабочий день</p>
    <p class="total-price-text"><span class="total-price">${response.price}</span></p>
    <button type="button" id="save-order-btn" class="zakazbtn">Оформить заказ</button>
</div>
                    `;

                    $('#result-container').html(resultHtml).show();
                } else {
                    $('#result').html(response.message);
                }
            },
            error: function(xhr, status, error) {
                isRequestInProgress = false;
                console.error('Ошибка при отправке запроса:', error);
                $('#result').html('Произошла ошибка при расчете стоимости.');
            }
        });
    });

    $('#result-container').on('click', '#save-order-btn', function() {
        if (isRequestInProgress) return;

        isRequestInProgress = true;

        var sides = {};
        $('.print-checkbox:checked').each(function() {
            var side = $(this).data('side');
            var widthInput = $(this).closest('.checkbox-wrapper').find('.print-width');
            var heightInput = $(this).closest('.checkbox-wrapper').find('.print-height');

            var width = widthInput.val();
            var height = heightInput.val();

            if (width && height) {
                width = parseInt(width, 10);
                height = parseInt(height, 10);

                if (side === 'breast' || side === 'back') {
                    sides[`sides[${side}]`] = 1;
                    sides[`${side}[width]`] = width;
                    sides[`${side}[height]`] = height;
                } else if (side === 'right_shoulder' || side === 'left_shoulder') {
                    sides[`sides[${side}]`] = 1;
                    sides[`${side}[width]`] = width;
                    sides[`${side}[height]`] = height;
                }
            }
        });

        var formData = $('#calculator-form').serializeArray();
        $.each(sides, function(key, value) {
            formData.push({ name: key, value: value });
        });

        $.ajax({
            url: '/calculator/save_order',
            type: 'POST',
            data: $.param(formData),
            contentType: 'application/x-www-form-urlencoded',
            headers: {
                'Accept': 'application/json, text/javascript, */*; q=0.01'
            },
            success: function(response) {
                isRequestInProgress = false;
                if (response.status === 'success') {
                    // Перенаправляем пользователя на страницу заказа
                    window.location.href = `/order/${response.order_id}`;
                } else {
                    alert('Ошибка при оформлении заказа: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                isRequestInProgress = false;
                console.error('Ошибка при отправке запроса:', error);
                alert('Произошла ошибка при оформлении заказа.');
            }
        });
    });

    $('.print-checkbox').change(function() {
        var container = $(this).closest('.checkbox-wrapper');
        var optionElement = container.find('.print-option');

        if (this.checked) {
            var newElement = createPrintOptionElement($(this).data('side'));
            container.append(newElement);
        } else {
            container.find('.print-option').remove();
        }
    });

    function createPrintOptionElement(side) {
        console.log(`Creating print option for side: ${side}`); // Отладка

        var formatOptions = [
            { value: 'Другой', text: 'Другой', width: null, height: null },
            { value: 'A7', text: 'A7', width: 74, height: 105 },
            { value: 'A6', text: 'A6', width: 105, height: 148 },
            { value: 'A5', text: 'A5', width: 148, height: 210 },
            { value: 'A4', text: 'A4', width: 210, height: 297 }
        ];

        if (side === 'left_shoulder' || side === 'right_shoulder') {
            formatOptions = formatOptions.filter(option => option.value === 'A6' || option.value === 'A7' || option.value === 'Другой');
        }

        var container = $('<div class="print-option"></div>');

        var formatLabel = $('<label class="format-text">Формат</label>');
        var formatSelect = $('<select class="print-format"></select>');
        var widthLabel = $('<label class="format-text">Ширина</label>');
        var widthInput = $('<input type="number" class="print-width" placeholder="0" inputmode="numeric" pattern="[0-9]*" min="0" max="999">');
        var widthError = $('<span class="error-text">Мин. ширина нанесения 10мм</span>');
        var heightLabel = $('<label class="format-text">Высота</label>');
        var heightInput = $('<input type="number" class="print-height" placeholder="0" inputmode="numeric" pattern="[0-9]*" min="0" max="999">');
        var heightError = $('<span class="error-text">Мин. высота нанесения 10мм</span>');

        formatOptions.forEach(function(option) {
            formatSelect.append($('<option></option>').attr('value', option.value).text(option.text));
        });

        formatSelect.change(function() {
            var selectedOption = formatOptions.find(option => option.value === formatSelect.val());
            if (selectedOption) {
                widthInput.val(selectedOption.width);
                heightInput.val(selectedOption.height);
                widthError.hide();
                heightError.hide();
            }
        });

        widthInput.on('input', function() {
            var value = $(this).val().slice(0, 3);
            $(this).val(value);
            updateFormatOptions($(this), widthInput, heightInput);
        });

        heightInput.on('input', function() {
            var value = $(this).val().slice(0, 3);
            $(this).val(value);
            updateFormatOptions($(this), widthInput, heightInput);
        });

        var inputGroup = $('<div class="input-group"></div>');
        inputGroup.append(
            formatLabel,
            formatSelect,
            widthLabel,
            widthInput,
            widthError,
            heightLabel,
            heightInput,
            heightError
        );

        container.append(inputGroup);
        return container;
    }

    function updateFormatOptions(inputElement, widthInput, heightInput) {
        var formatOptions = [
            { value: 'Другой', text: 'Другой', width: null, height: null },
            { value: 'A7', text: 'A7', width: 74, height: 105 },
            { value: 'A6', text: 'A6', width: 105, height: 148 },
            { value: 'A5', text: 'A5', width: 148, height: 210 },
            { value: 'A4', text: 'A4', width: 210, height: 297 }
        ];

        if (inputElement.closest('.print-option').parent().data('side') === 'left_shoulder' ||
            inputElement.closest('.print-option').parent().data('side') === 'right_shoulder') {
            formatOptions = formatOptions.filter(option => option.value === 'A6' || option.value === 'A7' || option.value === 'Другой');
        }

        var width = parseInt(widthInput.val(), 10);
        var height = parseInt(heightInput.val(), 10);

        var option = formatOptions.find(o => o.width === width && o.height === height);
        if (option) {
            inputElement.closest('.print-option').find('.print-format').val(option.value);
        } else {
            inputElement.closest('.print-option').find('.print-format').val('Другой');
        }
    }

    document.getElementById('calculate-btn').addEventListener('click', function() {
        document.getElementById('result-container').style.display = 'block';
        setTimeout(() => {
            document.getElementById('result-container').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 100);
    });
});
