<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    use HasFactory;

    protected $table = 'divisions';
    protected $fillable = ['name', 'abbreviation', 'responsibility', 'description'];

    /**
     * Define the relationship with the CLevel model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function clevels(): BelongsToMany
    {
        return $this->belongsToMany(CLevel::class, 'c_level_divisions');
    }

    /**
     * Define the relationship with the CLevelDivision model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cLevelDivisions(): HasMany
    {
        return $this->hasMany(CLevelDivision::class);
    }

    public function members()
    {
        return $this->hasMany(User::class, 'division_id');
    }
}
