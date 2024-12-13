<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CLevelDivision extends Model
{
    use HasFactory;

    protected $table = 'c_level_divisions';  // Ensure the table name is correct
    protected $fillable = ['c_level_id', 'division_id'];  // Fillable fields for mass assignment

    /**
     * Define the relationship with the CLevel model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cLevel(): BelongsTo
    {
        return $this->belongsTo(CLevel::class, 'c_level_id');
    }

    /**
     * Define the relationship with the Division model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'division_id');
    }
}
