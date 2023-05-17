<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Redis;
use Telegram\Bot\BotsManager;

class WebhookController extends Controller
{

    public function __construct(BotsManager $botsManager)
    {
        $this->botsmanager = $botsManager;
    }


    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $webhook = $this->botsmanager->bot()
            ->commandsHandler(true);

        $update = $this->botsmanager->bot()->getWebhookUpdate();

//        $language = $webhook->getCallbackQuery()->getData();
//        $userId = $webhook->getCallbackQuery()->getFrom()->getId();

        if ($update->isType('message')) {
            $message = $update->getMessage()->getText();
            info($message);
            // Обрабатываем полученное сообщение
            // $message содержит информацию о входящем сообщении

            // Проверяем, является ли сообщение ответом на предыдущее сообщение
//            if ($message->isReply()) {
//                // Обрабатываем входящий ответ
//            }
        }
        if ($update->isType('callback_query')) {

            $callbackQuery = $update->getCallbackQuery();

            // Получение ID пользователя
            $userId = $callbackQuery->getFrom()->getId();

            // Получение username пользователя
            $username = $callbackQuery->getFrom()->getUsername();

            // Получение информации о чате
            $chat = $callbackQuery->getMessage()->getChat();

            // Получение ID чата
            $chatId = $chat->getId();

            // Получение данных callback кнопки
            $data = $callbackQuery->getData();
            $this->saveLanguage($userId, $data);
            info($data);
            info($userId);
            info($username);
            // Обрабатываем callback кнопку
            // $callbackQuery содержит информацию о нажатой кнопке
        }

        return response(null, Response::HTTP_OK);
    }

    private function saveLanguage($userId, $language)
    {
        // Сохраняем выбранный язык в состоянии бота
        Redis::set("user_language:$userId", $language);

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

        $bot = $this->botsmanager->bot();

        $bot->sendMessage([
            'chat_id' => $userId,
            'text' => $message,
        ]);
    }
}
