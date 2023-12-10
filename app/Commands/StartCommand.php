<?php

namespace App\Commands;

use App\Models\User;
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

        $firstName = $this->getUpdate()->getMessage()->getFrom()->getFirstName();

        $username = $this->getUpdate()->getMessage()->getFrom()->getUsername();

        $user = User::query()->firstOrCreate(
            ['telegram_id' => $userId],
            ['username' => $username, 'first_name' => $firstName, 'balance' => 0.00],
        );
        info($user->active);

        // Проверяем, есть ли уже сохраненный язык для пользователя
        $language = Redis::get("user_language:$userId");

        if (isset($language) && $user->active === 1) {

            // Если язык уже выбран, отправляем сообщение на выбранном языке
            $select_language = Lang::get('translations.selected_language', [], $language);
            $info_message = Lang::get('translations.info_start', ['firstName' => $firstName], $language);
            $button_message = Lang::get('translations.change', ['firstName' => $firstName], $language);

            $buttons = Keyboard::make([
                'inline_keyboard' => [
                    [
                        ['text' => $button_message, 'callback_data' => 'change'],
                    ],
                ],
            ]);

            $this->sendWelcomeMessage($info_message, $select_language, $buttons);

        } elseif($user->active === 1) {

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

    private function sendWelcomeMessage($info_message, $select_language, $buttons)
    {

        $this->replyWithMessage([
            'text' => $info_message . "\n" . $select_language,
            'reply_markup' => $buttons
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
