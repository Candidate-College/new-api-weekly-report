<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyReport extends Model
{
    protected $fillable = ['user_id', 'created_at', 'content_text', 'content_photo', 'last_updated_at'];

    protected $primaryKey = ['user_id', 'created_at'];
    public $incrementing = false;
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
