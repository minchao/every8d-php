<?php

namespace Every8d\Tests;

use Every8d\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

trait HelperTrait
{
    public function createMockHttpClient(ResponseInterface $response = null): HttpClient
    {
        $mock = new MockHandler([
            $response,
        ]);
        $handler = HandlerStack::create($mock);

        return new HttpClient(['handler' => $handler]);
    }

    public function createClient(array $config = [], Response $response = null): Client
    {
        if ($response !== null) {
            $config['httpClient'] = $this->createMockHttpClient($response);
        }

        $client = new Client(
            array_merge([
                'username' => 'username',
                'password' => 'password',
            ], $config)
        );

        return $client;
    }
}
