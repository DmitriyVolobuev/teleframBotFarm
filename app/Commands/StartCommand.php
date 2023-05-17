<?php

namespace App\Commands;

use Telegram\Bot\Commands\Command;
use Illuminate\Support\Facades\Redis;
use Telegram\Bot\Keyboard\Keyboard;
use Illuminate\Support\Facades\Lang;


class StartCommand extends Command
{

    protected string $name = 'start';

    protected string $description = 'Start command';

    public function handle()
    {

        $userId = $this->getUpdate()->getMessage()->getFrom()->getId();

        // Проверяем, есть ли уже сохраненный язык для пользователя
        $language = Redis::get("user_language:$userId");

        if (isset($language)) {
            // Если язык уже выбран, отправляем сообщение на выбранном языке
            $this->sendWelcomeMessage($language);
        } else {
            // Если язык не выбран, отправляем сообщение с инлайн-клавиатурой для выбора языка
            $this->replyWithMessage([
                'text' => 'Выберите язык / Select language:',
                'reply_markup' => Keyboard::make([
                    'inline_keyboard' => [
                        [
                            ['text' => 'Русский', 'callback_data' => 'ru'],
                            ['text' => 'English', 'callback_data' => 'en'],
                        ],
                    ],
                ]),
            ]);
        }
    }

    private function sendWelcomeMessage($language)
    {

//        $buttons = [
//            [['text' => 'Русский', 'callback_data' => 'ru']],
//            [['text' => 'English', 'callback_data' => 'en']],
//            // Добавьте остальные кнопки из базы данных
//        ];
//
//        $replyMarkup = Keyboard::make([
//            'inline_keyboard' => $buttons,
//        ]);

        $message = Lang::get('translations.selected_language', [], $language);
        $info_message = Lang::get('translations.info_start', [], $language);
//        info($message);

        $this->replyWithMessage([
            'text' => $info_message . "\n" . $message,
            'reply_markup' => Keyboard::make([
                'inline_keyboard' => [
                    [
                        ['text' => 'Русский', 'callback_data' => 'ru'],
                        ['text' => 'English', 'callback_data' => 'en'],
                    ],
                ],
            ]),
        ]);
    }
}
