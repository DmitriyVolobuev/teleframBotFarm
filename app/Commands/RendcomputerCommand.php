<?php

namespace App\Commands;

use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;

class RendcomputerCommand extends Command
{

    protected string $name = 'rendcomputer';

    protected string $description = 'Rendcomputer command';

    public function handle()
    {

        $buttons = [
            [['text' => '🖥️ Rtx 3090', 'callback_data' => 'button1']],
            [['text' => '🖥️ Rtx 4090', 'callback_data' => 'button2']],
            // Добавьте остальные кнопки из базы данных
        ];

        $replyMarkup = Keyboard::make([
            'inline_keyboard' => $buttons,
        ]);

        $this->replyWithMessage([
           'text' => 'Рендер компьютеров',
           'reply_markup' => $replyMarkup,
        ]);

    }
}
