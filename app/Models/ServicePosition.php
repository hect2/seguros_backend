<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicePosition extends Model
{
    protected $fillable = [
        'business_id',
        'name',
        'location',
        'shift',
        'service_type',
        'active',
    ];

    /* 🔗 Relaciones */

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id', 'id');
    }

    public function collaborators()
    {
        return $this->hasMany(Employee::class);
    }
}
