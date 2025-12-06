<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Subscription;
use App\Models\Site;

class PaymentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subscription;
    public $site;
    public $plan;
    public $daysUntilPayment;
    public $paymentDate;

    /**
     * Create a new message instance.
     */
    public function __construct(Subscription $subscription, int $daysUntilPayment)
    {
        $this->subscription = $subscription;
        $this->site = $subscription->site;
        $this->plan = $subscription->plan;
        $this->daysUntilPayment = $daysUntilPayment;
        
        // 결제 예정일 계산
        if ($subscription->isTrial() && $subscription->trial_ends_at) {
            $this->paymentDate = $subscription->trial_ends_at;
        } else {
            $this->paymentDate = $subscription->current_period_end;
        }
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = $this->daysUntilPayment . '일 후 ' . $this->plan->name . ' 구독료 결제 예정 안내';
        
        return $this->subject($subject)
                    ->view('emails.payment-reminder')
                    ->with([
                        'subscription' => $this->subscription,
                        'site' => $this->site,
                        'plan' => $this->plan,
                        'daysUntilPayment' => $this->daysUntilPayment,
                        'paymentDate' => $this->paymentDate,
                    ]);
    }
}


