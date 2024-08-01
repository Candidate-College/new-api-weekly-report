<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Thiagoprz\EloquentCompositeKey\HasCompositePrimaryKey;

class OTP extends Model
{
    use HasFactory;

    protected $table = 'otps';
    protected $fillable = ['user_id', 'created_at', 'expiration_time', 'OTP_code'];
    protected $primaryKey = ['user_id', 'created_at'];
    public $incrementing = false;
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function getKeyName()
    {
        return $this->primaryKey;
    }
}