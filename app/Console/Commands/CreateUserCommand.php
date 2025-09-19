<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateUserCommand extends Command
{
    protected $signature = 'user:create 
                            {name : Имя пользователя}
                            {email : Email пользователя}
                            {password : Пароль}
                            {--role= : Роль пользователя (operator/manager). Если не указано спросить}';

    protected $description = 'Создать нового пользователя с ролью';

    public function handle()
    {
        $name = $this->argument('name');
        $email = $this->argument('email');
        $password = $this->argument('password');
        
        $role = $this->option('role');
        if (!$role) {
            $role = $this->choice(
                'Выберите роль пользователя',
                ['operator', 'manager'],
                0
            );
        }

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => $role
        ], [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:operator,manager'
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return 1;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => $role,
        ]);

        $this->info("Пользователь успешно создан!");
        $this->line("Имя: {$user->name}");
        $this->line("Email: {$user->email}");
        $this->line("Роль: " . $this->getRoleName($user->role));
        $this->line("ID: {$user->id}");

        return 0;
    }

    protected function getRoleName($role)
    {
        return $role === 'manager' ? 'Руководитель' : 'Оператор';
    }
}