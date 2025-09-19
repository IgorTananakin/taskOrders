<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        $users = [
            [
                'name' => 'Руководитель Иванов',
                'email' => 'testmanager@example.com',
                'password' => Hash::make('password'),
                'role' => 'manager',
            ],
            [
                'name' => 'Оператор Петров',
                'email' => 'testoperator@example.com',
                'password' => Hash::make('password'),
                'role' => 'operator',
            ],
            [
                'name' => 'Оператор Сидорова',
                'email' => 'testoperator2@example.com',
                'password' => Hash::make('password'),
                'role' => 'operator',
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }

        $this->command->info('Пользователи созданы успешно!');
    }
}