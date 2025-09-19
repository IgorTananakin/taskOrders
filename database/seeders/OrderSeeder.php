<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();
        $products = Product::all();
        
        $statuses = ['new', 'in_progress', 'completed'];
        
        for ($i = 0; $i < 50; $i++) {
            $user = $users->random();
            $status = $statuses[array_rand($statuses)];
            
            $orderDate = Carbon::now()->subDays(rand(0, 30));
            
            $order = Order::create([
                'user_id' => $user->id,
                'full_name' => $this->generateRandomName(),
                'phone' => $this->generateRandomPhone(),
                'email' => $this->generateRandomEmail(),
                'inn' => $this->generateRandomInn(),
                'company_name' => $this->generateRandomCompany(),
                'address' => $this->generateRandomAddress(),
                'status' => $status,
                'order_date' => $orderDate,
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ]);
            
            $orderProducts = $products->random(rand(1, 4));
            
            foreach ($orderProducts as $product) {
                $quantity = rand(1, 20);
                
                $order->products()->attach($product->id, [
                    'quantity' => $quantity,
                    'price' => rand(100, 5000),
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                ]);
            }
        }
        
        $this->command->info('Заказы созданы успешно!');
    }
    
    private function generateRandomName()
    {
        $lastNames = ['Иванов', 'Петров', 'Сидоров', 'Кузнецов', 'Смирнов', 'Попов', 'Васильев'];
        $firstNames = ['Иван', 'Петр', 'Алексей', 'Дмитрий', 'Сергей', 'Андрей', 'Михаил'];
        $middleNames = ['Иванович', 'Петрович', 'Алексеевич', 'Дмитриевич', 'Сергеевич'];
        
        return $lastNames[array_rand($lastNames)] . ' ' . 
               $firstNames[array_rand($firstNames)] . ' ' . 
               $middleNames[array_rand($middleNames)];
    }
    
    private function generateRandomPhone()
    {
        return '+7 (' . rand(900, 999) . ') ' . rand(100, 999) . '-' . rand(10, 99) . '-' . rand(10, 99);
    }
    
    private function generateRandomEmail()
    {
        $domains = ['gmail.com', 'mail.ru', 'yandex.ru', 'example.com'];
        $names = ['ivanov', 'petrov', 'sidorov', 'kuznetsov', 'smirnov', 'popov', 'vasilev'];
        $numbers = rand(100, 999);
        
        return $names[array_rand($names)] . $numbers . '@' . $domains[array_rand($domains)];
    }
    
    private function generateRandomInn()
    {
        return (string) rand(1000000000, 9999999999);
    }
    
    private function generateRandomCompany()
    {
        $types = ['ООО', 'ИП', 'ЗАО', 'АО'];
        $names = ['Ромашка', 'Стройматериалы', 'ПроектСтрой', 'ТехноСтрой', 'СтройГрупп', 'СтройИнвест'];
        
        return $types[array_rand($types)] . ' "' . $names[array_rand($names)] . '"';
    }
    
    private function generateRandomAddress()
    {
        $cities = ['г. Москва', 'г. Санкт-Петербург', 'г. Новосибирск', 'г. Екатеринбург', 'г. Казань'];
        $streets = ['ул. Ленина', 'ул. Пушкина', 'пр. Мира', 'ул. Советская', 'ул. Центральная'];
        
        return $cities[array_rand($cities)] . ', ' . 
               $streets[array_rand($streets)] . ', д. ' . rand(1, 100);
    }
}