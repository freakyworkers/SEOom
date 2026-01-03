<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Subscription;
use App\Models\Site;
use App\Models\Payment;

class PaymentFailedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subscription;
    public $site;
    public $plan;
    public $payment;
    public $retryCount;
    public $nextRetryDate;

    /**
     * Create a new message instance.
     */
    public function __construct(Subscription $subscription, Payment $payment = null)
    {
        $this->subscription = $subscription;
        $this->site = $subscription->site;
        $this->plan = $subscription->plan;
        $this->payment = $payment;
        $this->retryCount = $subscription->retry_count;
        
        // 다음 재시도일 계산 (다음날)
        $this->nextRetryDate = \Carbon\Carbon::now()->addDay();
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = $this->plan->name . ' 구독료 결제 실패 안내';
        
        return $this->subject($subject)
                    ->view('emails.payment-failed')
                    ->with([
                        'subscription' => $this->subscription,
                        'site' => $this->site,
                        'plan' => $this->plan,
                        'payment' => $this->payment,
                        'retryCount' => $this->retryCount,
                        'nextRetryDate' => $this->nextRetryDate,
                    ]);
    }
}





