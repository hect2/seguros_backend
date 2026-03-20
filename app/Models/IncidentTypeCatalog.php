<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncidentTypeCatalog extends Model
{
    protected $table = 'incident_type_catalogs';

    protected $fillable = [
        'name',
        'slug',
        'active'
    ];
}