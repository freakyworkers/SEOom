<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddonProductOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'addon_product_id',
        'name',
        'amount_mb',
        'price',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'amount_mb' => 'integer',
        'price' => 'decimal:2',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the addon product that owns this option.
     */
    public function addonProduct()
    {
        return $this->belongsTo(AddonProduct::class);
    }

    /**
     * Get formatted amount (GB).
     */
    public function getAmountGbAttribute(): float
    {
        if (!$this->amount_mb || $this->amount_mb <= 0) {
            return 0;
        }
        return round($this->amount_mb / 1024, 2);
    }
}


