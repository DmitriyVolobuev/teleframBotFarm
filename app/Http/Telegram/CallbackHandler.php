<?php

namespace App\Http\Telegram;

use App\Http\Service\PaymentService;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redis;
use Telegram\Bot\Actions;
use Telegram\Bot\BotsManager;
use Telegram\Bot\Keyboard\Keyboard;
use YooKassa\Client;


class CallbackHandler
{

    public function __construct(BotsManager $botsmanager)
    {
        $this->botsmanager = $botsmanager;
//        $this->arCallback = $arCallback;
    }

    public function handle($callbackQuery)
    {

        $bot = $this->botsmanager->bot();

        $data = $callbackQuery->getData();

        $callbackId = $callbackQuery->getId();

        // Получение ID пользователя
        $telegramId = $callbackQuery->getFrom()->getId();

        $user = User::where('telegram_id', $telegramId)
            ->where('active', 1)
            ->first();
//        info($user);

        // Получение username пользователя
        $username = $callbackQuery->getFrom()->getUsername();

        $firstName = $callbackQuery->getFrom()->getFirstName();

        // Получение информации о чате
        $chat = $callbackQuery->getMessage()->getChat();

        $messageId = $callbackQuery->getMessage()->getMessageId();

        // Получение ID чата
        $chatId = $chat->getId();

        $language = Redis::get("user_language:$telegramId");

//        if ($data == 'ru' || $data == 'en') {
//            $this->saveLanguage($telegramId, $data);
//        }

            $pc = 'pc1';
            switch ($data) {
                // start
                case 'en':
                case 'ru':
                    $this->saveLanguage($bot, $telegramId, $messageId, $firstName, $data);
                    break;
                case 'change':
                    $this->changeLanguage($bot, $telegramId, $messageId, $language);
                    break;
                // start

                // Account
                case 'pay':
//                $this->arCallback->handle($callbackQuery);
                    $this->payCallback($bot, $telegramId, $language);
                    break;
                case 'yukassa':
                    $this->yukassaCallback($bot, $telegramId, $messageId, $language);
                    break;
                case 'crypt':
                    $this->handleCryptCallback($bot, $telegramId, $messageId, $language);
                    break;
                case 'back_pay_account':
                    $this->backPayAccountCallback($bot, $telegramId, $messageId, $language);
                    break;
                case '100':
                    $this->oneHundredCallback($bot, $telegramId, $language,);
                    break;
                // Account

                // Rent pc
                case $pc:
                    $this->infoPcCallback($bot, $telegramId, $messageId, $language);
                    break;
                case 'back_pay_rent':
                    $this->backRentCallback($bot, $telegramId, $messageId, $language);
                    break;

                // Admin
                case 'pc_control':
                    $this->controlPcCallback($bot, $telegramId, $language);
                    break;
                case 'user_control':
                    $this->controllUserCallback($bot, $telegramId, $messageId, $language);
                    break;
                case 'admin_back':
                    $this->backAdminCallback($bot, $telegramId, $messageId, $language);
                    break;
                case 'admin_back_users':
                    $this->backAdminUserCallback($bot, $telegramId, $messageId, $language);
                    break;
            }
            if (strpos($data, 'admin_pc') === 0) {
                $adminPcNumber = substr($data, strlen('admin_pc'));
//            info($adminPcNumber);
                $this->infoAdminControlCallback($bot, $telegramId, $messageId, $adminPcNumber);
            }

            if (strpos($data, 'admin_user') === 0) {
                $adminUserId = substr($data, strlen('admin_user'));
//                info($adminPcNumber);
                $this->adminUserControlCallback($bot, $telegramId, $messageId, $adminUserId, $firstName);
            }

            if (strpos($data, 'admin_accrue') === 0) {
                $adminUserId = substr($data, strlen('admin_accrue'));
//                info($adminPcNumber);
                $this->adminUserAccrueCallback($bot, $telegramId, $messageId, $adminUserId, $firstName, $callbackId);
            }

            if (strpos($data, 'admin_banned_user') === 0) {
                $adminUserId = substr($data, strlen('admin_banned_user'));
//                info($adminPcNumber);
                $this->adminUserBannedCallback($bot, $telegramId, $messageId, $adminUserId);
            }

            if (strpos($data, 'admin_unban_user') === 0) {
                $adminUserId = substr($data, strlen('admin_unban_user'));
//                info($adminPcNumber);
                $this->adminUserUnbanCallback($bot, $telegramId, $messageId, $adminUserId);
            }

//        info($data);

    }

    private function changeLanguage($bot, $telegramId, $messageId, $language)
    {
        $buttons = Keyboard::make([
            'inline_keyboard' => [
                [
                    ['text' => 'Русский', 'callback_data' => 'ru'],
                    ['text' => 'English', 'callback_data' => 'en'],
                ],
            ],
        ]);

        $choose_language = Lang::get('translations.choose_language', [], $language);

        $bot->editMessageText([
            'chat_id' => $telegramId,
            'message_id' => $messageId, // ID сообщения, которое нужно изменить
            'text' => $choose_language,
            'reply_markup' => $buttons
        ]);
    }

    private function saveLanguage($bot, $telegramId, $messageId, $firstName,$language)
    {
        // Сохраняем выбранный язык в состоянии бота
        Redis::set("user_language:$telegramId", $language);

        // Отправляем сообщение на выбранном языке
        switch ($language) {
            case 'ru':
                $message = 'Вы выбрали русский язык.';
                break;
            case 'en':
                $message = 'You have chosen English language.';
                break;
//            default:
//                $message = 'Выбран недопустимый язык.';
//                break;
        }

        $info_start = Lang::get('translations.info_start', ['firstName' => $firstName], $language);
        $button_message = Lang::get('translations.change', [], $language);

        $buttons = Keyboard::make([
            'inline_keyboard' => [
                [
                    ['text' => $button_message, 'callback_data' => 'change'],
                ],
            ],
        ]);

        $bot->editMessageText([
            'chat_id' => $telegramId,
            'message_id' => $messageId, // ID сообщения, которое нужно изменить
            'text' => $info_start . "\n" . $message,
            'reply_markup' => $buttons
        ]);

//        $bot->sendMessage([
//            'chat_id' => $telegramId,
//            'text' => $message,
//        ]);
    }

    private function payCallback($bot, $telegramId, $language)
    {

//        info($language);
        $info_pay = Lang::get('translations.info_pay', [], $language);

        $button_yukassa = Lang::get('translations.pay_yukassa', [], $language);
        $button_crypt = Lang::get('translations.pay_crypt', [], $language);

        $buttons = Keyboard::make([
            'inline_keyboard' => [
                [
                    ['text' => $button_yukassa, 'callback_data' => 'yukassa'],
                    ['text' => $button_crypt, 'callback_data' => 'crypt'],
                ],
            ],
        ]);

        $bot->sendMessage([
            'chat_id' => $telegramId,
            'text' => $info_pay,
            'reply_markup' => $buttons,
        ]);
    }

    private function yukassaCallback($bot, $telegramId, $messageId, $language)
    {

        $select_summ = Lang::get('translations.select_summ', [], $language);
        $button_rub = Lang::get('translations.rub', [], $language);
        $enter_amount = Lang::get('translations.enter_amount', [], $language);
        $button_back = Lang::get('translations.back', [], $language);

        $buttons = Keyboard::make([
            'inline_keyboard' => [
                [
                    ['text' => '+100 ' . $button_rub, 'callback_data' => '100'],
                    ['text' => '+500 ' . $button_rub, 'callback_data' => '500'],
                ],
                [
                    ['text' => '+1000 ' . $button_rub, 'callback_data' => '1000'],
                    ['text' => $enter_amount, 'callback_data' => 'enter_amount'],
                ],
                [
                    ['text' => $button_back, 'callback_data' => 'back_pay_account'],
                ],
            ],
        ]);

        $bot->editMessageText([
            'chat_id' => $telegramId,
            'message_id' => $messageId, // ID сообщения, которое нужно изменить
            'text' => $select_summ,
            'reply_markup' => $buttons
        ]);
    }

    private function handleCryptCallback($bot, $telegramId, $messageId, $language)
    {

        $select_summ = Lang::get('translations.select_summ', [], $language);
        $button_rub = Lang::get('translations.rub', [], $language);
        $enter_amount = Lang::get('translations.enter_amount', [], $language);
        $button_back = Lang::get('translations.back', [], $language);

        $buttons = Keyboard::make([
            'inline_keyboard' => [
                [
                    ['text' => $button_back, 'callback_data' => 'back_pay_account'],
                ],
            ],
        ]);

        $bot->editMessageText([
            'chat_id' => $telegramId,
            'message_id' => $messageId,
            'text' => 'Введите номер транзакции',
            'reply_markup' => $buttons
        ]);
    }

    private function backPayAccountCallback($bot, $telegramId, $messageId, $language)
    {
        $info_pay = Lang::get('translations.info_pay', [], $language);

        $button_yukassa = Lang::get('translations.pay_yukassa', [], $language);
        $button_crypt = Lang::get('translations.pay_crypt', [], $language);

        $buttons = Keyboard::make([
            'inline_keyboard' => [
                [
                    ['text' => $button_yukassa, 'callback_data' => 'yukassa'],
                    ['text' => $button_crypt, 'callback_data' => 'crypt'],
                ],
            ],
        ]);

        $bot->editMessageText([
            'chat_id' => $telegramId,
            'message_id' => $messageId,
            'text' => $info_pay,
            'reply_markup' => $buttons,
        ]);
    }

    private function oneHundredCallback($bot, $telegramId, $language)
    {
        $amount = 100;

        $service = new PaymentService;

        $discription = 'Пополнение баланса';

        $transaction = Transaction::create([
           'amount' =>  $amount,
           'description' =>  $discription,
           'user_id' => $telegramId,
        ]);

        if ($transaction) {

            $paymentLink = $service->createPayment($amount, $discription, [
                'user_id' => $telegramId,
                'transaction_id' => $transaction->id,
            ]);

        }

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => 'Подтвердите пополнение', 'url' => $paymentLink]
                ]
            ]
        ];

        $bot->sendMessage([
            'chat_id' => $telegramId,
            'text' => '100 руб',
            'reply_markup' => json_encode($keyboard)
        ]);
    }


    // --RentComputer--

    private function infoPcCallback($bot, $telegramId, $messageId, $language)
    {
        $info_pc = Lang::get('translations.info_pc', [], $language);
        $notification = Lang::get('translations.notification', [], $language);

        $button_back = Lang::get('translations.back', [], $language);
        $button_rent = Lang::get('translations.rent', [], $language);
        $button_book = Lang::get('translations.book', [], $language);

        $buttons = Keyboard::make([
            'inline_keyboard' => [
                [
                    ['text' => $button_rent, 'callback_data' => 'rent'],
                    ['text' => $button_book, 'callback_data' => 'booking'],
                    ['text' => $notification, 'callback_data' => 'notification'],
                ],
                [
                    ['text' => $button_back, 'callback_data' => 'back_pay_rent'],
                ],
            ],
        ]);

        $bot->editMessageText([
            'chat_id' => $telegramId,
            'message_id' => $messageId,
            'text' => $info_pc,
            'reply_markup' => $buttons,
        ]);
    }

    private function backRentCallback($bot, $telegramId, $messageId, $language)
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

        $bot->editMessageText([
            'chat_id' => $telegramId,
            'message_id' => $messageId,
            'text' => $message,
            'reply_markup' => $replyMarkup,
        ]);
    }

    // --RentComputer--

    // --Admin Panel--

    private function controlPcCallback($bot, $telegramId, $language)
    {

        $buttons = [
            [['text' => '🖥️ Rtx 3090', 'callback_data' => 'admin_pc1']],
            [['text' => '🖥️ Rtx 4090', 'callback_data' => 'admin_pc2']],
            // Добавьте остальные кнопки из базы данных
        ];

        $replyMarkup = Keyboard::make([
            'inline_keyboard' => $buttons,
        ]);

        $bot->sendMessage([
            'chat_id' => $telegramId,
            'text' => 'Управление ПК',
            'reply_markup' => $replyMarkup,
        ]);
    }

    private function controllUserCallback($bot, $telegramId, $messageId, $language)
    {
        $users = User::all();

        $buttons = [];

        foreach ($users as $user) {

            $buttons[] = [['text' => $user->first_name, 'callback_data' => 'admin_user'.$user->telegram_id]];

        }

        $buttons[] = [['text' => 'Назад', 'callback_data' => 'admin_back']];

        $replyMarkup = Keyboard::make([
            'inline_keyboard' => $buttons,
        ]);

        $bot->editMessageText([
            'chat_id' => $telegramId,
            'message_id' => $messageId,
            'text' => "Пользователи",
            'reply_markup' => $replyMarkup,
        ]);
    }

    private function infoAdminControlCallback($bot, $telegramId, $messageId, $adminPcNumber)
    {

        $buttons = [
            [['text' => 'Отключить', 'callback_data' => 'disable_pc'], ['text' => 'Скидка', 'callback_data' => 'discount_pc']],
            [['text' => 'Назад', 'callback_data' => 'admin_back']],
            // Добавьте остальные кнопки из базы данных
        ];

        $replyMarkup = Keyboard::make([
            'inline_keyboard' => $buttons,
        ]);

        $bot->editMessageText([
            'chat_id' => $telegramId,
            'message_id' => $messageId,
            'text' => "Инфо о пк $adminPcNumber",
            'reply_markup' => $replyMarkup,
        ]);
    }

    private function adminUserControlCallback($bot, $telegramId, $messageId, $adminUserId, $firstName)
    {

        $user = User::where('telegram_id', $adminUserId)->first();

        if ($user !== null) {
            $firstName = $user->first_name;
        } else $firstName = '';

        $buttons = [
            [['text' => 'Забанить', 'callback_data' => "admin_banned_user$adminUserId"], ['text' => 'Пополнить баланс', 'callback_data' => "admin_accrue$adminUserId"]],
            [['text' => 'Разбанить', 'callback_data' => "admin_unban_user$adminUserId"]],
            [['text' => 'Назад', 'callback_data' => 'admin_back_users']],
            // Добавьте остальные кнопки из базы данных
        ];

        $replyMarkup = Keyboard::make([
            'inline_keyboard' => $buttons,
        ]);

        $bot->editMessageText([
            'chat_id' => $telegramId,
            'message_id' => $messageId,
            'text' => "Действие над пользователем: $firstName id: $adminUserId",
            'reply_markup' => $replyMarkup,
        ]);
    }

    private function adminUserAccrueCallback($bot, $telegramId, $messageId, $adminUserId, $firstName, $callbackId)
    {
        // Отправляем ответное сообщение пользователю
        $bot->answerCallbackQuery([
            'callback_query_id' => $callbackId,
            'text' => "Введите сумму пополнения пользователю:",
            'show_alert' => false,
        ]);

        // Задаем действие бота "ожидание ввода" для текущего пользователя
        $bot->sendChatAction([
            'chat_id' => $telegramId,
            'action' => Actions::TYPING,
        ]);
    }

    private function adminUserBannedCallback($bot, $telegramId, $messageId, $adminUserId)
    {

        $user = User::where('telegram_id', $adminUserId)->first();

        $firstName = '';

        if ($user !== null) {

            $user->active = '0';
            $user->save();

            $firstName = $user->first_name;

            $bot->sendMessage([
                'chat_id' => $telegramId,
                'text' => "Пользователь $firstName забанен",
            ]);
        }
    }

    private function adminUserUnbanCallback($bot, $telegramId, $messageId, $adminUserId)
    {

        $user = User::where('telegram_id', $adminUserId)->first();

        $firstName = '';

        if ($user !== null) {

            $user->active = '1';
            $user->save();

            $firstName = $user->first_name;

            $bot->sendMessage([
                'chat_id' => $telegramId,
                'text' => "Пользователь $firstName разбанен",
            ]);
        }
    }

    private function backAdminCallback($bot, $telegramId, $messageId, $language)
    {

        $buttons = [
            [['text' => 'Статистика', 'callback_data' => 'statistics'], ['text' => 'Управление ПК', 'callback_data' => 'pc_control']],
            [['text' => 'Пользователи', 'callback_data' => 'user_control']],
            // Добавьте остальные кнопки из базы данных
        ];

        $replyMarkup = Keyboard::make([
            'inline_keyboard' => $buttons,
        ]);

        $bot->editMessageText([
            'chat_id' => $telegramId,
            'message_id' => $messageId,
            'text' => 'Админ панель',
            'reply_markup' => $replyMarkup,
        ]);
    }

    private function backAdminUserCallback($bot, $telegramId, $messageId, $language)
    {

        $users = User::all();

        $buttons = [];

        foreach ($users as $user) {

            $buttons[] = [['text' => $user->first_name, 'callback_data' => 'admin_user'.$user->telegram_id]];

        }

        $buttons[] = [['text' => 'Назад', 'callback_data' => 'admin_back']];

        $replyMarkup = Keyboard::make([
            'inline_keyboard' => $buttons,
        ]);

        $bot->editMessageText([
            'chat_id' => $telegramId,
            'message_id' => $messageId,
            'text' => "Пользователи",
            'reply_markup' => $replyMarkup,
        ]);
    }

}
