<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ToggleMenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'toggle_menu_id',
        'title',
        'content',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    /**
     * Get the toggle menu that owns this item.
     */
    public function toggleMenu()
    {
        return $this->belongsTo(ToggleMenu::class);
    }
}






