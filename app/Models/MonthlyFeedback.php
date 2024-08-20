<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Thiagoprz\EloquentCompositeKey\HasCompositePrimaryKey;

class MonthlyFeedback extends Model
{
    protected $table = 'monthly_feedbacks';
    use HasFactory;
    protected $fillable = ['user_id', 'year', 'month', 'content_text'];

    protected $primaryKey = ['user_id', 'year', 'month'];
    public $incrementing = false;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function getKeyName()
    {
        return $this->primaryKey;
    }
}
