<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Site;
use App\Models\Comment;

class NewCommentNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $site;
    public $comment;
    public $fromName;

    /**
     * Create a new message instance.
     */
    public function __construct(Site $site, Comment $comment)
    {
        $this->site = $site;
        $this->comment = $comment;
        $this->fromName = $site->getSetting('mail_from_name', $site->name);
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $boardName = $this->comment->post->board->name ?? '알 수 없음';
        $authorName = $this->comment->user->nickname ?? $this->comment->user->name ?? '알 수 없음';
        $postTitle = $this->comment->post->title ?? '알 수 없음';
        
        return $this->subject($boardName . ' 새댓글 작성 알림')
                    ->view('emails.new-comment-notification')
                    ->with([
                        'site' => $this->site,
                        'comment' => $this->comment,
                        'boardName' => $boardName,
                        'authorName' => $authorName,
                        'postTitle' => $postTitle,
                        'fromName' => $this->fromName,
                    ]);
    }
}








