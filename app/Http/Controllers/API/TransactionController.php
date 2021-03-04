<?php

namespace App\Http\Controllers\API;

use Exception;
use Midtrans\Snap;
use Midtrans\Config;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class TranscationController extends Controller
{
    public function all(Request $request) {

        // Membuat variable yang dibutuhkan :

        $id = $request->input('id');
        // 6 = by default data makanan yang ada bakal 6
        $limit = $request->input('limit', 6);
        $food_id = $request->input('food_id');
        $status = $request->input('status');

        // Pengambilan data berdasarkan id :
        if($id) {
            // menambahkan relasinya [food dan user]
            $transaction = Transaction::with(['food', 'user'])->find($id);

            if($transaction) {
                return ResponseFormatter::success(
                    $transaction,
                    'Data transaksi berhasil di ambil'
                );
            } else {
                return ResponseFormatter::error(
                    null,
                    'Data transaksi tidak ada',
                    404
                );
            }
        }
        // -------------Akhir-------------


        // Kenapa di bikin if?  arna pertama ada querynya
        // karna butuh relasinya jadi saya panggil relasinya :
        // where buat apa? jadi saya cuma mau ambil data yang sedang login, 
        // jadi hanya trasaksi yang dia punya aja bukan orang lain
        $transaction = Transaction::with(['food', 'user'])->where('user_id', Auth::user()->id);

        if ($food_id) {
            // cara bacanya where food_id = $food_id
            $transaction->where('food_id', $food_id);
        }

        if ($status) {
            // cara bacanya where status = $status
            $transaction->where('status', $status);
        }

        return ResponseFormatter::success(
            $transaction->paginate($limit),
            'Data list transaction berhasil di tambah'
        );
    }

    public function update(Request $request, $id) {
        $transaction = Transaction::findOrFail($id);

        $transaction->update($request->all());

        return ResponseFormatter::success($transaction, 'Transaksi berhasil di perbarui');
    }

    public function checkout(Request $request) {
        // Membuat validasi
        $request->validate([
            'food_id' => 'required|exists:food_id',
            'user_id' => 'required|exists:user_id',
            'quantity' => 'required',
            'total' => 'required',
            'status' => 'required'
        ]);

        // Proses pembuatan database yang di tampung di variable transaction
        $transaction = Transaction::create([
            'food_id' => $request->food_id,
            'user_id' => $request->user_id,
            'quantity' => $request->quantity,
            'total' => $request->total,
            'status' => $request->status,
            'payment_url' => ''
        ]);

        // Konfigurasi midtrans (biar bisa panggil si midtransnya)
        Config::$serverKey = config('services.midtrans.serverKey');
        Config::$isProduction = config('services.midtrans.isProduction');
        Config::$isSanitized = config('services.midtrans.isSanitized');
        Config::$is3ds = config('services.midtrans.is3ds');
        

        // Panggil transaksi yang tadi di buat (karna butuh relasinya jadi dipanggil ulang)
        $transaction = Transaction::with(['food', 'user'])->find($transaction->id);

        
        // Membuat transaksi midtrans
        // Membuat objek agar bisa di oper ke midtrans itu sendiri
        $midtrans = [
            'transaction_details' => [
                'order_id' => $transaction->id,
                'gross_amount' => $transaction->total,
            ],
            'customer_details' => [
                'first_name' => $transaction->user->name,
                'email' => $transaction->user->email
            ],
            'enable_payments' => ['gopay', 'bank_transfer'],
            'vtweb' => []
        ];

        // Memanggil midtrans
        try {
            // Ambil halaman payment midtrans
            $paymentUrl = Snap::createTransaction($midtrans)->redirect_url;

            $transaction->payment_url = $paymentUrl;
            $transaction->save();

            // Mengembalikan data ke API
            return ResponseFormatter::success($transaction, 'Transaksi berhasil');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 'Transaksi Gagal');
        }

        
    }
}
