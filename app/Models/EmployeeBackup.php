<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeBackup extends Model
{
    protected $fillable = [
        'employee_id',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];
}
