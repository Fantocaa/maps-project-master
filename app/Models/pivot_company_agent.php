<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class pivot_company_agent extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pivot_company_agents';

    protected $fillable = ['company_id', 'agent_id'];
}
