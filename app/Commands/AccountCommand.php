<?php

namespace App\Commands;

use Telegram\Bot\Commands\Command;
use Illuminate\Support\Facades\Redis;
use Telegram\Bot\Keyboard\Keyboard;
use Illuminate\Support\Facades\Lang;
use App\Models\User;


class AccountCommand extends Command
{

    protected string $name = 'account';

    protected string $description = 'Account command';

    public function handle()
    {

        $telegramId = $this->getUpdate()->getMessage()->getFrom()->getId();

//        $firstName = $this->getUpdate()->getMessage()->getFrom()->getFirstName();

        $user = User::query()->where('telegram_id', $telegramId)->first();

        if ($user) {
            $name = $user->first_name;

            $balance = $user->balance;
        }
//        info($balance);

        // Проверяем, есть ли уже сохраненный язык для пользователя
        $language = Redis::get("user_language:$telegramId");

        if (isset($language)) {
            // Если язык уже выбран, отправляем сообщение на выбранном языке
            $this->sendAccountMessage($language, $name, $balance);
        }
    }

    private function sendAccountMessage($language, $name, $balance)
    {

        $name = Lang::get('translations.personal_information', ['name' => $name], $language);
        $balance = Lang::get('translations.balance', ['balance' => $balance], $language);
        $rent_pc = Lang::get('translations.rent_pc', [], $language);

        $button_pay = Lang::get('translations.pay_balance', [], $language);

        $this->replyWithMessage([
            'text' => $name . "\n" . $balance . "\n" . $rent_pc,
            'reply_markup' => Keyboard::make([
                'inline_keyboard' => [
                    [
                        ['text' => $button_pay, 'callback_data' => 'pay'],
                    ],
                ],
            ]),
        ]);
    }
}
