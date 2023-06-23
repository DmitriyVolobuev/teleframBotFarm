<?php

namespace App\Commands;

use App\Models\User;
use Telegram\Bot\Commands\Command;
use Illuminate\Support\Facades\Redis;
use Telegram\Bot\Keyboard\Keyboard;
use Illuminate\Support\Facades\Lang;


class HelpCommand extends Command
{

    protected string $name = 'help';

    protected string $description = 'Help command';

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
                $this->sendHelpMessage($language);
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

    private function sendHelpMessage($language)
    {

        $info_help = Lang::get('translations.info_help', [], $language);
        $info_tg = Lang::get('translations.info_tg', [], $language);

        $this->replyWithMessage([
            'text' => $info_help . "\n" . $info_tg,
            'parse_mode' => 'HTML',
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
