<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CLevel extends Model
{
    use HasFactory;

    protected $fillable = ['name','created_at','updated_at'];
    protected $table = 'c_levels';
    public function divisions()
    {
        return $this->hasMany(CLevelDivisionRelationship::class, 'c_level_id');
    }
}
