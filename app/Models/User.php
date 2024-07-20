<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function getRoleNamesAttribute()
    {
        return $this->roles->pluck('name');
    }

    // Company

    public function companies()
    {
        return $this->belongsToMany(Company::class, 'user_companies', 'user_id', 'company_id');
    }

    public function viewCompanies()
    {
        return $this->hasMany(view_company::class);
    }

    public function getCompanyNamesAttribute()
    {
        return $this->companies->pluck('name');
    }

    public function getViewCompanyNamesAttribute()
    {
        return $this->viewCompanies->pluck('name');
    }

    // Customer

    public function customer()
    {
        return $this->belongsToMany(Company::class, 'user_companies', 'user_id', 'customer_id');
    }

    public function viewCustomers()
    {
        return $this->hasMany(viewCustomer::class);
    }

    public function getCustomerNamesAttribute()
    {
        return $this->customers->pluck('name');
    }

    public function getViewCustomerNamesAttribute()
    {
        return $this->viewCustomers->pluck('name');
    }
}
