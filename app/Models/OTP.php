<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OTP extends Model
{
    protected $table = 'otps';
    protected $fillable = ['user_id', 'created_at', 'expiration_time', 'OTP_code'];

    protected $primaryKey = ['user_id', 'created_at'];
    public $incrementing = false;
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
