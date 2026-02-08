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

        'employee_code',
        'admission_date',
        'departure_date',
        'client_id',
        'service_position_id',
        'position_id',
        'employee_status_id',
        'turn',
        'reason_for_leaving',
        'suspension_date',
        'life_insurance_code',
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

    public function client()
    {
        return $this->belongsTo(Business::class, 'client_id', 'id');
    }

    public function servicePosition()
    {
        return $this->belongsTo(ServicePosition::class);
    }
}

