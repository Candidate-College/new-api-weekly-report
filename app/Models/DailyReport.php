<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyReport extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'content_text', 'content_photo', 'timestamp'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
