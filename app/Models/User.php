<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'first_name', 'last_name', 'email', 'instagram', 'linkedin', 'batch_no',
        'password', 'division', 'supervisor_id', 'vice_supervisor_id', 'CFlag',
        'Sflag', 'StFlag', 'profile_picture',
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

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function viceSupervisor()
    {
        return $this->belongsTo(User::class, 'vice_supervisor_id');
    }

    public function monthlyFeedbacks()
    {
        return $this->hasMany(MonthlyFeedback::class);
    }

    public function otps()
    {
        return $this->hasMany(OTP::class);
    }

    public function dailyReports()
    {
        return $this->hasMany(DailyReport::class);
    }

    public function kpis()
    {
        return $this->hasMany(KPIRating::class);
    }
}
