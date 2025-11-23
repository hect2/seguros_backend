<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    use HasFactory;

    protected $table = 'offices';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'district_id',
        'user_id',
        'code',
        'name',
        'direction',
        'phone',
        'observations',
        'status',
    ];

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withDefault();
    }

    public function employees()
    {
        return $this->hasMany(Position::class,'office_id','id');
    }
}
