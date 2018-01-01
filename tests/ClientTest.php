<?php

namespace Every8d\Tests;

use Every8d\Client;
use Every8d\Exception\BadResponseException;
use Every8d\Exception\ErrorResponseException;
use Every8d\Exception\NotFoundException;
use Every8d\Exception\UnexpectedStatusCodeException;
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

    public function testShouldBeOkWhenNewRequest()
    {
        $client = new Client('', '', $this->createMockHttpClient());

        $expected = $this->createRequest(
            'GET',
            'https://oms.every8d.com/path',
            [
                'User-Agent' => $client->getUserAgent(),
                'Content-Type' => 'text/html; charset=utf-8',
            ],
            'ok'
        );

        $actual = $client->newRequest(
            'GET',
            'path',
            [
                'Content-Type' => 'text/html; charset=utf-8',
            ],
            'ok'
        );

        $this->assertEquals($expected->getMethod(), $actual->getMethod());
        $this->assertEquals($expected->getUri(), $actual->getUri());
        $this->assertEquals($expected->getHeaders(), $actual->getHeaders());
        $this->assertEquals($expected->getBody()->getContents(), $actual->getBody()->getContents());
        $this->assertEquals($expected->getProtocolVersion(), $actual->getProtocolVersion());
    }

    public function getRequestUriCases()
    {
        return [
            [
                'https://oms.every8d.com/',
                'path',
                'https://oms.every8d.com/path',
            ],
            [
                'https://oms.every8d.com/basePath/',
                'path',
                'https://oms.every8d.com/basePath/path',
            ],
            [
                'https://oms.every8d.com/basePath/',
                'path/',
                'https://oms.every8d.com/basePath/path',
            ],
            [
                'https://oms.every8d.com/basePath/',
                '',
                'https://oms.every8d.com/basePath',
            ],
            [
                'https://oms.every8d.com/',
                'https://example.com/path',
                'https://example.com/path',
            ],
        ];
    }

    /**
     * @dataProvider getRequestUriCases
     * @param string $baseUri
     * @param string $actualUri
     * @param string $exceptedUri
     */
    public function testShouldBeOkWhenNewRequestWithUri(string $baseUri, string $actualUri, string $exceptedUri)
    {
        $client = new Client('', '', $this->createMockHttpClient());
        $client->setBaseURL($baseUri);

        $expected = $this->createRequest(
            'GET',
            $exceptedUri
        );

        $actual = $client->newRequest(
            'GET',
            $actualUri
        );

        $this->assertEquals($expected->getUri(), $actual->getUri());
    }

    public function testShouldBeOkWhenNewFormRequest()
    {
        $client = new Client('username', 'password', $this->createMockHttpClient());

        $expected = $this->createRequest(
            'POST',
            'https://oms.every8d.com/path',
            [
                'User-Agent' => $client->getUserAgent(),
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            http_build_query([
                'UID' => 'username',
                'PWD' => 'password',
            ])
        );

        $actual = $client->newFormRequest('path');

        $this->assertEquals($expected->getMethod(), $actual->getMethod());
        $this->assertEquals($expected->getUri(), $actual->getUri());
        $this->assertEquals($expected->getHeaders(), $actual->getHeaders());
        $this->assertEquals($expected->getBody()->getContents(), $actual->getBody()->getContents());
        $this->assertEquals($expected->getProtocolVersion(), $actual->getProtocolVersion());
    }

    public function testShouldBeOkWhenSend()
    {
        $httpClient = $this->createMockHttpClient(
            $this->createResponse(200, null, [], 'ok')
        );
        $client = new Client('', '', $httpClient);

        $response = $client->send($this->createRequest('GET', ''));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('ok', $response->getBody()->getContents());
    }

    public function testSendWithErrorResponseException()
    {
        $this->expectException(ErrorResponseException::class);
        $this->expectExceptionMessage('主機端發生不明錯誤，請與廠商窗口聯繫。');
        $this->expectExceptionCode(-99);

        $httpClient = $this->createMockHttpClient(
            $this->createResponse(200, null, [], '-99, 主機端發生不明錯誤，請與廠商窗口聯繫。')
        );
        $client = new Client('', '', $httpClient);
        $client->send($this->createRequest('GET', ''));
    }

    public function testSendWithBadResponseException()
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

    public function testSendWithUnexpectedResponseException()
    {
        $this->expectException(UnexpectedStatusCodeException::class);
        $this->expectExceptionMessage('Unexpected status code: 402');

        $httpClient = $this->createMockHttpClient($this->createResponse(402));

        $client = new Client('', '', $httpClient);
        $client->send($this->createRequest('GET', ''));
    }
}
