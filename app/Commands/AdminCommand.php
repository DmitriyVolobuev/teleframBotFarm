<?php

namespace App\Commands;

use Telegram\Bot\Commands\Command;
use Illuminate\Support\Facades\Redis;
use Telegram\Bot\Keyboard\Keyboard;
use Illuminate\Support\Facades\Lang;


class AdminCommand extends Command
{

    protected string $name = 'admin';

    protected string $description = 'Admin command';

    public function handle()
    {

        $userId = $this->getUpdate()->getMessage()->getFrom()->getId();

        $firstName = $this->getUpdate()->getMessage()->getFrom()->getFirstName();

        $username = $this->getUpdate()->getMessage()->getFrom()->getUsername();

        // Проверяем, есть ли уже сохраненный язык для пользователя
        $language = Redis::get("user_language:$userId");

        $this->chooseWelcomeMessage();

    }

    private function chooseWelcomeMessage()
    {

        $buttons = [
            [['text' => 'Статистика', 'callback_data' => 'statistics'], ['text' => 'Управление ПК', 'callback_data' => 'pc_control']],
            [['text' => 'Пользователи', 'callback_data' => 'admin_users']],
            // Добавьте остальные кнопки из базы данных
        ];

        $replyMarkup = Keyboard::make([
            'inline_keyboard' => $buttons,
        ]);

        $this->replyWithMessage([
            'text' => 'Админ панель',
            'reply_markup' => $replyMarkup
        ]);
    }
}
