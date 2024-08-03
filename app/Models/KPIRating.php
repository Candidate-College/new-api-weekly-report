<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Database\Factories\KpiRatingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Thiagoprz\EloquentCompositeKey\HasCompositePrimaryKey;


class KPIRating extends Model
{
    use HasFactory;

    protected $table = 'kpis';
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

    public function getKeyName()
    {
        return $this->primaryKey;
    }

    /**
 * @return \Illuminate\Database\Eloquent\Factories\Factory
 */
    protected static function newFactory()
    {
        return KpiRatingFactory::new();
    }	
}
