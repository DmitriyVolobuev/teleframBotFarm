<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatusEnum;
use App\Http\Service\PaymentService;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use YooKassa\Client;
use YooKassa\Model\Notification\NotificationSucceeded;
use YooKassa\Model\Notification\NotificationWaitingForCapture;
use YooKassa\Model\NotificationEventType;

class PaymentController extends Controller
{

    public function callback(Request $request, PaymentService $service)
    {
        $source = file_get_contents('php://input');

        $requestBody = json_decode($source, true);

        $notification = (isset($requestBody['event']) && $requestBody['event'] === NotificationEventType::PAYMENT_SUCCEEDED)
            ? new NotificationSucceeded($requestBody)
            : new NotificationWaitingForCapture($requestBody);

        $payment = $notification->getObject();

        if (isset($payment->status) && $payment->status === 'waiting_for_capture') {

            $service->getClient()->capturePayment([
               'amount' => $payment->amount,
            ], $payment->id, uniqid('', true));

        }

        if (isset($payment->status) && $payment->status === 'succeeded')
        {
            if ((bool)$payment->paid === true) {

                $metadata = (object)$payment->metadata;

                if (isset($metadata->transaction_id)) {

                    $transactionId = (int)$metadata->transaction_id;

                    $transaction = Transaction::find($transactionId);

                    $transaction->status = PaymentStatusEnum::CONFIRMED;

                    $transaction->save();

//                    $payment->amount->value;

                    $user_id = (int)$metadata->user_id;

                    $user = User::where('telegram_id', $user_id)->first();

                    $user->balance = $payment->amount->value;

                    $user->save();
//                    info($payment->amount);
                }
            }
        }
    }
}
