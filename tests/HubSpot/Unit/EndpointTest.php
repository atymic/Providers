<?php

namespace SocialiteProviders\Tests\HubSpot\Unit;

use SocialiteProviders\Tests\HubSpot\TestCase;

class EndpointTest extends TestCase
{
    public function test_token_exchange_posts_to_the_current_token_endpoint(): void
    {
        $provider = $this->makeRecordingProvider([
            $this->jsonResponse([
                'access_token'  => 'access-token',
                'refresh_token' => 'refresh-token',
                'expires_in'    => 1800,
            ]),
        ]);

        $provider->getAccessTokenResponse('auth-code');

        $this->assertSame('POST', $this->lastRequest()->getMethod());
        $this->assertSame(
            'https://api.hubapi.com/oauth/2026-03/token',
            $this->lastRequestUrl()
        );

        $body = $this->lastRequestBody();

        $this->assertSame('authorization_code', $body['grant_type']);
        $this->assertSame('auth-code', $body['code']);
        $this->assertSame(static::CLIENT_ID, $body['client_id']);
        $this->assertSame(static::CLIENT_SECRET, $body['client_secret']);
        $this->assertSame(static::REDIRECT_URI, $body['redirect_uri']);
    }

    public function test_refresh_token_posts_to_the_current_token_endpoint(): void
    {
        $provider = $this->makeRecordingProvider([
            $this->jsonResponse(['access_token' => 'new-access-token']),
        ]);

        $result = $provider->refreshToken('old-refresh-token');

        $this->assertSame('new-access-token', $result['access_token']);
        $this->assertSame(
            'https://api.hubapi.com/oauth/2026-03/token',
            $this->lastRequestUrl()
        );

        $body = $this->lastRequestBody();

        $this->assertSame('refresh_token', $body['grant_type']);
        $this->assertSame('old-refresh-token', $body['refresh_token']);
    }

    public function test_user_lookup_introspects_with_the_token_in_the_body(): void
    {
        $provider = $this->makeRecordingProvider([
            $this->jsonResponse([
                'active'    => true,
                'user'      => 'user@example.com',
                'user_id'   => 123456,
                'hub_id'    => 987654,
                'token_use' => 'access',
            ]),
        ]);

        $provider->userFromToken('access-token');

        $request = $this->lastRequest();

        $this->assertSame('POST', $request->getMethod());
        $this->assertSame(
            'https://api.hubapi.com/oauth/2026-03/token/introspect',
            $this->lastRequestUrl()
        );

        $body = $this->lastRequestBody();

        $this->assertSame('access-token', $body['token']);
        $this->assertSame('access_token', $body['token_type_hint']);
        $this->assertSame(static::CLIENT_ID, $body['client_id']);
        $this->assertSame(static::CLIENT_SECRET, $body['client_secret']);
    }

    /**
     * The v1 endpoint this replaced put the token in the URL path, leaking it
     * into access logs and browser history.
     */
    public function test_the_access_token_never_appears_in_a_url(): void
    {
        $provider = $this->makeRecordingProvider([
            $this->jsonResponse([
                'active'  => true,
                'user'    => 'user@example.com',
                'user_id' => 123456,
            ]),
        ]);

        $provider->userFromToken('super-secret-token');

        $this->assertStringNotContainsString('super-secret-token', $this->lastRequestUrl());
    }

    /**
     * Introspection answers 200 {"active": false} rather than a 4xx, so an
     * expired or revoked token would otherwise map to a null id and email.
     */
    public function test_an_inactive_token_is_rejected(): void
    {
        $provider = $this->makeRecordingProvider([
            $this->jsonResponse(['active' => false]),
        ]);

        $this->expectException(\InvalidArgumentException::class);

        $provider->userFromToken('revoked-token');
    }

    public function test_authorize_url_is_unchanged(): void
    {
        $provider = $this->makeProvider();

        $this->assertStringStartsWith(
            'https://app.hubspot.com/oauth/authorize',
            $provider->stateless()->redirect()->getTargetUrl()
        );
    }
}
