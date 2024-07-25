<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KPIRating extends Model
{
    use HasFactory;
    
    protected $table = 'kpi_ratings';

    protected $fillable = ['user_id', 'year', 'month', 'activeness_Q1_score', 'activeness_Q2_score', 'activeness_Q3_score', 'ability_Q1_score', 'communication_Q1_score', 'communication_Q2_score', 'discipline_Q1_score', 'discipline_Q2_score', 'discipline_Q3_score'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
