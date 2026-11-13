<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CreateAdmin extends Command
{
    protected $signature = 'admin:create {login} {--generate : Generate a random password}';
    protected $description = 'Создать отдельную учётную запись администратора справочников';

    public function handle(): int
    {
        $login = trim($this->argument('login'));
        $password = $this->option('generate') ? Str::password(20) : $this->secret('Пароль (не менее 12 символов)');
        $validator = Validator::make(compact('login', 'password'), [
            'login' => ['required', 'string', 'max:100', 'unique:admins,login'],
            'password' => ['required', 'string', 'min:12', 'max:255'],
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) $this->error($message);
            return self::FAILURE;
        }
        Admin::create(compact('login', 'password'));
        $this->info("Администратор {$login} создан. Вход: /admin");
        if ($this->option('generate')) $this->line("Пароль: {$password}");
        return self::SUCCESS;
    }
}
