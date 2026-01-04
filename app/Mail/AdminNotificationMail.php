<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Site;

class AdminNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $site;
    public $type;
    public $data;

    /**
     * Create a new message instance.
     */
    public function __construct(Site $site, string $type, array $data = [])
    {
        $this->site = $site;
        $this->type = $type;
        $this->data = $data;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = $this->getSubject();
        
        return $this->subject($subject)
                    ->view('emails.admin-notification')
                    ->with([
                        'site' => $this->site,
                        'type' => $this->type,
                        'data' => $this->data,
                    ]);
    }

    /**
     * Get subject based on notification type.
     */
    private function getSubject(): string
    {
        $subjects = [
            'new_user' => '[' . $this->site->name . '] 새 회원가입 알림',
            'new_post' => '[' . $this->site->name . '] 새 게시글 작성 알림',
            'new_comment' => '[' . $this->site->name . '] 새 댓글 작성 알림',
            'new_message' => '[' . $this->site->name . '] 새 쪽지 수신 알림',
        ];

        return $subjects[$this->type] ?? '[' . $this->site->name . '] 알림';
    }
}








