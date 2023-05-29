<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatusEnum;
use App\Models\Transaction;
use App\Models\User;
use App\Service\PaymentService;
use Illuminate\Http\Request;
use YooKassa\Client;
use YooKassa\Model\Notification\NotificationSucceeded;
use YooKassa\Model\Notification\NotificationWaitingForCapture;
use YooKassa\Model\NotificationEventType;

class PaymentController extends Controller
{
    public function handleCallback(Request $request)
    {
        // Проверка на успешность платежа
        if ($request->input('object.event') === 'payment.succeeded') {
            // Получение данных платежа
            $paymentId = $request->input('object.id');
            $amount = $request->input('object.amount.value');
            $userId = $request->input('object.metadata.user_id');

            // Обновление баланса пользователя
            $user = User::where('telegram_id', $userId)->firstOrFail();
            $user->balance += $amount;
            $user->save();

            // Запись транзакции
            $transaction = new Transaction();
            $transaction->user_id = $userId;
            $transaction->amount = $amount;
            $transaction->save();

            // Логирование успешного платежа
            info('Платеж успешно обработан. Payment ID: ' . $paymentId);
        } else {
            // Логирование ошибки платежа
            info('Ошибка обработки платежа. Event: ' . $request->input('object.event'));
        }

        // Возвращаем ответ YooKassa
        return response('OK', 200);
    }

//    public function create(Request $request, PaymentService $service)
//    {
//        $amount = (float)$request->input('amount');
//        $discription = 'Пополнение баланса';
//
//        $transaction = Transaction::create([
//            'amount' => $amount,
//            'discription' => $discription,
//        ]);
//
//        if ($transaction) {
//
//            $link = $service->createPayment($amount, $discription, [
//                'transaction_id' => $transaction->id,
//                'user_id' => 1,
//            ]);
//
//            return redirect()->away($link);
//        }
//    }

//    public function callback(Request $request, PaymentService $service)
//    {
//        $source = file_get_contents('php//input');
//
//        $requestBody = json_decode($source, true);
//
//        $notification = ($requestBody['event'] === NotificationEventType::PAYMENT_SUCCEEDED)
//            ? new NotificationSucceeded($requestBody)
//            : new NotificationWaitingForCapture($requestBody);
//
//        $payment = $notification->getObject();
//
//        if (isset($payment->status) && $payment->status === 'succeeded')
//        {
//            if ((bool)$payment->paid === true) {
//                $metadata = (object)$payment->metadata;
//
//                if (isset($metadata->transaction_id)) {
//
//                    $transactionId = (int)$metadata->transaction_id;
//
//                    $transaction = Transaction::find($transactionId);
//
//                    $transaction->status = PaymentStatusEnum::CONFIRMED;
//
//                    $transaction->save();
//
//                    $payment->amount->value;
//                }
//
//            }
//        }
//    }
}
