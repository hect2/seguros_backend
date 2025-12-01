<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;
    protected $table = 'employees';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'full_name',
        'dpi',
        'birth_date',
        'phone',
        'email',
        'files',
        'status_id',
        'digessp_fecha_vencimiento',
    ];

    protected $casts = [
        'files' => 'array',
        'birth_date' => 'date:Y-m-d',
    ];

    public function positions()
    {
        return $this->hasMany(Position::class, 'employee_id', 'id');
    }

    public function trackings()
    {
        return $this->hasMany(Tracking::class, 'employee_id', 'id');
    }

    public function status()
    {
        return $this->belongsTo(EmployeeStatus::class);
    }
}
