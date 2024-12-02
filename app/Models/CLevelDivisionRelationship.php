<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CLevelDivisionRelationship extends Model
{
    use HasFactory;

    protected $fillable = ['c_level_id', 'division_id'];
    protected $table = 'c_level_divisions_relationship';

    public function user() : BelongsTo
    {
        return $this->belongsTo(CLevel::class, 'c_level_id');
    }

    public function division() : BelongsTo
    {
        return $this->belongsTo(Division::class, 'division_id');
    }
}
