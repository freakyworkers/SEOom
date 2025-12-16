<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use App\Models\Site;
use App\Models\User;
use App\Models\EmailVerification;
use App\Models\PhoneVerification;
use App\Mail\EmailVerificationMail;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Show login form.
     */
    public function showLoginForm(Site $site)
    {
        return view('auth.login', compact('site'));
    }

    /**
     * Handle login request.
     */
    public function login(Request $request, Site $site)
    {
        $loginMethod = $site->getSetting('registration_login_method', 'email');
        
        $rules = [
            'password' => 'required',
        ];
        
        if ($loginMethod === 'username') {
            $rules['email'] = 'required|string|max:255';
        } else {
            $rules['email'] = 'required|email';
        }
        
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // 마스터 사이트인 경우 마스터 사용자로 로그인 시도
        if ($site->is_master_site) {
            $masterUser = \App\Models\MasterUser::where('email', $request->input('email'))->first();
            
            if ($masterUser && \Illuminate\Support\Facades\Hash::check($request->input('password'), $masterUser->password)) {
                // 해당 사이트의 관리자 계정 찾기 또는 생성 (site_id와 email 조합으로 찾기)
                $admin = \App\Models\User::where('site_id', $site->id)
                    ->where('email', $masterUser->email)
                    ->first();
                
                if (!$admin) {
                    // 해당 사이트에 해당 이메일이 없을 때만 생성
                    // 이메일이 다른 사이트에 존재하더라도, site_id가 다르면 생성 가능해야 함
                    $admin = \App\Models\User::create([
                        'site_id' => $site->id,
                        'name' => $masterUser->name . ' (Master)',
                        'username' => null, // username은 nullable
                        'nickname' => null, // nickname은 nullable
                        'email' => $masterUser->email,
                        'password' => \Illuminate\Support\Facades\Hash::make(uniqid()), // 임시 비밀번호 (사용하지 않음)
                        'role' => 'admin',
                    ]);
                } else {
                    // 역할이 admin이 아니면 업데이트
                    if ($admin->role !== 'admin') {
                        $admin->update(['role' => 'admin']);
                    }
                }
                
                // 사이트 사용자로 로그인
                \Illuminate\Support\Facades\Auth::login($admin, $request->boolean('remember'));
                
                // 세션에 마스터 사용자 정보 저장
                session(['is_master_user' => true, 'master_user_id' => $masterUser->id]);
                
                $request->session()->regenerate();
                
                // intended URL이 있으면 그곳으로, 없으면 현재 페이지 또는 기본 경로로
                $intendedUrl = $request->input('intended_url') ?: session()->pull('url.intended');
                
                if ($intendedUrl) {
                    return redirect($intendedUrl);
                }
                
                // 마스터 사이트인 경우 관리자 대시보드로 리다이렉트
                if ($site->isMasterSite()) {
                    return redirect()->route('master.admin.dashboard');
                }
                // 커스텀 도메인/서브도메인인 경우 루트 경로로, 아니면 /site/{slug}로
                return redirect($site->getHomeUrl());
            }
        }

        // Site별 사용자만 로그인 가능하도록 필터링
        $credentials = [
            'login' => $request->input('email'),
            'password' => $request->input('password'),
            'site_id' => $site->id,
            'login_method' => $loginMethod,
        ];

        if ($this->authService->login($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            // intended URL이 있으면 그곳으로, 없으면 현재 페이지 또는 기본 경로로
            $intendedUrl = $request->input('intended_url') ?: session()->pull('url.intended');
            
            if ($intendedUrl) {
                return redirect($intendedUrl);
            }
            
            // 마스터 사이트인 경우 관리자 대시보드로 리다이렉트
            if ($site->isMasterSite()) {
                // 관리자 권한이 있는 경우에만 대시보드로, 아니면 홈으로
                $user = auth()->user();
                if ($user && $user->canManage()) {
                    return redirect()->route('master.admin.dashboard');
                }
                return redirect('/');
            }
            // 커스텀 도메인/서브도메인인 경우 루트 경로로, 아니면 /site/{slug}로
            return redirect($site->getHomeUrl());
        }

        $errorMessage = $loginMethod === 'username' 
            ? '입력한 아이디와 비밀번호가 일치하지 않습니다.'
            : '입력한 이메일과 비밀번호가 일치하지 않습니다.';

        return back()->withErrors([
            'email' => $errorMessage,
        ])->withInput();
    }

    /**
     * Show registration form.
     */
    public function showRegisterForm(Site $site)
    {
        return view('auth.register', compact('site'));
    }

    /**
     * Handle registration request.
     */
    public function register(Request $request, Site $site)
    {
        $rules = [
            'username' => 'required|string|max:255|unique:users,username,NULL,id,site_id,' . $site->id,
            'name' => 'required|string|max:255',
            'nickname' => 'required|string|max:255|unique:users,nickname,NULL,id,site_id,' . $site->id,
            'email' => 'required|string|email|max:255|unique:users,email,NULL,id,site_id,' . $site->id,
            'password' => 'required|string|min:8|confirmed',
        ];

        // 전화번호 설정이 활성화된 경우
        if ($site->getSetting('registration_enable_phone', false)) {
            $rules['phone'] = 'nullable|string|max:20';
        }

        // 전화번호 인증 설정이 활성화된 경우
        if ($site->getSetting('registration_enable_phone_verification', false)) {
            $phone = $request->phone;
            $verification = PhoneVerification::where('site_id', $site->id)
                ->where('phone', $phone)
                ->whereNotNull('verified_at')
                ->latest()
                ->first();
            
            if (!$verification || $verification->isExpired()) {
                return back()->withErrors(['phone' => '전화번호 인증을 완료해주세요. (만료되었거나 인증되지 않음)'])->withInput();
            }
        }

        // 주소 설정이 활성화된 경우
        if ($site->getSetting('registration_enable_address', false)) {
            $rules['postal_code'] = 'nullable|string|max:10';
            $rules['address'] = 'nullable|string|max:255';
            $rules['address_detail'] = 'nullable|string|max:255';
        }

        // 이메일 인증 설정이 활성화된 경우
        if ($site->getSetting('registration_enable_email_verification', false)) {
            // 이메일 인증이 완료되었는지 확인
            $email = $request->email;
            $verification = EmailVerification::where('site_id', $site->id)
                ->where('email', $email)
                ->whereNotNull('verified_at')
                ->latest()
                ->first();

            if (!$verification) {
                return back()
                    ->withErrors(['email' => '이메일 인증을 완료해주세요.'])
                    ->withInput();
            }
        }

        // 추천인 설정이 활성화된 경우
        if ($site->getSetting('registration_enable_referrer', false)) {
            $rules['referrer_nickname'] = 'nullable|string|max:255';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // 추천인 검증
        if ($request->filled('referrer_nickname')) {
            $referrer = User::where('site_id', $site->id)
                ->where(function($query) use ($request) {
                    $query->where('nickname', $request->referrer_nickname)
                          ->orWhere('name', $request->referrer_nickname);
                })
                ->first();
            
            if (!$referrer) {
                return back()->withErrors([
                    'referrer_nickname' => '존재하지 않는 추천인 닉네임 또는 이름입니다.',
                ])->withInput();
            }
        }

        $user = $this->authService->register($request->all(), $site->id);

        $this->authService->login($request->only('email', 'password') + ['site_id' => $site->id]);

        // 마스터 사이트인 경우 루트로 리다이렉트
        if ($site->isMasterSite()) {
            return redirect('/');
        }
        // 커스텀 도메인/서브도메인인 경우 루트 경로로, 아니면 /site/{slug}로
        return redirect($site->getHomeUrl());
    }

    /**
     * Handle logout request.
     */
    public function logout(Request $request, Site $site)
    {
        $this->authService->logout();

        // 마스터 사이트인 경우 루트로 리다이렉트
        if ($site->isMasterSite()) {
            return redirect('/');
        }
        // 커스텀 도메인/서브도메인인 경우 루트 경로로, 아니면 /site/{slug}로
        return redirect($site->getHomeUrl());
    }

    /**
     * Send email verification.
     */
    public function sendVerificationEmail(Request $request, Site $site)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->email;

        // 이미 가입된 이메일인지 확인
        $existingUser = User::where('site_id', $site->id)
            ->where('email', $email)
            ->first();

        if ($existingUser) {
            return response()->json([
                'success' => false,
                'message' => '이미 가입된 이메일입니다.',
            ], 400);
        }

        try {
            // 이메일 인증 레코드 생성
            $verification = EmailVerification::createVerification($site->id, $email);

            // SiteSetting에서 메일 설정 가져오기
            $mailer = $site->getSetting('mail_mailer', 'smtp');
            if (empty($mailer)) {
                $mailer = 'smtp';
            }

            $mailUsername = $site->getSetting('mail_username', '');
            $mailConfig = [
                'mailer' => $mailer,
                'host' => $site->getSetting('mail_host', 'smtp.gmail.com'),
                'port' => (int)$site->getSetting('mail_port', '587'),
                'username' => $mailUsername,
                'password' => $site->getSetting('mail_password', ''),
                'encryption' => $site->getSetting('mail_encryption', 'tls'),
                'from' => [
                    'address' => $mailUsername, // mail_username을 발신자 이메일로 사용
                    'name' => $site->getSetting('mail_from_name', $site->name),
                ],
            ];

            // Config::set을 사용하여 메일 설정 변경
            Config::set('mail.default', $mailConfig['mailer']);
            Config::set('mail.mailers.smtp.transport', 'smtp');
            Config::set('mail.mailers.smtp.host', $mailConfig['host']);
            Config::set('mail.mailers.smtp.port', $mailConfig['port']);
            Config::set('mail.mailers.smtp.encryption', $mailConfig['encryption']);
            Config::set('mail.mailers.smtp.username', $mailConfig['username']);
            Config::set('mail.mailers.smtp.password', $mailConfig['password']);
            Config::set('mail.mailers.smtp.timeout', null);
            Config::set('mail.mailers.smtp.auth_mode', null);
            Config::set('mail.from.address', $mailConfig['from']['address']);
            Config::set('mail.from.name', $mailConfig['from']['name']);

            // 메일러 재설정
            app('mail.manager')->forgetMailers();

            // 이메일 발송 시도
            try {
                $fromName = $site->getSetting('mail_from_name', $site->name);
                Mail::to($email)->send(new EmailVerificationMail($site, $email, $verification->token, $fromName));
                
                return response()->json([
                    'success' => true,
                    'message' => '인증번호가 발송되었습니다. 이메일을 확인해주세요.',
                ]);
            } catch (\Exception $mailException) {
                // 개발 환경에서는 메일 발송 실패 시에도 인증번호를 반환
                \Log::warning('이메일 인증 발송 실패 (개발 모드): ' . $mailException->getMessage());
                
                // 개발 환경인 경우 인증번호를 직접 반환
                if (config('app.debug') || app()->environment('local')) {
                    return response()->json([
                        'success' => true,
                        'message' => '개발 모드: 메일 서버 설정이 없어 이메일을 발송하지 못했습니다. 인증번호: ' . $verification->token,
                        'verification_code' => $verification->token,
                        'debug_mode' => true,
                    ]);
                }
                
                // 프로덕션 환경에서는 오류 반환
                throw $mailException;
            }
        } catch (\Exception $e) {
            \Log::error('이메일 인증 발송 실패: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => '이메일 발송 중 오류가 발생했습니다. 메일 서버 설정을 확인해주세요. 오류: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify email with verification code.
     */
    public function verifyEmailCode(Request $request, Site $site)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
        ]);

        $verification = EmailVerification::where('site_id', $site->id)
            ->where('email', $request->email)
            ->where('token', $request->code)
            ->first();

        if (!$verification) {
            return response()->json([
                'success' => false,
                'message' => '인증번호가 일치하지 않습니다.',
            ], 400);
        }

        if ($verification->isExpired()) {
            return response()->json([
                'success' => false,
                'message' => '인증번호가 만료되었습니다. 다시 발송해주세요.',
            ], 400);
        }

        if ($verification->isVerified()) {
            return response()->json([
                'success' => false,
                'message' => '이미 인증된 이메일입니다.',
            ], 400);
        }

        // 인증 완료 처리
        $verification->markAsVerified();

        return response()->json([
            'success' => true,
            'message' => '이메일 인증이 완료되었습니다.',
        ]);
    }

    /**
     * Verify referrer nickname.
     */
    public function verifyReferrer(Request $request, Site $site)
    {
        $request->validate([
            'nickname' => 'required|string|max:255',
        ]);

        // 닉네임으로 먼저 검색, 없으면 이름으로 검색
        $referrer = User::where('site_id', $site->id)
            ->where(function($query) use ($request) {
                $query->where('nickname', $request->nickname)
                      ->orWhere('name', $request->nickname);
            })
            ->first();

        if (!$referrer) {
            return response()->json([
                'success' => false,
                'message' => '존재하지 않는 추천인 닉네임 또는 이름입니다.',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => '추천인 확인 완료',
            'referrer' => [
                'id' => $referrer->id,
                'nickname' => $referrer->nickname ?? $referrer->name,
                'name' => $referrer->name,
            ],
        ]);
    }

    /**
     * Send phone verification SMS.
     */
    public function sendPhoneVerification(Request $request, Site $site)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
        ]);

        $phone = $request->phone;
        
        // 기존 사용자 확인
        $existingUser = User::where('site_id', $site->id)->where('phone', $phone)->first();
        if ($existingUser) {
            return response()->json([
                'success' => false,
                'message' => '이미 가입된 전화번호입니다.',
            ], 400);
        }

        try {
            $verification = PhoneVerification::createVerification($site->id, $phone);
            
            // SMS 발송
            $smsService = new SmsService($site);
            $senderName = $site->getSetting('sms_sender_name', '');
            $result = $smsService->sendVerificationCode($phone, $verification->code, $senderName);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => '인증번호가 발송되었습니다. 문자를 확인해주세요.',
                ]);
            } else {
                // 개발 환경에서는 인증번호를 반환
                if (config('app.debug') || app()->environment('local')) {
                    return response()->json([
                        'success' => true,
                        'message' => '개발 모드: SMS 서버 설정이 없어 문자를 발송하지 못했습니다. 인증번호: ' . $verification->code,
                        'verification_code' => $verification->code,
                        'debug_mode' => true,
                    ]);
                }
                
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'SMS 발송에 실패했습니다.',
                ], 500);
            }
        } catch (\Exception $e) {
            \Log::error('전화번호 인증 발송 실패: ' . $e->getMessage());
            
            // 개발 환경에서는 인증번호를 반환
            if (config('app.debug') || app()->environment('local')) {
                $verification = PhoneVerification::createVerification($site->id, $phone);
                return response()->json([
                    'success' => true,
                    'message' => '개발 모드: SMS 서버 설정이 없어 문자를 발송하지 못했습니다. 인증번호: ' . $verification->code,
                    'verification_code' => $verification->code,
                    'debug_mode' => true,
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'SMS 발송 중 오류가 발생했습니다. SMS 서버 설정을 확인해주세요. 오류: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify phone with verification code.
     */
    public function verifyPhoneCode(Request $request, Site $site)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
            'code' => 'required|string|size:6',
        ]);

        $verification = PhoneVerification::where('site_id', $site->id)
            ->where('phone', $request->phone)
            ->where('code', $request->code)
            ->latest()
            ->first();

        if (!$verification) {
            return response()->json([
                'success' => false,
                'message' => '유효하지 않거나 만료된 인증번호입니다.',
            ], 400);
        }

        if ($verification->isExpired()) {
            return response()->json([
                'success' => false,
                'message' => '인증번호가 만료되었습니다. 다시 발송해주세요.',
            ], 400);
        }

        if ($verification->isVerified()) {
            return response()->json([
                'success' => false,
                'message' => '이미 인증된 전화번호입니다.',
            ], 400);
        }

        // 인증 완료 처리
        $verification->markAsVerified();
        
        // 세션에 인증된 전화번호 저장
        session(['verified_phone' => $verification->phone]);

        return response()->json([
            'success' => true,
            'message' => '전화번호 인증이 완료되었습니다.',
            'verified_phone' => $verification->phone,
        ]);
    }
}
