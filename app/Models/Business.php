<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'direction',
        'phone',
    ];

    public function distrito()
    {
        return $this->hasMany(Distrito::class);
    }
}
