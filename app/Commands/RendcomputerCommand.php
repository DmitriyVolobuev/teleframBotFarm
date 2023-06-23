<?php

namespace App\Commands;

use App\Models\User;
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

        $user = User::where('telegram_id', $userId)
            ->where('active', 1)
            ->first();

        // Проверяем, есть ли уже сохраненный язык для пользователя
        $language = Redis::get("user_language:$userId");

        if ($user) {
            if (isset($language)) {
                // Если язык уже выбран, отправляем сообщение на выбранном языке
                $this->sendRentMessage($language);
            } else {

                // Если язык не выбран, отправляем сообщение с инлайн-клавиатурой для выбора языка
//            $info_message = Lang::get('translations.info_start', ['firstName' => $firstName], $language);
                $choose_language = Lang::get('translations.choose_language', [], $language);

                $buttons = Keyboard::make([
                    'inline_keyboard' => [
                        [
                            ['text' => 'Русский', 'callback_data' => 'ru'],
                            ['text' => 'English', 'callback_data' => 'en'],
                        ],
                    ],
                ]);

                $this->chooseWelcomeMessage($choose_language, $buttons);
            }
        }

    }

    private function sendRentMessage($language)
    {

        $message = Lang::get('translations.info_rent', [], $language);
        $notification = Lang::get('translations.notification', [], $language);

        $buttons = [
            [['text' => '🖥️ Rtx 3090', 'callback_data' => 'pc1']],
            [['text' => '🖥️ Rtx 4090', 'callback_data' => 'pc2']],
            [['text' => $notification, 'callback_data' => 'notification']],
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

    private function chooseWelcomeMessage($select_language, $buttons)
    {

        $this->replyWithMessage([
            'text' => $select_language,
            'reply_markup' => $buttons
        ]);
    }
}
