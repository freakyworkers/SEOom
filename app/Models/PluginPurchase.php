<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PluginPurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'plugin_id',
        'site_id',
        'user_id',
        'status',
        'purchased_at',
        'expires_at',
    ];

    protected $casts = [
        'purchased_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get plugin
     */
    public function plugin()
    {
        return $this->belongsTo(Plugin::class);
    }

    /**
     * Get site
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

