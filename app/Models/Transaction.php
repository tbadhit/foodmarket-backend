<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    // Tambahin 1 (pertama) :
    protected $fillable = [
        'food_id', 'user_id', 'quantity', 'total', 'status', 'payment_url'
    ];

    // Membuat relasi foodnya :
    public function food() {
        return $this->hasOne(Food::class, 'id', 'food_id');
    }

    // Membuat relasi usernya :
    public function user() {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    // Menambahkan accessor untuk mengubah format 
    // tanggal yang tadinya di laravel itu timestamp di convert menjadi epoch atau uniq date
    public function getCreatedAtAttribute($value) {
        return Carbon::parse($value)->timestamp;
    }
    public function getUpdateAtAttribute($value) {
        return Carbon::parse($value)->timestamp;
    }
}
