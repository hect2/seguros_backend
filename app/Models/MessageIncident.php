<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageIncident extends Model
{
    use HasFactory;

    protected $table = 'message_incidents';

    protected $fillable = [
        'id_message_reply',
        'id_incident',
        'id_user',
        'message',
        'attachments',
    ];

    protected $casts = [
        'attachments' => 'array',
    ];

    /**
     * Estructura esperada del JSON `attachments` (si se usa):
     * [
     *   {
     *     "nombre_archivo": "captura1.png",
     *     "fecha_emision": "2025-11-10"
     *   }
     * ]
     */

    // 🔹 Relaciones
    public function incident()
    {
        return $this->belongsTo(Incident::class, 'id_incident', 'IncidentID');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    public function replyTo()
    {
        return $this->belongsTo(MessageIncident::class, 'id_message_reply');
    }

    public function replies()
    {
        return $this->hasMany(MessageIncident::class, 'id_message_reply');
    }
}
