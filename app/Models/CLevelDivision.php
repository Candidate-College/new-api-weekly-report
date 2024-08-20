<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CLevelDivision extends Model
{
    use HasFactory;

    protected $fillable = ['c_level_id', 'division_id'];
    protected $table = 'c_level_divisions';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'c_level_id');
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }
}
