<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class md_agent extends Model
{
    use HasFactory, SoftDeletes;

    public function companies()
    {
        return $this->belongsToMany(md_company::class, 'pivot_company_agents', 'agent_id', 'company_id');
    }
}
