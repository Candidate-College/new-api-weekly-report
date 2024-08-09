<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DivisionKPI extends Model
{
    use HasFactory;

    protected $table = 'division_kpis';
    protected $fillable = ['division_id', 'year', 'task_name', 'task_id'];

    public function division()
    {
        return $this->belongsTo(Division::class);
    }
}
