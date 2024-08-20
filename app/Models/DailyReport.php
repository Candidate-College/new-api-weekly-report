<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Thiagoprz\EloquentCompositeKey\HasCompositePrimaryKey;

class DailyReport extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'created_at', 'content_text', 'content_photo', 'last_updated_at'];

    protected $primaryKey = ['user_id', 'created_at'];
    public $incrementing = false;
    public $timestamps = false;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function getKeyName()
    {
        return $this->primaryKey;
    }
    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
