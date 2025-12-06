<?php

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];
    
    /**
     * Determine if encryption has been disabled for the given cookie.
     *
     * @param  string  $name
     * @return bool
     */
    protected function shouldDisableEncryption($name)
    {
        // popup_hidden_으로 시작하는 쿠키는 암호화하지 않음
        if (strpos($name, 'popup_hidden_') === 0) {
            return true;
        }
        
        return parent::shouldDisableEncryption($name);
    }
}




