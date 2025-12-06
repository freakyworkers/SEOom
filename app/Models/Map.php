<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Map extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'name',
        'map_type',
        'address',
        'latitude',
        'longitude',
        'zoom',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'zoom' => 'integer',
    ];

    /**
     * Get the site that owns the map.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}
