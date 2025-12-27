<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the site that owns the custom page.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the widget containers for this custom page.
     */
    public function containers()
    {
        return $this->hasMany(CustomPageWidgetContainer::class, 'custom_page_id')->orderBy('order');
    }

    /**
     * Get all widgets for this custom page.
     */
    public function widgets()
    {
        return $this->hasMany(CustomPageWidget::class, 'custom_page_id');
    }
}





