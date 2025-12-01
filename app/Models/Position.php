<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use HasFactory;
    protected $table = 'positions';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'employee_id',
        'office_id',
        'district_id',
        'admin_position_type_id',
        'operative_position_type_id',
        'initial_salary',
        'bonuses',
        'status',
    ];

    public function employees()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function office()
    {
        return $this->belongsTo(Office::class, 'office_id', 'id');
    }

    public function district()
    {
        return $this->belongsTo(District::class, 'district_id', 'id');
    }

    public function adminPositionType()
    {
        return $this->belongsTo(PositionType::class, 'admin_position_type_id', 'id');
    }

    public function operativePositionType()
    {
        return $this->belongsTo(PositionType::class, 'operative_position_type_id', 'id');
    }
}

