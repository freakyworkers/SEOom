<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'notice',
        'auto_delete_24h',
        'allow_guest',
        'banned_words',
    ];

    protected $casts = [
        'auto_delete_24h' => 'boolean',
        'allow_guest' => 'boolean',
    ];

    /**
     * Get the site that owns the chat setting.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get banned words as array.
     */
    public function getBannedWordsArray(): array
    {
        if (empty($this->banned_words)) {
            return [];
        }
        return array_filter(array_map('trim', explode("\n", $this->banned_words)));
    }

    /**
     * Check if a message contains banned words.
     */
    public function containsBannedWords(string $message): bool
    {
        $bannedWords = $this->getBannedWordsArray();
        if (empty($bannedWords)) {
            return false;
        }
        
        foreach ($bannedWords as $word) {
            if (stripos($message, $word) !== false) {
                return true;
            }
        }
        
        return false;
    }
}






