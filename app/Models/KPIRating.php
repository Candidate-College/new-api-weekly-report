<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KPIRating extends Model
{
    protected $fillable = [
        'user_id', 'year', 'month', 'activeness_Q1_score', 'activeness_Q2_score',
        'activeness_Q3_score', 'ability_Q1_score', 'communication_Q1_score',
        'communication_Q2_score', 'discipline_Q1_score', 'discipline_Q2_score',
        'discipline_Q3_score',
    ];

    protected $primaryKey = ['user_id', 'year', 'month'];
    public $incrementing = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
