<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomPageWidget extends Model
{
    use HasFactory;

    protected $fillable = [
        'custom_page_id',
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
     * Get the custom page that owns the widget.
     */
    public function customPage()
    {
        return $this->belongsTo(CustomPage::class, 'custom_page_id');
    }

    /**
     * Get the container that owns the widget.
     */
    public function container()
    {
        return $this->belongsTo(CustomPageWidgetContainer::class, 'container_id');
    }

    /**
     * Get available widget types (same as MainWidget).
     */
    public static function getAvailableTypes(): array
    {
        return MainWidget::getAvailableTypes();
    }
}




