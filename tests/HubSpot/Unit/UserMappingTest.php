<?php

namespace SocialiteProviders\Tests\HubSpot\Unit;

use SocialiteProviders\Tests\HubSpot\TestCase;

class UserMappingTest extends TestCase
{
    public function test_it_maps_the_introspection_response(): void
    {
        $provider = $this->makeRecordingProvider([
            $this->jsonResponse($this->fixtureJson('introspect.json')),
        ]);

        $user = $provider->userFromToken('access-token');

        $this->assertSame(123456, $user->getId());
        $this->assertSame('user@example.com', $user->getEmail());
        $this->assertNull($user->getName());
        $this->assertNull($user->getNickname());
        $this->assertNull($user->getAvatar());
    }

    public function test_it_keeps_the_portal_details_on_the_raw_user(): void
    {
        $provider = $this->makeRecordingProvider([
            $this->jsonResponse($this->fixtureJson('introspect.json')),
        ]);

        $raw = $provider->userFromToken('access-token')->getRaw();

        $this->assertTrue($raw['active']);
        $this->assertSame(987654, $raw['hub_id']);
        $this->assertContains('crm.objects.contacts.read', $raw['scopes']);
    }
}
