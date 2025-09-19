<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $products = [
            ['name' => 'Плитка ПК 12-5-2', 'unit' => 'шт.'],
            ['name' => 'Кирпич красный', 'unit' => 'шт.'],
            ['name' => 'Цемент М500', 'unit' => 'кг'],
            ['name' => 'Песок речной', 'unit' => 'т'],
            ['name' => 'Гипсокартон', 'unit' => 'лист'],
            ['name' => 'Краска белая', 'unit' => 'л'],
            ['name' => 'Обои флизелиновые', 'unit' => 'рулон'],
            ['name' => 'Линолеум', 'unit' => 'м²'],
            ['name' => 'Лампа светодиодная', 'unit' => 'шт.'],
            ['name' => 'Розетка электрическая', 'unit' => 'шт.'],
            ['name' => 'Выключатель', 'unit' => 'шт.'],
            ['name' => 'Кабель ВВГ', 'unit' => 'м'],
            ['name' => 'Труба ПВХ', 'unit' => 'м'],
            ['name' => 'Кран шаровой', 'unit' => 'шт.'],
            ['name' => 'Уголок металлический', 'unit' => 'шт.'],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }

        $this->command->info('Товары созданы успешно!');
    }
}