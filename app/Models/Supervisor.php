<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supervisor extends Model
{
    protected $fillable = ['user_id', 'division'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function staff()
    {
        return $this->hasMany(Staff::class);
    }

    public function monthlyFeedback()
    {
        return $this->hasMany(MonthlyFeedback::class);
    }
}
