<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;
    protected $table = 'districts';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'code',
        'name',
        'description',
        'status',
        // 'business_id',
    ];

    public function offices()
    {
        return $this->hasMany(Office::class);
    }

    public function getOfficesCountAttribute(): int
    {
        return $this->offices()->count();
    }
}
