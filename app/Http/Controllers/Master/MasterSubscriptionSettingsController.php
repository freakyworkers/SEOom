<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterSubscriptionSettingsController extends Controller
{
    /**
     * Display the subscription settings page.
     */
    public function index()
    {
        $masterSite = \App\Models\Site::getMasterSite();
        if (!$masterSite) {
            abort(404, '마스터 사이트를 찾을 수 없습니다.');
        }

        $trialDays = $masterSite->getSetting('subscription_trial_days', 7);
        $tossSecretKey = $masterSite->getSetting('toss_payments_secret_key', '');
        $tossClientKey = $masterSite->getSetting('toss_payments_client_key', '');
        $tossBaseUrl = $masterSite->getSetting('toss_payments_base_url', 'https://api.tosspayments.com');

        return view('master.subscription-settings.index', compact('trialDays', 'tossSecretKey', 'tossClientKey', 'tossBaseUrl'));
    }

    /**
     * Update subscription settings.
     */
    public function update(Request $request)
    {
        $request->validate([
            'trial_days' => 'required|integer|min:0|max:30',
            'toss_secret_key' => 'nullable|string|max:500',
            'toss_client_key' => 'nullable|string|max:500',
            'toss_base_url' => 'nullable|string|max:500',
        ]);

        $masterSite = \App\Models\Site::getMasterSite();
        if (!$masterSite) {
            return back()->with('error', '마스터 사이트를 찾을 수 없습니다.');
        }

        $masterSite->setSetting('subscription_trial_days', $request->input('trial_days'));
        $masterSite->setSetting('toss_payments_secret_key', $request->input('toss_secret_key', ''));
        $masterSite->setSetting('toss_payments_client_key', $request->input('toss_client_key', ''));
        $masterSite->setSetting('toss_payments_base_url', $request->input('toss_base_url', 'https://api.tosspayments.com'));

        return redirect()->route('master.subscription-settings.index')
            ->with('success', '구독 설정이 저장되었습니다.');
    }
}

