<?php

namespace App\Models;

use Database\Factories\OtpFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Thiagoprz\EloquentCompositeKey\HasCompositePrimaryKey;

class OTP extends Model
{
    use HasFactory;

    protected $table = 'otps';
    protected $fillable = ['user_id', 'created_at', 'expiration_time', 'OTP_code', 'token'];
    protected $primaryKey = ['user_id', 'created_at'];
    public $incrementing = false;
    public $timestamps = false;
    protected $casts = [
        'expiration_time' => 'datetime',
    ];

    public function user(): BelongsTo
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
        return OtpFactory::new();
    }
}
