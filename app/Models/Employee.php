<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'position',
        'phone',
        'base_salary',
        'status',
    ];

    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }
}
