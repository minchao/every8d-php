<?php

namespace Every8d\Tests;

use Every8d\Client;
use Every8d\Exception\BadResponseException;
use Every8d\Exception\NotFoundException;
use Http\Client\HttpClient;
use Http\Discovery\UriFactoryDiscovery;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    use HelperTrait;

    public function testCreate()
    {
        $client = new Client('username', 'password', $this->createMockHttpClient());

        $this->assertInstanceOf(HttpClient::class, $client->getHttpClient());
        $this->assertEquals(Client::DEFAULT_USER_AGENT, $client->getUserAgent());
        $this->assertEquals('UserAgent', $client->setUserAgent('UserAgent')->getUserAgent());
        $this->assertEquals(Client::DEFAULT_BASE_URL, $client->getBaseURL());

        $uri = UriFactoryDiscovery::find()->createUri('BaseURL');

        $this->assertEquals($uri, $client->setBaseURL($uri->__toString())->getBaseURL());
    }

    public function testSendWithEmptyResponseBody()
    {
        $this->expectException(BadResponseException::class);
        $this->expectExceptionMessage('Unexpected empty body');

        $httpClient = $this->createMockHttpClient($this->createResponse(200));

        $client = new Client('', '', $httpClient);
        $client->send($this->createRequest('GET', ''));
    }

    public function testSendWithNotFoundException()
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Not found');

        $httpClient = $this->createMockHttpClient($this->createResponse(404));

        $client = new Client('', '', $httpClient);
        $client->send($this->createRequest('GET', ''));
    }
}
