<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DivisionKPI extends Model
{
    use HasFactory;

    protected $table = 'division_kpis';
    protected $fillable = ['division_id', 'year', 'month','task_name', 'weight', 'target','end_of_month_realization'];
    public $incrementing = false;

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

}
