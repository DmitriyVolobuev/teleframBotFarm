<?php

use App\Http\Controllers\TelegramController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;
use phpseclib3\Net\SSH2;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {

//    $ssh = new SSH2('192.168.0.103', 22);
//
//    if (!$ssh->login('dm', '0000')) {
//        throw new \Exception('Login failed');
//    } else {
//        $newPassword = '2222';
//
//        // Выполнение команды для изменения пароля
//        $command = "net user dm2 $newPassword";
//        $output = $ssh->exec($command);

        echo 'Password changed successfully';
        echo 123;

        echo 'OK';
//    }
});

Route::post('/webhook', WebhookController::class);

Route::match(['GET', 'POST'], '/payments/callback', [PaymentController::class, 'callback'])->name('payment.callback');

