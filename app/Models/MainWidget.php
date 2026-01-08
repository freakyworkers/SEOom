<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MainWidget extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'container_id',
        'column_index',
        'type',
        'title',
        'settings',
        'order',
        'is_active',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'column_index' => 'integer',
        'order' => 'integer',
    ];

    /**
     * Get the site that owns the widget.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the container that owns the widget.
     */
    public function container()
    {
        return $this->belongsTo(MainWidgetContainer::class, 'container_id');
    }

    /**
     * Get available widget types (same as SidebarWidget).
     */
    public static function getAvailableTypes(): array
    {
        return SidebarWidget::getAvailableTypes();
    }
}









