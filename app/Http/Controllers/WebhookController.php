<?php

namespace App\Http\Controllers;

use App\Http\Telegram\CallbackHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Telegram\Bot\BotsManager;

class WebhookController extends Controller
{

    public function __construct(BotsManager $botsManager, CallbackHandler $callbackHandler)
    {
        $this->botsmanager = $botsManager;
        $this->callbackHandler = $callbackHandler;
    }


    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $webhook = $this->botsmanager->bot()
            ->commandsHandler(true);

        $update = $this->botsmanager->bot()->getWebhookUpdate();

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
            $this->callbackHandler->handle($callbackQuery);

//            // Получение ID пользователя
//            $userId = $callbackQuery->getFrom()->getId();
//
//            // Получение username пользователя
//            $username = $callbackQuery->getFrom()->getUsername();
//
//            // Получение информации о чате
//            $chat = $callbackQuery->getMessage()->getChat();
//
//            // Получение ID чата
//            $chatId = $chat->getId();
//
//            // Получение данных callback кнопки
//            $data = $callbackQuery->getData();
//            $this->saveLanguage($userId, $data);
//            info($data);
//            info($userId);
//            info($username);
            // Обрабатываем callback кнопку
            // $callbackQuery содержит информацию о нажатой кнопке
        }

        return response(null, Response::HTTP_OK);
    }

}
