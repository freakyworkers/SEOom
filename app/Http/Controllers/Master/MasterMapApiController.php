<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterMapApiController extends Controller
{
    /**
     * Display the map API settings page.
     */
    public function index()
    {
        // 마스터 설정에서 지도 API 키 가져오기
        $googleApiKey = $this->getMasterSetting('map_api_google_key', '');
        $naverApiKey = $this->getMasterSetting('map_api_naver_key', '');
        $kakaoApiKey = $this->getMasterSetting('map_api_kakao_key', '');
        
        return view('master.map-api.index', compact('googleApiKey', 'naverApiKey', 'kakaoApiKey'));
    }

    /**
     * Update map API settings.
     */
    public function update(Request $request)
    {
        $request->validate([
            'google_api_key' => 'nullable|string|max:500',
            'naver_api_key' => 'nullable|string|max:500',
            'kakao_api_key' => 'nullable|string|max:500',
        ]);

        $this->setMasterSetting('map_api_google_key', $request->input('google_api_key', ''));
        $this->setMasterSetting('map_api_naver_key', $request->input('naver_api_key', ''));
        $this->setMasterSetting('map_api_kakao_key', $request->input('kakao_api_key', ''));

        return redirect()->route('master.map-api.index')
            ->with('success', '지도 API 설정이 저장되었습니다.');
    }

    /**
     * Get master setting value.
     */
    private function getMasterSetting($key, $default = null)
    {
        $masterSite = \App\Models\Site::getMasterSite();
        if (!$masterSite) {
            return $default;
        }
        
        $setting = DB::table('site_settings')
            ->where('site_id', $masterSite->id)
            ->where('key', $key)
            ->first();
        
        return $setting ? $setting->value : $default;
    }

    /**
     * Set master setting value.
     */
    private function setMasterSetting($key, $value)
    {
        $masterSite = \App\Models\Site::getMasterSite();
        if (!$masterSite) {
            throw new \Exception('마스터 사이트를 찾을 수 없습니다.');
        }
        
        return DB::table('site_settings')->updateOrInsert(
            [
                'site_id' => $masterSite->id,
                'key' => $key,
            ],
            [
                'value' => $value,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}

