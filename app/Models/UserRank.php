<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRank extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'rank',
        'name',
        'criteria_type',
        'criteria_value',
        'display_type',
        'icon_path',
        'color',
        'order',
    ];

    protected $casts = [
        'rank' => 'integer',
        'criteria_value' => 'integer',
        'order' => 'integer',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function getIconUrlAttribute()
    {
        if ($this->icon_path) {
            return asset('storage/' . $this->icon_path);
        }
        return null;
    }
}








