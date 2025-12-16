<?php

namespace App\Socialite;

use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;

class KakaoProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://kauth.kakao.com/oauth/authorize', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return 'https://kauth.kakao.com/oauth/token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get('https://kapi.kakao.com/v2/user/me', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        $kakaoAccount = $user['kakao_account'] ?? [];
        $properties = $user['properties'] ?? [];

        return (new User)->setRaw($user)->map([
            'id' => $user['id'] ?? null,
            'nickname' => $properties['nickname'] ?? $kakaoAccount['profile']['nickname'] ?? null,
            'name' => $properties['nickname'] ?? $kakaoAccount['profile']['nickname'] ?? null,
            'email' => $kakaoAccount['email'] ?? null,
            'avatar' => $properties['profile_image'] ?? $kakaoAccount['profile']['profile_image_url'] ?? null,
        ]);
    }

    /**
     * {@inheritdoc}
     * 카카오는 Client Secret을 사용하지 않으므로 client_secret을 제거합니다.
     */
    protected function getTokenFields($code)
    {
        $fields = parent::getTokenFields($code);
        // 카카오는 Client Secret을 사용하지 않으므로 제거
        unset($fields['client_secret']);
        
        return array_merge($fields, [
            'grant_type' => 'authorization_code',
        ]);
    }
}


