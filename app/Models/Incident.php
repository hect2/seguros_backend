<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incident extends Model
{
    use HasFactory;

    protected $table = 'incidents';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'title',
        'type_id',
        'district_id',
        'criticity_id',
        'description',
        'files',
        'status_id',
        'user_reported',
        'user_assigned',
        'follow_date',
    ];

    protected $casts = [
        'files' => 'array', // Para manejar JSON como array en Laravel
    ];

    /**
     * Estructura esperada del JSON `files`:
     * [
     *   {
     *     "nombre_archivo": "documento1.pdf",
     *     "fecha_emision": "2025-11-10"
     *   },
     *   {
     *     "nombre_archivo": "imagen.png",
     *     "fecha_emision": "2025-11-09"
     *   }
     * ]
     */

    // 🔹 Relaciones
    public function district()
    {
        return $this->belongsTo(District::class, 'district_id', 'id');
    }

    public function userReported()
    {
        return $this->belongsTo(User::class, 'user_reported', 'id');
    }

    public function userAssigned()
    {
        return $this->belongsTo(User::class, 'user_assigned', 'id');
    }

    public function messages()
    {
        return $this->hasMany(MessageIncident::class, 'id_incident', 'IncidentID');
    }

    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    public function criticidad()
    {
        return $this->belongsTo(Critical::class, 'criticity_id', 'id');
    }

    public function status()
    {
        return $this->belongsTo(IncidentStatus::class);
    }

}
