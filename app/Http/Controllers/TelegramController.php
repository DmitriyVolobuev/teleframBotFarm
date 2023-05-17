<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Api;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Methods\Query;

class TelegramController extends Controller
{
    public function handleWebhook(Request $request)
    {
//        $telegram = new Api(env('TELEGRAM_BOT_TOKEN'));

//        $updates = $telegram->getUpdates();

        // Проверка наличия обновлений
//        if (!empty($updates)) {
//            // Получение последнего обновления
//            $latestUpdate = end($updates);
//            $callbackQuery = $latestUpdate->getCallbackQuery();
//            $message = $latestUpdate->getMessage();
//            $text = $message->getText();
//
////            dd($latestUpdate->getCallbackQuery()->getData());
//            $button1 = Keyboard::inlineButton(['text' => 'Кнопка 1', 'callback_data' => 'button1']);
//            $button2 = Keyboard::inlineButton(['text' => 'Кнопка 2', 'callback_data' => 'button2']);
//            $button3 = Keyboard::inlineButton(['text' => 'Кнопка 3', 'callback_data' => 'button3']);
//
//            // Создаем ряды кнопок и добавляем кнопки в них
//            $row1 = [$button1, $button2];
//            $row2 = [$button3];
//
//            // Создаем инлайн-меню и добавляем ряды кнопок
//            $inlineKeyboard = Keyboard::make()->inline()->row($row1)->row($row2);
//
//            switch ($text)
//            {
//                case '/start':
//                    // Отправляем сообщение с инлайн-меню
//                    $response = $telegram->sendMessage([
//                        'chat_id' => $message->getChat()->getId(),
//                        'text' => 'Обо мне все главное!',
//    //                    'reply_markup' => $inlineKeyboard,
//                    ]);
//                    break;
//                case '/rendcomputer':
//                    // Отправляем сообщение с инлайн-меню
//                    $response = $telegram->sendMessage([
//                        'chat_id' => $message->getChat()->getId(),
//                        'text' => 'Аренда компьютера!',
//                        //'reply_markup' => $inlineKeyboard,
//                    ]);
//                    break;
//                case '/account':
//                    // Отправляем сообщение с инлайн-меню
//                    $response = $telegram->sendMessage([
//                        'chat_id' => $message->getChat()->getId(),
//                        'text' => 'Личный кабинет',
//                        //'reply_markup' => $inlineKeyboard,
//                    ]);
//                    break;
//                case '/help':
//                    // Отправляем сообщение с инлайн-меню
//                    $messageText = 'Связь со мной' . "\n" . '<a href="https://t.me/achkovsky">Максим</a>';
//                    $response = $telegram->sendMessage([
//                        'chat_id' => $message->getChat()->getId(),
//                        'text' => $messageText,
//                        'parse_mode' => 'HTML',
//                    ]);
//                    break;
//                default:
//                    // Действия по умолчанию
//                    // ...
//                    break;
//            }
//
//            // Проверка на команду /start
////            if ($text === '/start') {
////                // Создаем кнопки для инлайн-меню
////
////                // Отправляем сообщение с инлайн-меню
////                $response = $telegram->sendMessage([
////                    'chat_id' => $message->getChat()->getId(),
////                    'text' => 'Обо мне все главное!',
//////                    'reply_markup' => $inlineKeyboard,
////                ]);
////            } else {
////                // Обработка команд пользователя
////                switch ($latestUpdate->getCallbackQuery()->getData()) {
////                    case 'button1':
////                        // Действия при нажатии на кнопку 1
////                        $messageText = 'Это кнопка 1. Она делает следующее...';
////
////                        // Обновляем текст сообщения и добавляем инлайн-меню
////                        $telegram->sendMessage([
////                            'chat_id' => $message->getChat()->getId(),
//////                            'message_id' => $message->getMessageId(),
////                            'text' => $messageText,
////                            'reply_markup' => $inlineKeyboard,
////                        ]);
////                        break;
////                    case 'button2':
////                        // Действия при нажатии на кнопку 2
////                        // ...
////                        break;
////                    case 'button3':
////                        // Действия при нажатии на кнопку 3
////                        // ...
////                        break;
////                    default:
////                        // Действия по умолчанию
////                        // ...
////                        break;
////                }
////
////                // Отправляем ответное сообщение для подтверждения нажатия
//////                dd($callbackQuery->getId());
//////                $telegram->answerCallbackQuery([
//////                    'callback_query_id' => $callbackQuery->getId(),
//////                    'text' => 'Вы нажали на кнопку: ' . $latestUpdate->getCallbackQuery()->getData(),
//////                ]);
////            }
//        }
//        Telegram::deleteWebhook();
        $response = Telegram::getWebhookInfo();

        dd($response);

        return response('OK', 200);
    }
}
