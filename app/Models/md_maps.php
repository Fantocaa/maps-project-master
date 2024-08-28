<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class md_maps extends Model
{
    use HasFactory, SoftDeletes;

    // Tentukan nama tabel secara eksplisit jika berbeda dari nama model
    protected $table = 'md_maps';

    public function biayas()
    {
        return $this->hasMany(md_biaya::class, 'id_maps');
    }

    // Definisi relasi ke Agent
    public function agent()
    {
        return $this->belongsTo(md_agent::class, 'id_agent');
    }

    // Definisi relasi ke Customer
    public function customer()
    {
        return $this->belongsTo(md_company::class, 'id_customer');
    }

    // Relasi ke perusahaan
    public function perusahaan()
    {
        return $this->belongsTo(md_company::class, 'id_perusahaan');
    }

    // Relasi ke Satuan
    public function satuan()
    {
        return $this->belongsTo(md_satuan::class, 'id_satuan');
    }

    public function jenisbarang()
    {
        return $this->belongsTo(md_jenis_barang::class, 'id_jenis_barang');
    }
}
