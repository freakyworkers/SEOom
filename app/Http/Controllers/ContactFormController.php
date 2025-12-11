<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\ContactForm;
use App\Models\ContactFormSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

class ContactFormController extends Controller
{
    /**
     * Submit a contact form.
     */
    public function submit(Site $site, ContactForm $contactForm, Request $request)
    {
        // 필드 데이터 수집 (필드 이름을 키로 사용)
        $formData = [];
        foreach ($contactForm->fields as $field) {
            // name을 우선 사용, 없으면 label 사용
            $fieldName = $field['name'] ?? $field['label'] ?? '';
            if (empty($fieldName)) {
                continue;
            }
            
            // FormData는 공백을 언더스코어로 변환하므로, 두 가지 모두 시도
            $fieldValue = $request->input($fieldName);
            if ($fieldValue === null || $fieldValue === '') {
                // 공백을 언더스코어로 변환한 이름도 시도
                $underscoreName = str_replace(' ', '_', $fieldName);
                $fieldValue = $request->input($underscoreName);
            }
            
            // null과 빈 문자열만 체크 (empty()는 "0"도 빈 값으로 간주하므로 사용하지 않음)
            if ($fieldValue === null || $fieldValue === '') {
                return response()->json([
                    'success' => false,
                    'message' => $fieldName . '을(를) 입력해주세요.',
                ], 422);
            }
            $formData[$fieldName] = $fieldValue;
        }

        // 문의내용 처리
        $inquiryContent = $request->input('문의내용', $request->input('inquiry_content'));
        if ($contactForm->has_inquiry_content && empty($inquiryContent)) {
            return response()->json([
                'success' => false,
                'message' => '문의내용을 입력해주세요.',
            ], 422);
        }

        // 체크박스 데이터 처리
        $checkboxData = null;
        if ($contactForm->checkboxes && isset($contactForm->checkboxes['enabled']) && $contactForm->checkboxes['enabled']) {
            $checkboxValues = $request->input('checkboxes', []);
            if (!empty($checkboxValues)) {
                $checkboxData = is_array($checkboxValues) ? $checkboxValues : [$checkboxValues];
            }
        }

        // 제출 데이터 저장
        $submission = ContactFormSubmission::create([
            'contact_form_id' => $contactForm->id,
            'site_id' => $site->id,
            'data' => $formData,
            'inquiry_content' => $inquiryContent,
            'checkbox_data' => $checkboxData,
        ]);

        // 운영자에게 메일 발송
        $adminEmail = $site->getSetting('admin_notification_email');
        if ($adminEmail) {
            try {
                // 메일 설정 가져오기
                $mailer = $site->getSetting('mail_mailer', 'smtp');
                $mailUsername = $site->getSetting('mail_username', '');
                $mailConfig = [
                    'mailer' => $mailer,
                    'host' => $site->getSetting('mail_host', 'smtp.gmail.com'),
                    'port' => (int)$site->getSetting('mail_port', '587'),
                    'username' => $mailUsername,
                    'password' => $site->getSetting('mail_password', ''),
                    'encryption' => $site->getSetting('mail_encryption', 'tls'),
                    'from' => [
                        'address' => $mailUsername,
                        'name' => $site->getSetting('mail_from_name', $site->name),
                    ],
                ];

                // Config 설정
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

                app('mail.manager')->forgetMailers();

                // 메일 내용 생성
                $siteName = $site->getSetting('site_name', $site->name);
                $subject = $siteName . ' - 컨텍트폼 작성 인원이 추가 되었습니다.';
                
                $mailContent = "컨텍트폼 작성 인원이 추가 되었습니다.\n\n";
                foreach ($formData as $fieldName => $fieldValue) {
                    $mailContent .= $fieldName . " : " . $fieldValue . "\n";
                }
                
                // 체크박스 데이터 추가
                if ($checkboxData && !empty($checkboxData)) {
                    $mailContent .= "\n체크 항목\n";
                    if (is_array($checkboxData)) {
                        foreach ($checkboxData as $checkedItem) {
                            $mailContent .= "✓ " . $checkedItem . "\n";
                        }
                    } else {
                        $mailContent .= "✓ " . $checkboxData . "\n";
                    }
                }
                
                if ($contactForm->has_inquiry_content && $inquiryContent) {
                    $mailContent .= "\n문의 내용\n";
                    $mailContent .= $inquiryContent . "\n";
                }

                Mail::raw($mailContent, function ($message) use ($adminEmail, $subject, $mailConfig) {
                    $message->to($adminEmail)
                            ->subject($subject);
                });
            } catch (\Exception $e) {
                // 메일 발송 실패해도 제출은 성공으로 처리
                \Log::error('Contact form email sending failed: ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => '신청이 완료되었습니다.',
        ]);
    }
}

