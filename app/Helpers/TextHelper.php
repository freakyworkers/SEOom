<?php

namespace App\Helpers;

class TextHelper
{
    /**
     * Convert URLs in text to clickable links.
     * 
     * @param string $text
     * @return string
     */
    public static function autoLink($text)
    {
        // 패턴 1: http:// 또는 https://로 시작하는 URL
        $pattern1 = '/(https?:\/\/[^\s<>"\'{}|\\^`\[\]]+)/i';
        $text = preg_replace($pattern1, '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>', $text);
        
        // 패턴 2: www.로 시작하는 URL
        $pattern2 = '/(www\.[^\s<>"\'{}|\\^`\[\]]+)/i';
        $text = preg_replace_callback($pattern2, function($matches) use (&$text) {
            $url = $matches[1];
            // 이미 링크로 변환된 경우는 건드리지 않음
            $pos = strpos($text, $url);
            if ($pos !== false) {
                $before = substr($text, max(0, $pos - 50), 50);
                // 이미 <a> 태그 안에 있거나 http://로 시작하는 링크 안에 있으면 건드리지 않음
                if (strpos($before, '<a') !== false || strpos($before, 'http://') !== false || strpos($before, 'https://') !== false) {
                    return $url;
                }
            }
            return '<a href="http://' . $url . '" target="_blank" rel="noopener noreferrer">' . $url . '</a>';
        }, $text);
        
        // 패턴 3: 도메인 형식 (naver.com, t.me/tcn_event 등)
        $pattern3 = '/\b([a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?\.(?:[a-zA-Z]{2,})(?:\/[^\s<>"\'{}|\\^`\[\]]*)?)\b/i';
        $text = preg_replace_callback($pattern3, function($matches) use (&$text) {
            $domain = $matches[1];
            
            // 이미 링크로 변환된 경우는 건드리지 않음
            if (preg_match('/<a[^>]*>.*?' . preg_quote($domain, '/') . '/is', $text)) {
                return $domain;
            }
            
            // 프로토콜이 이미 포함된 경우는 건드리지 않음
            $pos = strpos($text, $domain);
            if ($pos !== false && $pos > 0) {
                $before = substr($text, max(0, $pos - 15), 15);
                if (preg_match('/(https?|ftp|mailto|www):/i', $before)) {
                    return $domain;
                }
            }
            
            return '<a href="http://' . $domain . '" target="_blank" rel="noopener noreferrer">' . $domain . '</a>';
        }, $text);
        
        return $text;
    }
    
    /**
     * Convert URLs in HTML content to clickable links.
     * This method preserves existing HTML tags and only converts plain text URLs.
     * 
     * @param string $html
     * @return string
     */
    public static function autoLinkHtml($html)
    {
        if (empty($html)) {
            return $html;
        }
        
        // 1. 기존 <a> 태그를 임시로 마스킹
        $linkPlaceholders = [];
        $counter = 0;
        $html = preg_replace_callback('/<a\s+[^>]*>.*?<\/a>/is', function($matches) use (&$linkPlaceholders, &$counter) {
            $placeholder = "___LINK_TAG_{$counter}___";
            $linkPlaceholders[$placeholder] = $matches[0];
            $counter++;
            return $placeholder;
        }, $html);
        
        // 2. 이미지 태그 마스킹
        $html = preg_replace_callback('/<img[^>]*>/is', function($matches) use (&$linkPlaceholders, &$counter) {
            $placeholder = "___IMG_TAG_{$counter}___";
            $linkPlaceholders[$placeholder] = $matches[0];
            $counter++;
            return $placeholder;
        }, $html);
        
        // 3. HTML 태그 내부의 텍스트만 추출하여 URL을 링크로 변환
        // >텍스트< 패턴을 찾아서 텍스트 부분만 처리
        $html = preg_replace_callback('/>([^<>]+)</', function($matches) {
            $text = $matches[1];
            // 이미 링크나 이미지 플레이스홀더가 포함되어 있으면 건드리지 않음
            if (strpos($text, '___LINK_TAG_') !== false || strpos($text, '___IMG_TAG_') !== false) {
                return $matches[0];
            }
            $linked = self::autoLink($text);
            return '>' . $linked . '<';
        }, $html);
        
        // 4. 플레이스홀더를 원래 HTML 태그로 복원
        foreach (array_reverse($linkPlaceholders) as $placeholder => $original) {
            $html = str_replace($placeholder, $original, $html);
        }
        
        return $html;
    }
}

