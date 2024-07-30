<!DOCTYPE html>
<html>
<head>
    <title>Калькулятор стоимости футболок</title>
    <script src="/public/assets/js/obf.js"></script>
</head>
<body>

<div id="product-card" class="product-card">
    <span class="slider-text">"product"</span>
</div>

<div class="container">
    <h1>Футболки</h1>
    <form id="calculator-form">
        <h3>Футболка</h3>
        <div class="tshirt-type-container">
            <?php foreach ($types as $index => $type): ?>
                <label class="tshirt-type-label">
                    <input type="radio" name="type" value="<?php echo $type->id; ?>" class="tshirt-type" <?php echo $index === 0 ? 'checked' : ''; ?>>
                    <?php echo $type->type; ?>
                </label>
            <?php endforeach; ?>
        </div>

        <h3>Выберите цвет</h3>
        <div class="color-container">
            <?php foreach ($colors as $index => $color): ?>
                <div class="color-item">
                    <label>
                        <input type="radio" name="color" value="<?php echo $color->id; ?>" <?php echo $index === 0 ? 'checked' : ''; ?>>
                        <?php echo $color->color; ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>

        <h3>Выберите размер</h3>
        <div id="size-container" class="tshirt-type-container" >

        </div>

        <h3>Выберите материал</h3>
        <?php foreach ($materials as $index => $material): ?>
            <label>
                <input type="radio" name="material" value="<?php echo $material->id; ?>" <?php echo $index === 0 ? 'checked' : ''; ?>>
                <?php echo $material->material; ?>
            </label><br>
        <?php endforeach; ?>

        <h3>Выберите стороны нанесения</h3>
        <div class="print-options-container">
            <div class="checkbox-wrapper">
                <div class="form-group">
                    <label>
                        <input type="checkbox" class="print-checkbox" data-side="breast"> Грудь
                    </label>
                </div>
                <div class="dropdown-wrapper" id="print-option-breast">
                    <div class="print-option"></div>
                </div>
            </div>
            <br>

            <div class="checkbox-wrapper">
                <div class="form-group">
                    <label>
                        <input type="checkbox" class="print-checkbox" data-side="back"> Спина
                    </label>
                </div>
                <div class="dropdown-wrapper" id="print-option-back">
                    <div class="print-option"></div>
                </div>
            </div>
            <br>

            <div class="checkbox-wrapper">
                <div class="form-group">
                    <label>
                        <input type="checkbox" class="print-checkbox" data-side="right_shoulder"> Правое плечо
                    </label>
                </div>
                <div class="dropdown-wrapper" id="print-option-right_shoulder">
                    <div class="print-option"></div>
                </div>
            </div>
            <br>

            <div class="checkbox-wrapper">
                <div class="form-group">
                    <label>
                        <input type="checkbox" class="print-checkbox" data-side="left_shoulder"> Левое плечо
                    </label>
                </div>
                <div class="dropdown-wrapper" id="print-option-left_shoulder">
                    <div class="print-option"></div>
                </div>
            </div>
            <br>
        </div>

        <h3>Тип переноса <span class="transfer-type-info" data-description="Сублимационная печать подходит для синтетических тканей с высоким содержанием полиэстера. Она обеспечивает яркие и стойкие изображения, которые долго не выцветают. Сублимационная печать: нанесение полноцветных логотипов и изображений на белые или светлые футболки из синтетического материала.">?</span></h3>
        <div class="tshirt-type-container">
            <label>
                <input type="radio" name="transfer_type" value="1" checked>
                Сублимация
            </label>
            <br>
            <label>
                <input type="radio" name="transfer_type" value="2">
                Термоперенос
            </label>
            <br>
            <label>
                <input type="radio" name="transfer_type" value="3">
                Термоаппликация
            </label>
        </div>

        <div class="form-group">
            <h3>Тираж</h3>
            <label>
                <input type="number" name="quantity" id="quantity" min="1" placeholder="0">
            </label>
        </div>

        <button type="button" id="calculate-btn" class="property-1-variant-3 button">Рассчитать стоимость</button>
        <div id="error-message" style="color: red; display: none; margin-bottom: 10px;"></div>
    </form>
</div>

<div id="result-container" style="display: none;" id="result-container">
    <!-- Динамически заполняемый контент будет добавлен здесь -->
</div>


<script>
    $(document).ready(function() {
        // Функция для обновления подсказки при выборе типа переноса
        $('input[name="transfer_type"]').change(function() {
            var description = '';
            if ($(this).val() === '1') {
                description = 'Сублимационная печать подходит для синтетических тканей с высоким содержанием полиэстера. Она обеспечивает яркие и стойкие изображения, которые долго не выцветают. Сублимационная печать: нанесение полноцветных логотипов и изображений на белые или светлые футболки из синтетического материала.';
            } else if ($(this).val() === '2') {
                description = 'Термоперенос используют для натуральных и синтетических тканей, таких как хлопок и лён. Он позволяет создавать чёткие и насыщенные картинки, которые выдерживают многократные стирки. Термоперенос: печать на футболках из хлопка или смесовых тканей, включая тёмные и цветные футболки.';
            } else if ($(this).val() === '3') {
                description = 'Термоаппликация подходит для натуральных тканей, например хлопка и льна. С её помощью можно украсить футболки и другую одежду аппликациями, которые хорошо держатся на ткани и не выцветают. Термоаппликация: декорирование футболок с использованием плёнок с различными эффектами, таких как металлик, глиттер, суперэластичность и водостойкость.';
            }
            $('.transfer-type-info').attr('data-description', description);
        });

        // Инициализация для первоначальной установки описания
        $('input[name="transfer_type"]:checked').change();
    });
</script>
</body>
</html>
