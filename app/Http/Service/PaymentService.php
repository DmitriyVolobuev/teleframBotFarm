<?php

namespace App\Http\Service;

use YooKassa\Client;
use YooKassa\Common\Exceptions\ApiException;
use YooKassa\Common\Exceptions\BadApiRequestException;
use YooKassa\Common\Exceptions\ExtensionNotFoundException;
use YooKassa\Common\Exceptions\ForbiddenException;
use YooKassa\Common\Exceptions\InternalServerError;
use YooKassa\Common\Exceptions\NotFoundException;
use YooKassa\Common\Exceptions\ResponseProcessingException;
use YooKassa\Common\Exceptions\TooManyRequestsException;
use YooKassa\Common\Exceptions\UnauthorizedException;
use YooKassa\Model\NotificationEventType;
use YooKassa\Model\Receipt;
use YooKassa\Model\Receipt\PaymentMode;
use YooKassa\Model\ReceiptItem;


class PaymentService
{

    public function getClient(): Client
    {
        $client = new Client();
        $client->setAuth(config('services.yookassa.shop_id'), config('services.yookassa.secret_key'));

        return $client;
    }

    /**
     * @param float $amount
     * @param string $discription
     * @param array $option
     * @return string
     * @throws ApiException
     * @throws BadApiRequestException
     * @throws ExtensionNotFoundException
     * @throws ForbiddenException
     * @throws InternalServerError
     * @throws NotFoundException
     * @throws ResponseProcessingException
     * @throws TooManyRequestsException
     * @throws UnauthorizedException
     */
    public function createPayment(float $amount, string $discription, array $option = [])
    {
        $client = $this->getClient();

//        // Формируем чек
//        $receipt = new Receipt();
//        $receipt->setType(Receipt::TYPE_PAYMENT);
//        $receipt->setCustomerEmail($option['customer_email']);
//
//        // Добавляем операцию пополнения баланса в чек
//        $receiptItem = new ReceiptItem();
//        $receiptItem->setPrice($amount);
//        $receiptItem->setQuantity(1);
//        $receiptItem->setTax(ReceiptItemType::TAX_NO_VAT);
//        $receiptItem->setDescription('test');
//        $receiptItem->setPaymentMode(PaymentMode::FULL_PAYMENT);
//        $receipt->addItem($receiptItem);

        $payment = $client->createPayment(
            [
                'amount' => [
                    'value' => $amount,
                    'currency' => 'RUB',
                ],
                'confirmation' => [
                    'type' => 'redirect',
                    'return_url' => route('payment.callback'), // URL для обработки результата платежа
                ],
                'capture' => false,
                'description' => 'Пополнение баланса',
                'metadata' => [
                    'transaction_id' => $option['transaction_id'],
                    'user_id' => $option['user_id'],
                ],
            ],
            uniqid('', true) // Уникальный идентификатор платежа
        );



        return $paymentUrl = $payment->getConfirmation()->getConfirmationUrl();
    }

}
