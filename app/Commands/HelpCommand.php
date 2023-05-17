<?php

namespace App\Commands;

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

        // Проверяем, есть ли уже сохраненный язык для пользователя
        $language = Redis::get("user_language:$userId");

        if (isset($language)) {
            // Если язык уже выбран, отправляем сообщение на выбранном языке
            $this->sendHelpMessage($language);
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
}