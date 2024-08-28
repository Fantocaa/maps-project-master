<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class md_biaya extends Model
{
    use HasFactory, SoftDeletes;

    // Tentukan nama tabel secara eksplisit jika berbeda dari nama model
    protected $table = 'md_biayas';

    // Relasi ke tabel md_maps
    public function map()
    {
        return $this->belongsTo(md_maps::class, 'id');
    }
}
