<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ToggleMenu extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'name',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Get the site that owns the toggle menu.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the items for this toggle menu.
     */
    public function items()
    {
        return $this->hasMany(ToggleMenuItem::class)->orderBy('order');
    }
}

