<?php

namespace Every8d\Tests;

use Every8d\Client;
use Every8d\Exception\BadResponseException;
use Every8d\Exception\ErrorResponseException;
use Every8d\Exception\NotFoundException;
use Every8d\Exception\UnexpectedStatusCodeException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use function GuzzleHttp\Psr7\uri_for;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    use HelperTrait;

    public function testCreate()
    {
        $client = $this->createClient();

        $this->assertInstanceOf(ClientInterface::class, $client->getHttpClient());
        $this->assertEquals(Client::DEFAULT_USER_AGENT, $client->getUserAgent());
        $this->assertEquals('UserAgent', $client->setUserAgent('UserAgent')->getUserAgent());
        $this->assertEquals(Client::DEFAULT_BASE_URL, $client->getBaseUrl());
    }

    public function testShouldThrowBadMethodCallExceptionWhenCallNotExistsMethodInClient()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Method "badMethod" not found');

        $client = $this->createClient();
        $client->badMethod();
    }

    public function testShouldBeOkWhenNewRequest()
    {
        $client = $this->createClient();

        $expected = new Request(
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
                '',
                'https://oms.every8d.com',
            ],
            [
                'https://oms.every8d.com/',
                'path',
                'https://oms.every8d.com/path',
            ],
            [
                'https://oms.every8d.com/',
                'base/path',
                'https://oms.every8d.com/base/path',
            ],
            [
                'https://oms.every8d.com/base/',
                'path',
                'https://oms.every8d.com/base/path',
            ],
            [
                'https://oms.every8d.com/',
                'base/path?query=string',
                'https://oms.every8d.com/base/path?query=string',
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
        $client = $this->createClient();
        $client->setBaseUrl($baseUri);

        $expected = uri_for($exceptedUri);
        $actual = $client->newFormRequest($actualUri)->getUri();

        $this->assertEquals($expected, $actual);
    }

    public function testShouldBeOkWhenNewFormRequest()
    {
        $client = $this->createClient();

        $expected = new Request(
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
        $resp = new Response(200, [], 'ok');
        $client = $this->createClient([], $resp);

        $request = new Request('GET', '');
        $response = $client->send($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('ok', $response->getBody()->getContents());
    }

    public function testSendWithErrorResponseException()
    {
        $this->expectException(ErrorResponseException::class);
        $this->expectExceptionMessage('主機端發生不明錯誤，請與廠商窗口聯繫。');
        $this->expectExceptionCode(-99);

        $resp = new Response(200, [], '-99, 主機端發生不明錯誤，請與廠商窗口聯繫。');

        $client = $this->createClient([], $resp);
        $client->send(new Request('GET', ''));
    }

    public function testSendWithBadResponseException()
    {
        $this->expectException(BadResponseException::class);
        $this->expectExceptionMessage('Unexpected empty body');

        $resp = new Response(200);
        $client = $this->createClient([], $resp);
        $client->send(new Request('GET', ''));
    }

    public function testSendWithNotFoundException()
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Not found');

        $resp = new Response(404);
        $client = $this->createClient([], $resp);
        $client->send(new Request('GET', ''));
    }

    public function testSendWithUnexpectedResponseException()
    {
        $this->expectException(UnexpectedStatusCodeException::class);
        $this->expectExceptionMessage('Unexpected status code: 402');

        $resp = new Response(402);
        $client = $this->createClient([], $resp);
        $client->send(new Request('GET', ''));
    }
}
