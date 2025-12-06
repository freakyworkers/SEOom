<?php

namespace App\Services;

use App\Models\User;
use App\Models\Site;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

class AuthService
{
    /**
     * Register a new user.
     */
    public function register(array $data, $siteId = null)
    {
        $site = Site::find($siteId);
        
        DB::beginTransaction();
        try {
            $userData = [
                'site_id' => $siteId,
                'username' => $data['username'] ?? null,
                'name' => $data['name'],
                'nickname' => $data['nickname'] ?? null,
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => $data['role'] ?? 'user',
            ];

            // 전화번호
            if (isset($data['phone'])) {
                $userData['phone'] = $data['phone'];
            }

            // 주소
            if (isset($data['postal_code'])) {
                $userData['postal_code'] = $data['postal_code'];
            }
            if (isset($data['address'])) {
                $userData['address'] = $data['address'];
            }
            if (isset($data['address_detail'])) {
                $userData['address_detail'] = $data['address_detail'];
            }

            // 추천인 처리
            if (isset($data['referrer_nickname']) && !empty($data['referrer_nickname'])) {
                $referrer = User::where('site_id', $siteId)
                    ->where(function($query) use ($data) {
                        $query->where('nickname', $data['referrer_nickname'])
                              ->orWhere('name', $data['referrer_nickname']);
                    })
                    ->first();
                
                if ($referrer) {
                    $userData['referrer_id'] = $referrer->id;
                }
            }

            $user = User::create($userData);

            // 가입 포인트 지급
            $signupPoints = $site ? (int)$site->getSetting('registration_signup_points', 0) : 0;
            if ($signupPoints > 0) {
                $user->addPoints($signupPoints);
                
                // 알림 생성
                $notificationService = new NotificationService();
                $notificationService->createPointAwardNotification(
                    $user->id,
                    $siteId,
                    $signupPoints,
                    '회원가입'
                );
            }

            // 추천인 포인트 지급
            if ($user->referrer_id && $site) {
                $referrerPoints = (int)$site->getSetting('registration_referrer_points', 0);
                $newUserPoints = (int)$site->getSetting('registration_new_user_points', 0);
                
                // 추천인에게 포인트 지급
                if ($referrerPoints > 0) {
                    $referrer = User::find($user->referrer_id);
                    if ($referrer) {
                        $referrer->addPoints($referrerPoints);
                        
                        // 알림 생성
                        $notificationService = new NotificationService();
                        $notificationService->createPointAwardNotification(
                            $referrer->id,
                            $siteId,
                            $referrerPoints,
                            '추천인 가입'
                        );
                    }
                }
                
                // 가입자에게 추천인 포인트 지급
                if ($newUserPoints > 0) {
                    $user->addPoints($newUserPoints);
                    
                    // 알림 생성
                    $notificationService = new NotificationService();
                    $notificationService->createPointAwardNotification(
                        $user->id,
                        $siteId,
                        $newUserPoints,
                        '추천인 가입'
                    );
                }
            }

            // 운영자에게 새 회원가입 알림 메일 발송
            if ($site && $site->getSetting('notify_new_user', false)) {
                $adminEmail = $site->getSetting('admin_notification_email', '');
                if ($adminEmail) {
                    try {
                        $this->sendAdminNotification($site, 'new_user', [
                            'name' => $user->name,
                            'nickname' => $user->nickname,
                            'email' => $user->email,
                            'username' => $user->username,
                            'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                        ]);
                    } catch (\Exception $e) {
                        \Log::warning('운영자 알림 메일 발송 실패: ' . $e->getMessage());
                    }
                }
            }

            DB::commit();
            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Send admin notification email.
     */
    private function sendAdminNotification(Site $site, string $type, array $data = [])
    {
        $adminEmail = $site->getSetting('admin_notification_email', '');
        if (!$adminEmail) {
            return;
        }

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

        Mail::to($adminEmail)->send(new \App\Mail\AdminNotificationMail($site, $type, $data));
    }

    /**
     * Attempt to login user.
     * If site_id is provided in credentials, only users from that site can login.
     * Supports login with email or username based on login_method setting.
     */
    public function login(array $credentials, $remember = false)
    {
        $ipAddress = request()->ip();
        
        // If site_id is provided, add it to the query
        if (isset($credentials['site_id'])) {
            $siteId = $credentials['site_id'];
            $loginMethod = $credentials['login_method'] ?? 'email';
            unset($credentials['site_id']);
            unset($credentials['login_method']);
            
            // Get login identifier (email or username)
            $login = $credentials['login'] ?? $credentials['email'] ?? null;
            $password = $credentials['password'] ?? null;
            
            if (!$login || !$password) {
                return false;
            }
            
            // Find user by email or username based on login_method
            $user = User::where('site_id', $siteId);
            
            if ($loginMethod === 'username') {
                $user = $user->where('username', $login);
            } else {
                $user = $user->where('email', $login);
            }
            
            $user = $user->first();
            
            if ($user && Hash::check($password, $user->password)) {
                // Update last login IP
                $user->update(['last_login_ip' => $ipAddress]);
                
                Auth::login($user, $remember);
                request()->session()->regenerate();
                return true;
            }
            
            return false;
        }

        // Default Laravel authentication (for backward compatibility)
        $login = $credentials['login'] ?? $credentials['email'] ?? null;
        $password = $credentials['password'] ?? null;
        
        if ($login && $password) {
            // Try to find user by email
            $user = User::where('email', $login)->first();
            
            // If not found by email, try username
            if (!$user) {
                $user = User::where('username', $login)->first();
            }
            
            if ($user && Hash::check($password, $user->password)) {
                // Update last login IP
                $user->update(['last_login_ip' => $ipAddress]);
                
                Auth::login($user, $remember);
                request()->session()->regenerate();
                return true;
            }
        }

        return false;
    }

    /**
     * Logout user.
     */
    public function logout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }

    /**
     * Get authenticated user.
     */
    public function user()
    {
        return Auth::user();
    }

    /**
     * Check if user is authenticated.
     */
    public function check()
    {
        return Auth::check();
    }
}
