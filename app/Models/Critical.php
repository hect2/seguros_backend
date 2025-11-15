<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Critical extends Model
{
    use HasFactory;
    protected $table = 'criticals';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function incidents()
    {
        return $this->hasMany(Incident::class);
    }

}
