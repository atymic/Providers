<?php

namespace SocialiteProviders\Tests\HubSpot;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Psr\Http\Message\RequestInterface;
use SocialiteProviders\HubSpot\Provider;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Tests\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * @var array<int, array{request: RequestInterface}>
     */
    protected array $httpHistory = [];

    protected function provider(): string
    {
        return Provider::class;
    }

    /**
     * @param  array<int, Response>  $responses
     */
    protected function makeRecordingProvider(array $responses, ?Request $request = null): AbstractProvider
    {
        $provider = $this->makeProvider($request);

        $stack = HandlerStack::create(new MockHandler($responses));
        $this->httpHistory = [];
        $stack->push(Middleware::history($this->httpHistory));

        $provider->setHttpClient(new Client(['handler' => $stack]));

        return $provider;
    }

    /**
     * @param  array<string, mixed>  $body
     */
    protected function jsonResponse(array $body, int $status = 200): Response
    {
        return new Response($status, ['Content-Type' => 'application/json'], json_encode($body));
    }

    protected function lastRequest(): RequestInterface
    {
        $entry = end($this->httpHistory);

        $this->assertIsArray($entry, 'No HTTP request was recorded.');

        return $entry['request'];
    }

    /**
     * @return array<string, string>
     */
    protected function lastRequestBody(): array
    {
        parse_str((string) $this->lastRequest()->getBody(), $body);

        return $body;
    }

    protected function lastRequestUrl(): string
    {
        return (string) $this->lastRequest()->getUri();
    }
}
