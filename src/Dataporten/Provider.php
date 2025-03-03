<?php

namespace SocialiteProviders\Dataporten;

use GuzzleHttp\RequestOptions;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;

class Provider extends AbstractProvider
{
    public const IDENTIFIER = 'DATAPORTEN';

    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase('https://auth.dataporten.no/oauth/authorization', $state);
    }

    protected function getTokenUrl(): string
    {
        return 'https://auth.dataporten.no/oauth/token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get('https://auth.dataporten.no/userinfo', [
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
        return (new User)->setRaw($user);
    }
}
