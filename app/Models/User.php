<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
    'first_name', 
    'last_name', 
    'email', 
    'instagram', 
    'linkedin', 
    'batch_no', 
    'hashed_password', 
    'email_verified_at'
    ];

    public function staff()
    {
        return $this->hasOne(Staff::class);
    }

    public function supervisor()
    {
        return $this->hasOne(Supervisor::class);
    }

    public function cLevel()
    {
        return $this->hasOne(CLevel::class);
    }

    public function monthlyFeedback()
    {
        return $this->hasMany(MonthlyFeedback::class);
    }
}
