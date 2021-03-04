<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Food extends Model
{
    use HasFactory, SoftDeletes;

    // Tambahin 1 (pertama) :
    protected $fillable = [
        'name', 'description', 'ingredients', 'price', 'rate', 'types',
        'picturePath'
    ];

    // Menambahkan accessor untuk mengubah format 
    // tanggal yang tadinya di laravel itu timestamp di convert menjadi epoch atau uniq date
    public function getCreatedAtAttribute($value) {
        return Carbon::parse($value)->timestamp;
    }
    public function getUpdateAtAttribute($value) {
        return Carbon::parse($value)->timestamp;
    }

    // ini buat ngakalin agar picturePath nya bisa camelCase :
    public function toArray()
    {
        $toArray = parent::toArray();
        $toArray['picturePath'] = $this->picturePath;
        return $toArray;
    }

    public function getPicturePathAttibute() {
        return url('') . Storage::url($this->attributes['picturePath']);
    }
}
