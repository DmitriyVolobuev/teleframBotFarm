<?php

namespace App\Commands;

use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Lang;


class RendcomputerCommand extends Command
{

    protected string $name = 'rendcomputer';

    protected string $description = 'Rendcomputer command';

    public function handle()
    {

        $userId = $this->getUpdate()->getMessage()->getFrom()->getId();

        // Проверяем, есть ли уже сохраненный язык для пользователя
        $language = Redis::get("user_language:$userId");

        if (isset($language)) {
            // Если язык уже выбран, отправляем сообщение на выбранном языке
            $this->sendRentMessage($language);
        }

    }

    private function sendRentMessage($language)
    {

        $message = Lang::get('translations.info_rent', [], $language);
        $notification = Lang::get('translations.notification', [], $language);

        $buttons = [
            [['text' => '🖥️ Rtx 3090', 'callback_data' => 'button1']],
            [['text' => '🖥️ Rtx 4090', 'callback_data' => 'button2']],
            [['text' => $notification, 'callback_data' => 'button2']],
            // Добавьте остальные кнопки из базы данных
        ];

        $replyMarkup = Keyboard::make([
            'inline_keyboard' => $buttons,
        ]);

        $this->replyWithMessage([
            'text' => $message,
            'reply_markup' => $replyMarkup,
        ]);
    }
}
