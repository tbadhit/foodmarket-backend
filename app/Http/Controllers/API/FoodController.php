<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Food;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;

class FoodController extends Controller
{
    public function all(Request $request) {

        // Membuat variable yang dibutuhkan :

        $id = $request->input('id');
        // 6 = by default data makanan yang ada bakal 6
        $limit = $request->input('limit', 6);
        $name = $request->input('name');
        $types = $request->input('types');

        // Membuat harga dari angka terkecil ke angka terbesar
        $price_from = $request->input('price_from');
        $price_to = $request->input('price_to');

        // membuat variable rate 
        $rate_from = $request->input('rate_from');
        $rate_to = $request->input('rate_to');

        // Pengambilan data berdasarkan id :
        if($id) {
            $food = Food::find($id);

            if($food) {
                return ResponseFormatter::success(
                    $food,
                    'Data produk berhasil di ambil'
                );
            } else {
                return ResponseFormatter::error(
                    null,
                    'Data produk tidak ada',
                    404
                );
            }
        }
        // -------------Akhir-------------


        // Kenapa di bikin if?  arna pertama ada querynya
        $food = Food::query();

        if ($name) {
            $food->where('name', 'like', '%' . $name . '%');
        }

        if ($types) {
            $food->where('types', 'like', '%' . $types . '%');
        }

        if ($price_from) {
            $food->where('price','>=', $price_from);
        }

        if ($price_to) {
            $food->where('price','<=', $price_to);
        }

        if ($rate_from) {
            $food->where('price','>=', $rate_from);
        }

        if ($rate_to) {
            $food->where('price','<=', $rate_to);
        }

        return ResponseFormatter::success(
            $food->paginate($limit),
            'Data list produk berhasil di tambah'
        );
    }
}
