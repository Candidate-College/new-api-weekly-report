<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable, HasFactory;

    protected $fillable = [
        'first_name', 'last_name', 'email', 'instagram', 'linkedin', 'batch_no',
        'password', 'division_id', 'supervisor_id', 'vice_supervisor_id', 'HFlag', 'ChFlag', 'CFlag', 'c_level_id',
        'Sflag', 'StFlag', 'profile_picture', 'email_verified_at',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'CFlag' => 'boolean',
        'Sflag' => 'boolean',
        'StFlag' => 'boolean',
    ];

    public function staff(): HasMany
    {
        return $this->hasMany(User::class, 'supervisor_id');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function viceSupervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vice_supervisor_id');
    }

    public function monthlyFeedbacks(): HasMany
    {
        return $this->hasMany(MonthlyFeedback::class);
    }

    public function otps(): HasMany
    {
        return $this->hasMany(OTP::class);
    }

    public function dailyReports(): HasMany
    {
        return $this->hasMany(DailyReport::class);
    }

    public function kpis(): HasMany
    {
        return $this->hasMany(KPIRating::class);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }
}
