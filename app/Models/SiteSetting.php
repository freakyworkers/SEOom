<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'key',
        'value',
    ];

    /**
     * Get the site that owns the setting.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get setting value as array (if JSON).
     */
    public function getValueAttribute($value)
    {
        $decoded = json_decode($value, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
    }

    /**
     * Set setting value (auto-encode if array).
     */
    public function setValueAttribute($value)
    {
        $this->attributes['value'] = is_array($value) ? json_encode($value) : $value;
    }
}

