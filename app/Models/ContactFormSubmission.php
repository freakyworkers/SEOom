<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactFormSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_form_id',
        'site_id',
        'data',
        'inquiry_content',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Get the contact form that owns the submission.
     */
    public function contactForm()
    {
        return $this->belongsTo(ContactForm::class);
    }

    /**
     * Get the site that owns the submission.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}



