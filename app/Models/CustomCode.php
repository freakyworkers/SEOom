<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'location',
        'code',
    ];

    /**
     * Get the site that owns the custom code.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get custom code by location for a site.
     */
    public static function getByLocation($siteId, $location)
    {
        return self::where('site_id', $siteId)
            ->where('location', $location)
            ->first();
    }
}




