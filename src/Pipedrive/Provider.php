<?php

namespace SocialiteProviders\Pipedrive;

use GuzzleHttp\RequestOptions;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;

class Provider extends AbstractProvider
{
    public const IDENTIFIER = 'PIPEDRIVE';

    protected $scopes = ['base'];

    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase('https://oauth.pipedrive.com/oauth/authorize', $state);
    }

    protected function getTokenUrl(): string
    {
        return 'https://oauth.pipedrive.com/oauth/token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get('https://api-proxy.pipedrive.com/users/me', [
            RequestOptions::HEADERS => [
                'Authorization' => 'Bearer '.$token,
            ],
        ]);

        return json_decode((string) $response->getBody(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id'       => $user['data']['id'],
            'name'     => $user['data']['name'],
            'email'    => $user['data']['email'],
        ]);
    }
}
