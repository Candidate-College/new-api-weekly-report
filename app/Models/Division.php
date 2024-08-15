<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    use HasFactory;

    protected $table = 'divisions';
    protected $fillable = ['name'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function kpis()
    {
        return $this->hasMany(DivisionKpi::class);
    }

    public function cLevelDivisions()
    {
        return $this->hasMany(CLevelDivision::class);
    }
}
