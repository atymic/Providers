<?php

namespace SocialiteProviders\StreamElements;

use GuzzleHttp\RequestOptions;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;

class Provider extends AbstractProvider
{
    public const IDENTIFIER = 'STREAMELEMENTS';

    protected $scopeSeparator = ' ';

    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase('https://api.streamelements.com/oauth2/authorize', $state);
    }

    protected function getTokenUrl(): string
    {
        return 'https://api.streamelements.com/oauth2/token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get(
            'https://api.streamelements.com/kappa/v2/channels/me',
            [
                RequestOptions::HEADERS => [
                    'Authorization' => 'oAuth '.$token,
                ],
            ]
        );

        return json_decode((string) $response->getBody(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id'            => $user['_id'],
            'nickname'      => $user['username'],
            'name'          => $user['displayName'],
            'alias'         => $user['alias'],
            'email'         => $user['email'],
            'avatar'        => $user['avatar'],
            'type'          => $user['broadcasterType'],
            'partner'       => $user['isPartner'],
            'suspended'     => $user['suspended'],
        ]);
    }
}
