<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Response;

class AdsController extends Controller
{
    /**
     * Generate ads.txt
     */
    public function index(Site $site)
    {
        $adsTxt = $site->getSetting('adsense_ads_txt', '');
        
        // 사용자가 ads.txt를 입력한 경우 사용, 없으면 빈 응답
        if (empty($adsTxt)) {
            return response('', 200)
                ->header('Content-Type', 'text/plain');
        }
        
        return response($adsTxt, 200)
            ->header('Content-Type', 'text/plain');
    }
}








