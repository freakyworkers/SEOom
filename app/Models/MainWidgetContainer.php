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
        'vertical_align',
        'full_width',
        'order',
    ];

    protected $casts = [
        'columns' => 'integer',
        'order' => 'integer',
        'full_width' => 'boolean',
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


