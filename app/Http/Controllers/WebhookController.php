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

        // Проверка Хэша транзакции
        function isTransactionValid($transactionId)
        {
            $pattern = '/^[a-f0-9]{64}$/i';

            return preg_match($pattern, $transactionId);
        }

        if ($update->isType('message')) {
            $message = $update->getMessage();

            $user_id = $message->getFrom()->getId();
            $chat_id = $message->getChat()->getId();
            $transactionId = $message->getText();

            if (isTransactionValid($transactionId)) {
                // Проверяем номер транзакции на соответствие условиям
                if (isTransactionValid($transactionId)) {
                    // Номер транзакции прошел проверку
                    $this->botsmanager->bot()->sendMessage([
                        'chat_id' => $chat_id,
                        'text' => 'Транзакция успешно прошла.',
                    ]);
                } else {
                    // Номер транзакции не соответствует условиям
                    $this->botsmanager->bot()->sendMessage([
                        'chat_id' => $chat_id,
                        'text' => 'Неверный номер транзакции. Попробуйте еще раз.',
                    ]);
                }
            }
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
