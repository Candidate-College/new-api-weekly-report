<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CLevelDivision extends Model
{
    use HasFactory;

    protected $fillable = ['c_level_id', 'division_id'];
    protected $table = 'c_level_divisions';

    public function user()
    {
        return $this->belongsTo(User::class, 'c_level_id');
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }
}
