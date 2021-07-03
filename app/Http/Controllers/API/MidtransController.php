<?php

namespace App\Http\Controllers\API;

use Midtrans\Config;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Midtrans\Notification;

class MidtransController extends Controller
{
   public function callBack(Request $request)
    {
        // Set konfigurasi midtrans
        Config::$serverKey = config('services.midtrans.serverKey');
        Config::$isProduction = config('services.midtrans.isProduction');
        Config::$isSanitized = config('services.midtrans.isSanitized');
        Config::$is3ds = config('services.midtrans.is3ds');

        // Buat instace midtrans notification
        $notification = new Notification();

        // Assign ke variable untuk memudahkan ngoding
        $status = $notification->transaction_status;
        $type = $notification->payment_type;
        $fraud = $notification->fraud_status;
        $order_id = $notification->order_id;

        // Cari transaksi berdasarkan ID
        $transaction = Transaction::findOrFail($order_id);

        // Handle notifikasi status midtrans (Paling penting)
        // jika ada notifikasi midtrans yang masuk ini prosesnya akan menginformasikan ke kita
        if ($status == 'capture') {
            if($type == 'credit_card') {
                if($fraud == 'challenge') {
                    $transaction->status = 'PENDING';
                } else {
                    $transaction->status = 'SUCCESS';
                }
            }
        } else if ($status == 'settlement') {

            $transaction->status = 'SUCCESS';

        } else if ($status == 'pending') {

            $transaction->status = 'PENDING';

        } else if ($status == 'deny') {

            $transaction->status = 'CANCELLED';

        } else if ($status == 'expire') {

            $transaction->status = 'CANCELLED';

        } else if ($status == 'cancel') {
            
            $transaction->status = 'CANCELLED';

        }


        // Simpan Transaksi
        $transaction->save();
    }

    public function success() {
        return view('midtrans.success');
    }

    public function unfinish() {
        return view('midtrans.unfinish');
    }

    public function error() {
        return view('midtrans.error');
    }
}
