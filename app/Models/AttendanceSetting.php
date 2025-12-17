<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'setting_type', // 'rank_points', 'consecutive_points', 'default_points', 'greeting', 'per_page'
        'setting_key',
        'setting_value',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    /**
     * Get the site that owns the setting.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}






