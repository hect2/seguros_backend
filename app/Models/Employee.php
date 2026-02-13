<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Employee extends Model
{
    use HasFactory;
    protected $table = 'employees';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'full_name',
        'dpi',
        'birth_date',
        'phone',
        'email',
        'files',
        'status_id',
        'digessp_code',
        'digessp_fecha_vencimiento',
    ];

    protected $casts = [
        'files' => 'array',
        'birth_date' => 'date:Y-m-d',
    ];

    public function positions()
    {
        return $this->hasMany(Position::class, 'employee_id', 'id');
    }

    public function trackings()
    {
        return $this->hasMany(Tracking::class, 'employee_id', 'id');
    }

    public function status()
    {
        return $this->belongsTo(EmployeeStatus::class);
    }

    public function history()
    {
        return $this->belongsTo(EmployeeBackup::class, 'employee_id', 'id');
    }

    public function lastHistory()
    {
        return $this->hasOne(EmployeeBackup::class)->latestOfMany();
    }

    public function lastHistory7Days()
    {
        return $this->hasOne(EmployeeBackup::class)
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->latestOfMany();
    }

    public function lastHistory15Days()
    {
        return $this->hasOne(EmployeeBackup::class)
            ->where('created_at', '>=', Carbon::now()->subDays(15))
            ->latestOfMany();
    }

    public function backups()
    {
        return $this->hasMany(EmployeeBackup::class);
    }

    public function getLastHistoryFromDaysAgo(int $days)
    {
        return $this->backups()
            ->where('created_at', '>=', now()->subDays($days))
            ->latest()
            ->first();
    }


}
