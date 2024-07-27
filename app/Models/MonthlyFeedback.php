<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyFeedback extends Model
{
    protected $fillable = ['user_id', 'year', 'month', 'content_text'];

    protected $primaryKey = ['user_id', 'year', 'month'];
    public $incrementing = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
