<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Services\MasterAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MasterAuthController extends Controller
{
    protected $authService;

    public function __construct(MasterAuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Show master login form.
     */
    public function showLoginForm()
    {
        return view('master.auth.login');
    }

    /**
     * Handle master login request.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        if ($this->authService->login($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();
            if (\Route::has('master.admin.dashboard')) {
                return redirect()->route('master.admin.dashboard');
            }
            return redirect('/admin/dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput();
    }

    /**
     * Handle master logout request.
     */
    public function logout(Request $request)
    {
        $this->authService->logout();

        return redirect()->route('master.login');
    }

    /**
     * Generate SSO token for master console auto-login from master site.
     */
    public function generateSsoToken()
    {
        // 마스터 사이트에서 로그인한 사용자 확인
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => '로그인이 필요합니다.',
            ], 401);
        }

        // 세션에서 마스터 사용자 정보 확인
        $masterUserId = session('master_user_id');
        if (!$masterUserId) {
            // 이메일로 마스터 사용자 확인
            $masterUser = \App\Models\MasterUser::where('email', $user->email)->first();
            if (!$masterUser) {
                return response()->json([
                    'success' => false,
                    'message' => '마스터 사용자가 아닙니다.',
                ], 403);
            }
            $masterUserId = $masterUser->id;
        }

        // 토큰 생성 (10분 유효)
        $token = \Illuminate\Support\Str::random(32);
        \Illuminate\Support\Facades\Cache::put('master_console_sso_token_' . $token, [
            'master_user_id' => $masterUserId,
        ], now()->addMinutes(10));

        return response()->json([
            'success' => true,
            'token' => $token,
            'url' => route('master.console.sso', ['token' => $token]),
        ]);
    }

    /**
     * Handle master console SSO login.
     */
    public function sso(Request $request)
    {
        $token = $request->input('token');
        
        if (!$token) {
            return redirect()->route('master.login')
                ->with('error', 'SSO 토큰이 필요합니다.');
        }

        $cacheKey = 'master_console_sso_token_' . $token;
        $ssoData = \Illuminate\Support\Facades\Cache::get($cacheKey);

        if (!$ssoData || !isset($ssoData['master_user_id'])) {
            return redirect()->route('master.login')
                ->with('error', '유효하지 않거나 만료된 SSO 토큰입니다.');
        }

        // 마스터 사용자 찾기
        $masterUser = \App\Models\MasterUser::find($ssoData['master_user_id']);
        if (!$masterUser) {
            return redirect()->route('master.login')
                ->with('error', '마스터 사용자를 찾을 수 없습니다.');
        }

        // 토큰 삭제
        \Illuminate\Support\Facades\Cache::forget($cacheKey);

        // 마스터 콘솔에 로그인
        auth('master')->login($masterUser, true);
        $request->session()->regenerate();

        if (\Route::has('master.admin.dashboard')) {
            return redirect()->route('master.admin.dashboard');
        }
        return redirect('/admin/dashboard');
    }
}







