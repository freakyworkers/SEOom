<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MobileMenu extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'icon_type',
        'icon_path',
        'name',
        'link_type',
        'link_target',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    /**
     * Get the site that owns the mobile menu.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the board if link_type is 'board'.
     */
    public function board()
    {
        return $this->belongsTo(Board::class, 'link_target');
    }

    /**
     * Get the URL for this mobile menu item.
     */
    public function getUrlAttribute()
    {
        $siteSlug = $this->site->slug ?? 'default';

        switch ($this->link_type) {
            case 'board':
                $board = Board::find($this->link_target);
                return $board ? route('boards.show', ['site' => $siteSlug, 'slug' => $board->slug]) : '#';
            case 'custom_page':
                $customPage = \App\Models\CustomPage::find($this->link_target);
                return $customPage ? route('custom-pages.show', ['site' => $siteSlug, 'slug' => $customPage->slug]) : '#';
            case 'external_link':
                return $this->link_target;
            case 'attendance':
                return route('attendance.index', ['site' => $siteSlug]);
            case 'point_exchange':
                return route('point-exchange.index', ['site' => $siteSlug]);
            case 'event_application':
                return route('event-application.index', ['site' => $siteSlug]);
            default:
                return '#';
        }
    }

    /**
     * Get the icon URL or class.
     */
    public function getIconAttribute()
    {
        if ($this->icon_type === 'image' && $this->icon_path) {
            return asset('storage/' . $this->icon_path);
        }
        return $this->icon_path; // 기본 아이콘 클래스명
    }
}


