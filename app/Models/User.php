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
        'email_verified_at', 
        'division', 
        'supervisor_id', 
        'CFlag',
        'SFlag', 
        'StFlag', 
        'profile_picture'
    ];

    protected $hidden = [
        'hashed_password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'CFlag' => 'boolean',
        'SFlag' => 'boolean',
        'StFlag' => 'boolean',
    ];

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(User::class, 'supervisor_id');
    }

    public function feedbacks(): HasMany
    {
        return $this->hasMany(MonthlyFeedback::class);
    }
}