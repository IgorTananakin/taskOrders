<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Создание заказа - TaskOrders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/orders/create.css') }}" rel="stylesheet">
</head>
<body class="bg-light">
    
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="/">TaskOrders</a>
            
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    {{ Auth::user()->name }} ({{ Auth::user()->role === 'manager' ? 'Руководитель' : 'Оператор' }})
                </span>
                <form action="/logout" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm">Выйти</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container order-container py-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Создание Заказа</h4>
            </div>
            
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="/orders" method="POST">
                    @csrf
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Фамилия *</label>
                            <input type="text" class="form-control" name="last_name" 
                                value="{{ old('last_name') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Имя *</label>
                            <input type="text" class="form-control" name="first_name" 
                                value="{{ old('first_name') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Отчество</label>
                            <input type="text" class="form-control" name="middle_name" 
                                value="{{ old('middle_name') }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Телефон *</label>
                        <input type="tel" class="form-control" id="phone" name="phone" 
                               value="{{ old('phone') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Почта *</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="{{ old('email') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="inn" class="form-label">ИНН *</label>
                        <input type="text" class="form-control" id="inn" name="inn" 
                               value="{{ old('inn') }}" required maxlength="12" minlength="10">
                    </div>

                    <div class="mb-3">
                        <label for="company_name" class="form-label">Название компании *</label>
                        <input type="text" class="form-control" id="company_name" name="company_name" 
                               value="{{ old('company_name') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Адрес *</label>
                        <input type="text" class="form-control" id="address" name="address" 
                               value="{{ old('address') }}" required>
                    </div>

                    <hr>
                    
                    <h5 class="mb-3">Товары в заказе *</h5>

                    <div class="row product-row">
                        <div class="col-md-4">
                            <label class="form-label">Товар *</label>
                            <input type="text" class="form-control" name="products[0][name]" 
                                   placeholder="Название товара" value="{{ old('products.0.name') }}" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Кол-во *</label>
                            <input type="number" class="form-control" name="products[0][quantity]" 
                                   min="1" value="{{ old('products.0.quantity', 1) }}" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Ед. измерения *</label>
                            <select class="form-select" name="products[0][unit]" required>
                                <option value="шт." {{ old('products.0.unit') == 'шт.' ? 'selected' : '' }}>шт.</option>
                                <option value="кг" {{ old('products.0.unit') == 'кг' ? 'selected' : '' }}>кг</option>
                                <option value="м" {{ old('products.0.unit') == 'м' ? 'selected' : '' }}>м</option>
                                <option value="м²" {{ old('products.0.unit') == 'м²' ? 'selected' : '' }}>м²</option>
                                <option value="л" {{ old('products.0.unit') == 'л' ? 'selected' : '' }}>л</option>
                                <option value="упак." {{ old('products.0.unit') == 'упак.' ? 'selected' : '' }}>упак.</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Цена *</label>
                            <input type="number" class="form-control" name="products[0][price]" 
                                   step="0.01" min="0" value="{{ old('products.0.price', 0) }}" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Сумма</label>
                            <input type="text" class="form-control" readonly value="0.00">
                        </div>
                    </div>

                    <div class="row product-row">
                        <div class="col-md-4">
                            <label class="form-label">Товар</label>
                            <input type="text" class="form-control" name="products[1][name]" 
                                   placeholder="Название товара" value="{{ old('products.1.name') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Кол-во</label>
                            <input type="number" class="form-control" name="products[1][quantity]" 
                                   min="1" value="{{ old('products.1.quantity', 1) }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Ед. измерения</label>
                            <select class="form-select" name="products[1][unit]">
                                <option value="шт." {{ old('products.1.unit') == 'шт.' ? 'selected' : '' }}>шт.</option>
                                <option value="кг" {{ old('products.1.unit') == 'кг' ? 'selected' : '' }}>кг</option>
                                <option value="м" {{ old('products.1.unit') == 'м' ? 'selected' : '' }}>м</option>
                                <option value="м²" {{ old('products.1.unit') == 'м²' ? 'selected' : '' }}>м²</option>
                                <option value="л" {{ old('products.1.unit') == 'л' ? 'selected' : '' }}>л</option>
                                <option value="упак." {{ old('products.1.unit') == 'упак.' ? 'selected' : '' }}>упак.</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Цена</label>
                            <input type="number" class="form-control" name="products[1][price]" 
                                   step="0.01" min="0" value="{{ old('products.1.price', 0) }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Сумма</label>
                            <input type="text" class="form-control" readonly value="0.00">
                        </div>
                    </div>

                    <hr>

                    <div class="text-center">
                        <button type="submit" class="btn btn-success btn-lg">
                            Создать заказ
                        </button>
                        <a href="/" class="btn btn-secondary btn-lg ms-2">
                            На главную
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // рассчёт суммы заказа
        document.addEventListener('DOMContentLoaded', function() {
            const calculateTotal = function(input) {
                const row = input.closest('.row');
                const quantityInput = row.querySelector('input[name*="[quantity]"]');
                const priceInput = row.querySelector('input[name*="[price]"]');
                const totalInput = row.querySelector('input[readonly]');
                
                if (quantityInput && priceInput && totalInput) {
                    const quantity = parseFloat(quantityInput.value) || 0;
                    const price = parseFloat(priceInput.value) || 0;
                    const total = quantity * price;
                    totalInput.value = total.toFixed(2);
                }
            };

            //для изменения количества и цены
            document.querySelectorAll('input[name*="[quantity]"], input[name*="[price]"]').forEach(input => {
                input.addEventListener('input', function() {
                    calculateTotal(this);
                });
                
                calculateTotal(input);
            });
        });
    </script>
</body>
</html>