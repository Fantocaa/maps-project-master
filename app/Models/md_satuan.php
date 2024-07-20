<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class md_satuan extends Model
{
    use HasFactory;
    use SoftDeletes;

    public function biaya()
    {
        return $this->hasMany(md_biaya::class, 'id_satuan');
    }
}
