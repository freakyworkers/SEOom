<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomPageWidgetContainer extends Model
{
    use HasFactory;

    protected $fillable = [
        'custom_page_id',
        'columns',
        'column_merges',
        'vertical_align',
        'full_width',
        'full_height',
        'background_type',
        'background_color',
        'background_gradient_start',
        'background_gradient_end',
        'background_gradient_angle',
        'background_image_url',
        'order',
    ];

    protected $casts = [
        'columns' => 'integer',
        'column_merges' => 'array',
        'order' => 'integer',
        'full_width' => 'boolean',
        'full_height' => 'boolean',
    ];

    /**
     * Get the custom page that owns the container.
     */
    public function customPage()
    {
        return $this->belongsTo(CustomPage::class, 'custom_page_id');
    }

    /**
     * Get the widgets in this container.
     */
    public function widgets()
    {
        return $this->hasMany(CustomPageWidget::class, 'container_id')->orderBy('column_index')->orderBy('order');
    }
}


