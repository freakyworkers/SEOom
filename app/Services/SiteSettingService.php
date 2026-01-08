<?php

namespace App\Services;

use App\Models\Site;
use App\Models\SiteSetting;

class SiteSettingService
{
    /**
     * Get all settings for a site.
     */
    public function getSettingsBySite($siteId)
    {
        return SiteSetting::where('site_id', $siteId)
            ->get()
            ->pluck('value', 'key')
            ->toArray();
    }

    /**
     * Get a setting value by key.
     */
    public function getSetting($siteId, $key, $default = null)
    {
        $setting = SiteSetting::where('site_id', $siteId)
            ->where('key', $key)
            ->first();

        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value.
     */
    public function setSetting($siteId, $key, $value)
    {
        return SiteSetting::updateOrCreate(
            [
                'site_id' => $siteId,
                'key' => $key,
            ],
            [
                'value' => $value,
            ]
        );
    }

    /**
     * Set multiple settings at once.
     */
    public function setSettings($siteId, array $settings)
    {
        foreach ($settings as $key => $value) {
            $this->setSetting($siteId, $key, $value);
        }

        return true;
    }

    /**
     * Delete a setting.
     */
    public function deleteSetting($siteId, $key)
    {
        return SiteSetting::where('site_id', $siteId)
            ->where('key', $key)
            ->delete();
    }
}













