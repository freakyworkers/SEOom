<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MainWidgetContainer extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'columns',
        'column_merges',
        'vertical_align',
        'full_width',
        'full_height',
        'widget_spacing',
        'background_type',
        'background_color',
        'background_gradient_start',
        'background_gradient_end',
        'background_gradient_direction',
        'background_image_url',
        'order',
    ];

    protected $casts = [
        'columns' => 'integer',
        'column_merges' => 'array',
        'order' => 'integer',
        'full_width' => 'boolean',
        'full_height' => 'boolean',
        'widget_spacing' => 'integer',
    ];

    /**
     * Get the site that owns the container.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the widgets in this container.
     */
    public function widgets()
    {
        return $this->hasMany(MainWidget::class, 'container_id')->orderBy('column_index')->orderBy('order');
    }

    /**
     * Get widgets grouped by column index.
     */
    public function getWidgetsByColumn()
    {
        return $this->widgets()->get()->groupBy('column_index');
    }
}


