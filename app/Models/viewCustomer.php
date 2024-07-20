<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class viewCustomer extends Model
{
    use HasFactory;

    protected $table = 'view_customers';

    protected $fillable = ['customer_id'];

    public function company()
    {
        return $this->belongsTo(Company::class, 'customer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
