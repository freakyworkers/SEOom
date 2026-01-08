<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterSocialLoginController extends Controller
{
    /**
     * Display the social login settings page.
     */
    public function index()
    {
        // 마스터 설정에서 소셜 로그인 정보 가져오기
        $googleClientId = $this->getMasterSetting('social_login_google_client_id', '');
        $googleClientSecret = $this->getMasterSetting('social_login_google_client_secret', '');
        $naverClientId = $this->getMasterSetting('social_login_naver_client_id', '');
        $naverClientSecret = $this->getMasterSetting('social_login_naver_client_secret', '');
        $kakaoClientId = $this->getMasterSetting('social_login_kakao_client_id', '');
        $kakaoClientSecret = $this->getMasterSetting('social_login_kakao_client_secret', '');
        $enabled = $this->getMasterSetting('social_login_enabled', false);
        
        return view('master.social-login.index', compact(
            'googleClientId',
            'googleClientSecret',
            'naverClientId',
            'naverClientSecret',
            'kakaoClientId',
            'kakaoClientSecret',
            'enabled'
        ));
    }

    /**
     * Update social login settings.
     */
    public function update(Request $request)
    {
        $request->validate([
            'enabled' => 'nullable|boolean',
            'google_client_id' => 'nullable|string|max:500',
            'google_client_secret' => 'nullable|string|max:500',
            'naver_client_id' => 'nullable|string|max:500',
            'naver_client_secret' => 'nullable|string|max:500',
            'kakao_client_id' => 'nullable|string|max:500',
            'kakao_client_secret' => 'nullable|string|max:500',
        ]);

        $this->setMasterSetting('social_login_enabled', $request->has('enabled') ? 1 : 0);
        $this->setMasterSetting('social_login_google_client_id', $request->input('google_client_id', ''));
        $this->setMasterSetting('social_login_google_client_secret', $request->input('google_client_secret', ''));
        $this->setMasterSetting('social_login_naver_client_id', $request->input('naver_client_id', ''));
        $this->setMasterSetting('social_login_naver_client_secret', $request->input('naver_client_secret', ''));
        $this->setMasterSetting('social_login_kakao_client_id', $request->input('kakao_client_id', ''));
        $this->setMasterSetting('social_login_kakao_client_secret', $request->input('kakao_client_secret', ''));

        return redirect()->route('master.social-login.index')
            ->with('success', '소셜 로그인 설정이 저장되었습니다.');
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







