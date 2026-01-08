<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'name',
        'link_type',
        'link_target',
        'parent_id',
        'order',
        'font_color',
    ];

    /**
     * Get the site that owns the menu.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the parent menu.
     */
    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    /**
     * Get the child menus.
     */
    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('order');
    }

    /**
     * Get the board if link_type is 'board'.
     */
    public function board()
    {
        return $this->belongsTo(Board::class, 'link_target');
    }

    /**
     * Get the URL for this menu item.
     */
    public function getUrlAttribute()
    {
        switch ($this->link_type) {
            case 'board':
                $board = Board::find($this->link_target);
                return $board ? route('boards.show', ['site' => $this->site->slug, 'slug' => $board->slug]) : '#';
            case 'custom_page':
                $customPage = \App\Models\CustomPage::find($this->link_target);
                return $customPage ? route('custom-pages.show', ['site' => $this->site->slug, 'slug' => $customPage->slug]) : '#';
            case 'external_link':
                return $this->link_target ?? '#';
            case 'attendance':
                return route('attendance.index', ['site' => $this->site->slug]);
            case 'point_exchange':
                return route('point-exchange.index', ['site' => $this->site->slug]);
            case 'event_application':
                return route('event-application.index', ['site' => $this->site->slug]);
            case 'anchor':
                // 앵커 링크는 현재 페이지에서 해당 ID로 스크롤
                return '#' . ($this->link_target ?? '');
            default:
                return '#';
        }
    }
}


