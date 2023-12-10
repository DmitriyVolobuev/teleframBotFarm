<?php

namespace App\Commands;

use App\Models\User;
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

        $user = User::where('telegram_id', $userId)
            ->where('active', 1)
            ->where('admin', 1)
            ->first();

        // Проверяем, есть ли уже сохраненный язык для пользователя
        $language = Redis::get("user_language:$userId");

        if ($user) $this->chooseWelcomeMessage();

    }

    private function chooseWelcomeMessage()
    {

        $buttons = [
            [['text' => 'Статистика', 'callback_data' => 'statistics'], ['text' => 'Управление ПК', 'callback_data' => 'pc_control']],
            [['text' => 'Пользователи', 'callback_data' => 'user_control']],
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
