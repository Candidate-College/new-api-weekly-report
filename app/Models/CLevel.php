<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CLevel extends Model
{
    use HasFactory;

    protected $table = 'c_levels';
    protected $fillable = ['name', 'abbreviation', 'responsibility', 'description']; // Add 'name' and 'abbreviation' to the fillable array

    /**
     * Define the relationship with the Division model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function divisions(): BelongsToMany
    {
        return $this->belongsToMany(Division::class, 'c_level_divisions');
    }
}
