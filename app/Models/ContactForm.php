<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'title',
        'fields',
        'has_inquiry_content',
        'button_text',
    ];

    protected $casts = [
        'fields' => 'array',
        'has_inquiry_content' => 'boolean',
    ];

    /**
     * Get the site that owns the contact form.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the submissions for this contact form.
     */
    public function submissions()
    {
        return $this->hasMany(ContactFormSubmission::class);
    }
}

