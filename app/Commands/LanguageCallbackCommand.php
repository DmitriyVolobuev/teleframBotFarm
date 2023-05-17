<?php

namespace App\Commands;

use Telegram\Bot\Commands\Command;
use Illuminate\Support\Facades\Redis;

class LanguageCallbackCommand extends Command
{

    protected string $name = 'languageCallback';

    public function handle()
    {
        $language = $this->getUpdate()->getCallbackQuery()->getData();
        $userId = $this->getUpdate()->getCallbackQuery()->getFrom()->getId();

        // Сохраняем выбранный язык в состоянии бота
        Redis::set("user_language:$userId", $language);

        // Отправляем приветственное сообщение на выбранном языке
        $this->sendWelcomeMessage($language);
    }

    private function sendWelcomeMessage($language)
    {
        // Отправляем сообщение на выбранном языке
        switch ($language) {
            case 'ru':
                $message = 'Вы выбрали русский язык.';
                break;
            case 'en':
                $message = 'You have chosen English language.';
                break;
            default:
                $message = 'Выбран недопустимый язык.';
                break;
        }

        $this->replyWithMessage([
            'text' => $message,
        ]);
    }
}
