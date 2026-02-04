<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncidentCatalog extends Model
{
    protected $table = 'incident_catalogs';

    protected $fillable = [
        'name',
        'slug',
        'type',
        'group',
        'active'
    ];
}
