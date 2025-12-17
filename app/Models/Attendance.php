<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'user_id',
        'attendance_date',
        'attendance_time',
        'greeting',
        'points_earned',
        'rank',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'attendance_time' => 'datetime',
        'points_earned' => 'integer',
        'rank' => 'integer',
    ];

    /**
     * Get the site that owns the attendance.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the user that made the attendance.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}






