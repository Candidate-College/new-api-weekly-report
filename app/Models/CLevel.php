<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CLevel extends Model
{
    use HasFactory;

    protected $table = 'c_levels';
    
    protected $fillable = ['user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
