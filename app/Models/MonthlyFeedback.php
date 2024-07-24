<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyFeedback extends Model
{
    use HasFactory;

    protected $table = 'monthly_feedback';

    protected $fillable = ['user_id', 'supervisor_id', 'month', 'year', 'content'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function supervisor()
    {
        return $this->belongsTo(Supervisor::class);
    }
}
