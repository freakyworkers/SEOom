<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Site;
use App\Models\Post;

class NewPostNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $site;
    public $post;
    public $fromName;

    /**
     * Create a new message instance.
     */
    public function __construct(Site $site, Post $post)
    {
        $this->site = $site;
        $this->post = $post;
        $this->fromName = $site->getSetting('mail_from_name', $site->name);
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $boardName = $this->post->board->name ?? '알 수 없음';
        $authorName = $this->post->user->nickname ?? $this->post->user->name ?? '알 수 없음';
        
        return $this->subject($boardName . ' 새글 작성 알림')
                    ->view('emails.new-post-notification')
                    ->with([
                        'site' => $this->site,
                        'post' => $this->post,
                        'boardName' => $boardName,
                        'authorName' => $authorName,
                        'fromName' => $this->fromName,
                    ]);
    }
}









