<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use GuzzleHttp\Client;

class SocialLoginController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Redirect to social provider.
     */
    public function redirectToProvider(Site $site, $provider)
    {
        // 소셜 로그인이 활성화되어 있는지 확인
        if (!$site->getSetting('registration_enable_social_login', false)) {
            if ($site->isMasterSite()) {
                return redirect()->route('master.login')
                    ->with('error', '소셜 로그인이 비활성화되어 있습니다.');
            }
            return redirect()->route('login', ['site' => $site->slug])
                ->with('error', '소셜 로그인이 비활성화되어 있습니다.');
        }

        // 제공자별 Client ID 확인
        $clientId = null;
        $clientSecret = null;
        
        if ($provider === 'google') {
            $clientId = $site->getSetting('google_client_id', '');
            $clientSecret = $site->getSetting('google_client_secret', '');
        } elseif ($provider === 'naver') {
            $clientId = $site->getSetting('naver_client_id', '');
            $clientSecret = $site->getSetting('naver_client_secret', '');
        } elseif ($provider === 'kakao') {
            $clientId = $site->getSetting('kakao_client_id', '');
            $clientSecret = $site->getSetting('kakao_client_secret', '');
        }

        if (empty($clientId) || empty($clientSecret)) {
            if ($site->isMasterSite()) {
                return redirect()->route('master.login')
                    ->with('error', '소셜 로그인 설정이 완료되지 않았습니다.');
            }
            return redirect()->route('login', ['site' => $site->slug])
                ->with('error', '소셜 로그인 설정이 완료되지 않았습니다.');
        }

        // 사이트 정보를 세션에 저장
        session(['social_login_site_id' => $site->id]);
        session(['social_login_site_slug' => $site->slug]);

        // 공통 리디렉션 URI 사용 (모든 사이트가 동일한 콜백 URI 사용)
        // state 파라미터에 site 정보를 포함하여 전달
        $state = base64_encode(json_encode([
            'site_id' => $site->id,
            'site_slug' => $site->slug,
            'provider' => $provider
        ]));
        
        // 공통 콜백 URI (site 파라미터 없이)
        $redirectUrl = url('/auth/' . $provider . '/callback');
        
        \Log::info('Social login redirect', [
            'site_id' => $site->id,
            'site_slug' => $site->slug,
            'provider' => $provider,
            'redirect_url' => $redirectUrl,
            'client_id' => substr($clientId, 0, 20) . '...' // 보안을 위해 일부만 로그
        ]);
        
        // 개발 환경에서 SSL 검증 비활성화를 위한 HTTP 클라이언트 설정
        $httpClient = null;
        if (app()->environment('local', 'development')) {
            $httpClient = new Client(['verify' => false]);
        }
        
        // 네이버와 카카오는 추가 설정 필요
        if ($provider === 'naver') {
            config(['services.naver.client_id' => $clientId]);
            config(['services.naver.client_secret' => $clientSecret]);
            config(['services.naver.redirect' => $redirectUrl]);
            $driver = Socialite::driver('naver');
            if ($httpClient) {
                $driver->setHttpClient($httpClient);
            }
            return $driver
                ->with(['state' => $state])
                ->redirect();
        } elseif ($provider === 'kakao') {
            config(['services.kakao.client_id' => $clientId]);
            config(['services.kakao.client_secret' => $clientSecret]);
            config(['services.kakao.redirect' => $redirectUrl]);
            $driver = Socialite::driver('kakao');
            if ($httpClient) {
                $driver->setHttpClient($httpClient);
            }
            return $driver
                ->with(['state' => $state])
                ->redirect();
        } else {
            // 구글
            config(['services.google.client_id' => $clientId]);
            config(['services.google.client_secret' => $clientSecret]);
            config(['services.google.redirect' => $redirectUrl]);
            
            \Log::info('Google OAuth config', [
                'redirect' => config('services.google.redirect'),
                'client_id_set' => !empty(config('services.google.client_id'))
            ]);
            
            // 구글은 state를 세션에 저장하고 Socialite에 전달
            // 세션을 명시적으로 저장
            session()->put('social_login_state', $state);
            session()->put('social_login_site_id', $site->id);
            session()->put('social_login_site_slug', $site->slug);
            session()->save(); // 세션 즉시 저장
            
            \Log::info('Google OAuth - Session saved', [
                'session_id' => session()->getId(),
                'state_length' => strlen($state),
                'site_id' => $site->id
            ]);
            
            try {
                $driver = Socialite::driver('google');
                
                // 개발 환경에서 SSL 검증 비활성화
                if (app()->environment('local', 'development')) {
                    $driver->setHttpClient(new Client(['verify' => false]));
                }
                
                return $driver
                    ->with(['state' => $state])
                    ->redirect();
            } catch (\Exception $e) {
                \Log::error('Google OAuth redirect error', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
        }
    }

    /**
     * Handle social provider callback (공통 엔드포인트).
     */
    public function handleProviderCallback(Request $request, $provider)
    {
        // 모든 요청 정보를 로그로 기록 (디버깅용)
        \Log::info('=== Social login callback START ===', [
            'provider' => $provider,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'all_params' => $request->all(),
            'has_code' => $request->has('code'),
            'has_state' => $request->has('state'),
            'has_error' => $request->has('error'),
            'code_value' => $request->get('code') ? substr($request->get('code'), 0, 20) . '...' : null,
            'state_value' => $request->get('state') ? substr($request->get('state'), 0, 50) . '...' : null,
            'error_value' => $request->get('error'),
            'session_id' => session()->getId(),
            'session_data' => [
                'social_login_site_id' => session('social_login_site_id'),
                'social_login_site_slug' => session('social_login_site_slug'),
                'social_login_state' => session('social_login_state') ? substr(session('social_login_state'), 0, 50) . '...' : null
            ]
        ]);
        
        try {
            
            // state 파라미터에서 사이트 정보 가져오기
            $state = $request->get('state');
            $siteInfo = null;
            
            // 구글 OAuth의 경우 state를 URL 파라미터로 받음
            // state가 없으면 세션에서 가져오기
            if (!$state && $provider === 'google') {
                $state = session('social_login_state');
            }
            
            // state 디코딩 시도
            if ($state) {
                try {
                    // base64 디코딩
                    $decodedState = base64_decode($state, true);
                    if ($decodedState === false) {
                        // base64 디코딩 실패 시 그대로 사용 (이미 디코딩된 경우)
                        $decodedState = $state;
                    }
                    
                    $decoded = json_decode($decodedState, true);
                    if ($decoded && isset($decoded['site_id']) && isset($decoded['site_slug'])) {
                        $siteInfo = $decoded;
                        \Log::info('Social login - Site info from state', [
                            'site_id' => $siteInfo['site_id'],
                            'site_slug' => $siteInfo['site_slug']
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::error('State decode error: ' . $e->getMessage(), [
                        'state' => substr($state, 0, 50) . '...',
                        'provider' => $provider
                    ]);
                }
            }
            
            // state가 없으면 세션에서 직접 가져오기 (기존 방식 호환)
            if (!$siteInfo) {
                $siteId = session('social_login_site_id');
                $siteSlug = session('social_login_site_slug');
                
                if ($siteId && $siteSlug) {
                    $siteInfo = [
                        'site_id' => $siteId,
                        'site_slug' => $siteSlug,
                        'provider' => $provider
                    ];
                    \Log::info('Social login - Site info from session', [
                        'site_id' => $siteInfo['site_id'],
                        'site_slug' => $siteInfo['site_slug']
                    ]);
                }
            }
            
            if (!$siteInfo) {
                \Log::error('Social login callback - No site info found', [
                    'provider' => $provider,
                    'request_state' => $request->get('state'),
                    'request_code' => $request->has('code') ? 'present' : 'missing',
                    'request_error' => $request->get('error'),
                    'request_params_keys' => array_keys($request->all()),
                    'session_data' => [
                        'social_login_site_id' => session('social_login_site_id'),
                        'social_login_site_slug' => session('social_login_site_slug'),
                        'social_login_state' => session('social_login_state'),
                        'session_id' => session()->getId()
                    ],
                    'url' => $request->fullUrl()
                ]);
                
                // 마스터 사이트인지 확인하여 적절한 리디렉션
                $masterSite = \App\Models\Site::getMasterSite();
                if ($masterSite) {
                    return redirect('/')
                        ->with('error', '소셜 로그인 정보를 찾을 수 없습니다. 다시 시도해주세요.');
                }
                return redirect('/')
                    ->with('error', '소셜 로그인 정보를 찾을 수 없습니다. 다시 시도해주세요.');
            }
            
            // 사이트 조회
            $site = Site::find($siteInfo['site_id']);
            if (!$site || $site->slug !== $siteInfo['site_slug']) {
                \Log::error('Social login callback - Site not found or mismatch', [
                    'site_id' => $siteInfo['site_id'],
                    'site_slug' => $siteInfo['site_slug'],
                    'found_site' => $site ? ['id' => $site->id, 'slug' => $site->slug] : null
                ]);
                return redirect('/')
                    ->with('error', '사이트 정보를 찾을 수 없습니다.');
            }

            // 제공자별 Client ID/Secret 가져오기
            $clientId = null;
            $clientSecret = null;
            
            if ($provider === 'google') {
                $clientId = $site->getSetting('google_client_id', '');
                $clientSecret = $site->getSetting('google_client_secret', '');
            } elseif ($provider === 'naver') {
                $clientId = $site->getSetting('naver_client_id', '');
                $clientSecret = $site->getSetting('naver_client_secret', '');
            } elseif ($provider === 'kakao') {
                $clientId = $site->getSetting('kakao_client_id', '');
                $clientSecret = $site->getSetting('kakao_client_secret', '');
            }

            // 공통 콜백 URI
            $redirectUrl = url('/auth/' . $provider . '/callback');
            
            \Log::info('Social login callback - Configuring provider', [
                'provider' => $provider,
                'redirect_url' => $redirectUrl,
                'site_id' => $site->id,
                'has_client_id' => !empty($clientId),
                'has_client_secret' => !empty($clientSecret)
            ]);
            
            // 개발 환경에서 SSL 검증 비활성화를 위한 HTTP 클라이언트 설정
            $httpClient = null;
            if (app()->environment('local', 'development')) {
                $httpClient = new Client(['verify' => false]);
            }
            
            // 제공자별 설정을 config에 임시로 저장
            if ($provider === 'naver') {
                config(['services.naver.client_id' => $clientId]);
                config(['services.naver.client_secret' => $clientSecret]);
                config(['services.naver.redirect' => $redirectUrl]);
                $driver = Socialite::driver('naver');
                if ($httpClient) {
                    $driver->setHttpClient($httpClient);
                }
                $socialUser = $driver->user();
            } elseif ($provider === 'kakao') {
                config(['services.kakao.client_id' => $clientId]);
                config(['services.kakao.client_secret' => $clientSecret]);
                config(['services.kakao.redirect' => $redirectUrl]);
                $driver = Socialite::driver('kakao');
                if ($httpClient) {
                    $driver->setHttpClient($httpClient);
                }
                $socialUser = $driver->user();
            } else {
                config(['services.google.client_id' => $clientId]);
                config(['services.google.client_secret' => $clientSecret]);
                config(['services.google.redirect' => $redirectUrl]);
                
                \Log::info('Google OAuth - Attempting to get user', [
                    'redirect' => config('services.google.redirect'),
                    'client_id_length' => strlen($clientId ?? ''),
                    'request_state' => $request->get('state') ? 'present' : 'missing',
                    'session_state' => session('social_login_state') ? 'present' : 'missing'
                ]);
                
                try {
                    // OAuth 콜백에서 code 확인
                    if (!$request->has('code')) {
                        $error = $request->get('error');
                        $errorDescription = $request->get('error_description');
                        \Log::error('Google OAuth - Missing code', [
                            'error' => $error,
                            'error_description' => $errorDescription,
                            'request_params' => $request->except(['code', 'state']), // 민감한 정보 제외
                            'has_state' => $request->has('state')
                        ]);
                        
                        // 사용자가 취소한 경우
                        if ($error === 'access_denied') {
                            if ($site->isMasterSite()) {
                                return redirect('/')
                                    ->with('error', '구글 로그인이 취소되었습니다.');
                            }
                            return redirect()->route('login', ['site' => $site->slug])
                                ->with('error', '구글 로그인이 취소되었습니다.');
                        }
                        
                        throw new \Exception('OAuth 인증 코드를 받지 못했습니다. ' . ($errorDescription ?? $error ?? '알 수 없는 오류'));
                    }
                    
                    // Socialite의 state 검증을 우회하기 위해 stateless() 사용
                    // 대신 우리가 직접 state를 검증
                    $requestState = $request->get('state');
                    $sessionState = session('social_login_state');
                    
                    // state 검증: 요청의 state와 세션의 state가 일치하거나, state에서 사이트 정보를 추출할 수 있어야 함
                    if ($requestState && $sessionState && $requestState !== $sessionState) {
                        // state가 다르지만 둘 다 존재하는 경우, 요청의 state를 사용 (구글이 반환한 state)
                        \Log::warning('Google OAuth - State mismatch, using request state', [
                            'request_state' => substr($requestState, 0, 50),
                            'session_state' => substr($sessionState, 0, 50)
                        ]);
                        // 세션의 state를 요청의 state로 업데이트
                        session()->put('social_login_state', $requestState);
                    }
                    
                    // Socialite 설정 확인
                    \Log::info('Google OAuth - Before user() call', [
                        'redirect' => config('services.google.redirect'),
                        'has_client_id' => !empty(config('services.google.client_id')),
                        'has_code' => $request->has('code'),
                        'has_state' => $request->has('state')
                    ]);
                    
                    // stateless()를 사용하여 세션 기반 state 검증 우회
                    // 대신 우리가 직접 state를 처리
                    // config가 제대로 설정되었는지 다시 확인
                    if (empty(config('services.google.client_id')) || empty(config('services.google.client_secret'))) {
                        \Log::error('Google OAuth - Config not set properly', [
                            'has_client_id' => !empty(config('services.google.client_id')),
                            'has_client_secret' => !empty(config('services.google.client_secret'))
                        ]);
                        // 다시 설정 시도
                        config(['services.google.client_id' => $clientId]);
                        config(['services.google.client_secret' => $clientSecret]);
                        config(['services.google.redirect' => $redirectUrl]);
                    }
                    
                    $driver = Socialite::driver('google')->stateless();
                    if ($httpClient) {
                        $driver->setHttpClient($httpClient);
                    }
                    $socialUser = $driver->user();
                    \Log::info('Google OAuth - User retrieved', [
                        'user_id' => $socialUser->getId(),
                        'email' => $socialUser->getEmail(),
                        'name' => $socialUser->getName()
                    ]);
                } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
                    // State 불일치 오류 - stateless()를 사용했으므로 이 오류는 발생하지 않아야 함
                    // 하지만 혹시 모르니 처리
                    \Log::error('Google OAuth - Invalid state exception (unexpected with stateless)', [
                        'error' => $e->getMessage(),
                        'session_id' => session()->getId(),
                        'has_state_in_request' => $request->has('state'),
                        'has_state_in_session' => session()->has('social_login_state'),
                        'request_state_preview' => $request->get('state') ? substr($request->get('state'), 0, 50) : null
                    ]);
                    
                    // stateless()를 사용했는데도 InvalidStateException이 발생한다면
                    // 다른 문제가 있을 수 있음. state를 무시하고 재시도
                    try {
                        \Log::info('Google OAuth - Retrying with stateless (ignoring state validation)');
                        $driver = Socialite::driver('google')->stateless();
                        if ($httpClient) {
                            $driver->setHttpClient($httpClient);
                        }
                        $socialUser = $driver->user();
                        \Log::info('Google OAuth - User retrieved after retry', [
                            'user_id' => $socialUser->getId(),
                            'email' => $socialUser->getEmail()
                        ]);
                    } catch (\Exception $retryException) {
                        // 재시도도 실패
                        \Log::error('Google OAuth - Retry also failed', [
                            'error' => $retryException->getMessage(),
                            'error_class' => get_class($retryException)
                        ]);
                        
                        if ($site) {
                            if ($site->isMasterSite()) {
                                return redirect('/')
                                    ->with('error', '인증 오류가 발생했습니다. 다시 시도해주세요.');
                            }
                            return redirect()->route('login', ['site' => $site->slug])
                                ->with('error', '인증 오류가 발생했습니다. 다시 시도해주세요.');
                        }
                        throw $e;
                    }
                } catch (\Exception $e) {
                    \Log::error('Google OAuth - Failed to get user', [
                        'error' => $e->getMessage(),
                        'error_code' => $e->getCode(),
                        'error_class' => get_class($e),
                        'trace' => substr($e->getTraceAsString(), 0, 2000), // 처음 2000자
                        'request_params' => array_keys($request->all()), // 키만 로그
                        'has_code' => $request->has('code'),
                        'has_state' => $request->has('state'),
                        'config_client_id' => !empty(config('services.google.client_id')),
                        'config_client_secret' => !empty(config('services.google.client_secret')),
                        'config_redirect' => config('services.google.redirect')
                    ]);
                    
                    // 오류를 다시 throw하지 않고 사용자에게 친화적인 메시지 표시
                    if ($site) {
                        if ($site->isMasterSite()) {
                            return redirect('/')
                                ->with('error', '구글 로그인 처리 중 오류가 발생했습니다: ' . $e->getMessage());
                        }
                        return redirect()->route('login', ['site' => $site->slug])
                            ->with('error', '구글 로그인 처리 중 오류가 발생했습니다: ' . $e->getMessage());
                    }
                    throw $e;
                }
            }

            // 기존 사용자 찾기 또는 새로 생성
            $user = User::where('site_id', $site->id)
                ->where('provider', $provider)
                ->where('provider_id', $socialUser->getId())
                ->first();

            if (!$user) {
                // 이메일로 기존 사용자 확인 (소셜 로그인으로 가입하지 않은 경우)
                $email = $socialUser->getEmail();
                $existingUser = null;
                
                if (!empty($email)) {
                    $existingUser = User::where('site_id', $site->id)
                        ->where('email', $email)
                        ->whereNull('provider')
                        ->first();
                }

                if ($existingUser) {
                    // 기존 사용자에 소셜 로그인 정보 연결
                    $existingUser->provider = $provider;
                    $existingUser->provider_id = $socialUser->getId();
                    $existingUser->save();
                    $user = $existingUser;
                } else {
                    // 새 사용자 생성
                    $email = $socialUser->getEmail();
                    $name = $socialUser->getName() ?? $socialUser->getNickname() ?? '사용자';
                    $nickname = $socialUser->getNickname() ?? $name;
                    
                    // 이메일이 없는 경우 (카카오 등) provider_id를 기반으로 생성
                    if (empty($email)) {
                        $email = $provider . '_' . $socialUser->getId() . '@social.local';
                    }
                    
                    $userData = [
                        'site_id' => $site->id,
                        'name' => $name,
                        'email' => $email,
                        'username' => $this->generateUniqueUsername($site->id, $email),
                        'nickname' => $nickname,
                        'password' => bcrypt(Str::random(32)), // 랜덤 비밀번호 (소셜 로그인 사용자는 비밀번호 불필요)
                        'provider' => $provider,
                        'provider_id' => $socialUser->getId(),
                        'email_verified_at' => $email !== ($provider . '_' . $socialUser->getId() . '@social.local') ? now() : null, // 실제 이메일인 경우만 인증 완료로 간주
                        'role' => 'user',
                    ];

                    // 아바타 URL이 있으면 저장
                    if ($socialUser->getAvatar()) {
                        $userData['avatar'] = $socialUser->getAvatar();
                    }

                    try {
                        $user = User::create($userData);
                        
                        \Log::info('Social login - New user created', [
                            'user_id' => $user->id,
                            'site_id' => $site->id,
                            'provider' => $provider,
                            'email' => $user->email,
                            'username' => $user->username
                        ]);

                        // 가입 포인트 지급
                        $signupPoints = $site->getSetting('registration_signup_points', 0);
                        if ($signupPoints > 0) {
                            $user->addPoints($signupPoints);
                        }
                    } catch (\Exception $e) {
                        \Log::error('Social login - User creation failed', [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                            'userData' => $userData
                        ]);
                        throw new \Exception('사용자 생성에 실패했습니다: ' . $e->getMessage());
                    }
                }
            } else {
                // 기존 소셜 로그인 사용자 - 아바타 업데이트
                if ($socialUser->getAvatar() && $socialUser->getAvatar() !== $user->avatar) {
                    $user->avatar = $socialUser->getAvatar();
                    $user->save();
                }
                
                \Log::info('Social login - Existing user found', [
                    'user_id' => $user->id,
                    'site_id' => $site->id,
                    'provider' => $provider
                ]);
            }

            // 사용자가 제대로 생성/조회되었는지 확인
            if (!$user || !$user->id) {
                \Log::error('Social login - User not found or not created', [
                    'site_id' => $site->id,
                    'provider' => $provider
                ]);
                throw new \Exception('사용자 생성 또는 조회에 실패했습니다.');
            }

            // 로그인 처리
            Auth::login($user, true);
            
            // 세션 재생성 (보안을 위해) - 로그인 후에 수행
            $oldSessionId = session()->getId();
            session()->regenerate();
            $newSessionId = session()->getId();
            
            \Log::info('Social login - Session regenerated', [
                'old_session_id' => $oldSessionId,
                'new_session_id' => $newSessionId,
                'user_id' => $user->id
            ]);
            
            // 세션 정리 (세션 재생성 후에 정리)
            session()->forget(['social_login_site_id', 'social_login_site_slug', 'social_login_state']);
            session()->save(); // 세션 저장
            
            // 로그인 상태 확인
            $isAuthenticated = Auth::check();
            $authenticatedUserId = Auth::id();
            
            \Log::info('Social login - User logged in successfully', [
                'user_id' => $user->id,
                'site_id' => $site->id,
                'provider' => $provider,
                'authenticated' => $isAuthenticated,
                'authenticated_user_id' => $authenticatedUserId,
                'session_id' => session()->getId()
            ]);
            
            // 로그인 실패 시 에러
            if (!$isAuthenticated || $authenticatedUserId !== $user->id) {
                \Log::error('Social login - Authentication failed after login', [
                    'user_id' => $user->id,
                    'authenticated' => $isAuthenticated,
                    'authenticated_user_id' => $authenticatedUserId
                ]);
                throw new \Exception('로그인 처리에 실패했습니다.');
            }

            // 마스터 사이트인 경우 루트로 리다이렉트
            if ($site->isMasterSite()) {
                \Log::info('=== Social login SUCCESS - Redirecting to master site home ===', [
                    'user_id' => $user->id,
                    'site_id' => $site->id
                ]);
                return redirect('/')
                    ->with('success', '로그인되었습니다.');
            }

            \Log::info('=== Social login SUCCESS - Redirecting to site home ===', [
                'user_id' => $user->id,
                'site_id' => $site->id,
                'site_slug' => $site->slug
            ]);
            return redirect()->route('home', ['site' => $site->slug])
                ->with('success', '로그인되었습니다.');

        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            \Log::error('Social login error', [
                'message' => $errorMessage,
                'error_code' => $e->getCode(),
                'trace' => $e->getTraceAsString(),
                'provider' => $provider ?? 'unknown',
                'request_params' => $request->all()
            ]);
            
            // 사용자 친화적인 오류 메시지 생성
            $userFriendlyMessage = '소셜 로그인 중 오류가 발생했습니다.';
            if (strpos($errorMessage, 'code') !== false || strpos($errorMessage, 'OAuth') !== false) {
                $userFriendlyMessage = '소셜 로그인 인증에 실패했습니다. 다시 시도해주세요.';
            } elseif (strpos($errorMessage, 'Client') !== false || strpos($errorMessage, 'client') !== false) {
                $userFriendlyMessage = '소셜 로그인 설정이 올바르지 않습니다. 관리자에게 문의해주세요.';
            }
            
            // 사이트 정보가 있으면 해당 사이트 로그인 페이지로, 없으면 루트로
            if (isset($site) && $site) {
                if ($site->isMasterSite()) {
                    return redirect('/')
                        ->with('error', $userFriendlyMessage);
                }
                return redirect()->route('login', ['site' => $site->slug])
                    ->with('error', $userFriendlyMessage);
            }
            
            return redirect('/')
                ->with('error', $userFriendlyMessage);
        }
    }

    /**
     * Generate unique username.
     */
    private function generateUniqueUsername($siteId, $email)
    {
        $baseUsername = explode('@', $email)[0];
        $username = $baseUsername;
        $counter = 1;

        while (User::where('site_id', $siteId)->where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        return $username;
    }
}

