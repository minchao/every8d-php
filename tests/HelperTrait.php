<?php

namespace Every8d\Tests;

use Http\Discovery\MessageFactoryDiscovery;
use Http\Message\MessageFactory;
use Http\Mock\Client;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

trait HelperTrait
{
    /**
     * @var MessageFactory
     */
    protected $messageFactory;

    public function createMockHttpClient(ResponseInterface $response = null): Client
    {
        $client = new Client();
        if ($response !== null) {
            $client->addResponse($response);
        }

        return $client;
    }

    /**
     * @param string $method
     * @param string|UriInterface $uri
     * @param array $headers
     * @param resource|string|StreamInterface|null $body
     * @param string $protocolVersion
     * @return \Psr\Http\Message\RequestInterface
     */
    public function createRequest(
        string $method,
        $uri,
        array $headers = [],
        $body = null,
        string $protocolVersion = '1.1'
    ) {
        if ($this->messageFactory === null) {
            $this->messageFactory = MessageFactoryDiscovery::find();
        }
        
        return $this->messageFactory->createRequest($method, $uri, $headers, $body, $protocolVersion);
    }

    /**
     * @param int $statusCode
     * @param string|null $reasonPhrase
     * @param array $headers
     * @param resource|string|StreamInterface|null $body
     * @param string $protocolVersion
     * @return ResponseInterface
     */
    public function createResponse(
        int $statusCode = 200,
        string $reasonPhrase = null,
        array $headers = [],
        $body = null,
        string $protocolVersion = '1.1'
    ): ResponseInterface {
        if ($this->messageFactory === null) {
            $this->messageFactory = MessageFactoryDiscovery::find();
        }

        return $this->messageFactory->createResponse($statusCode, $reasonPhrase, $headers, $body, $protocolVersion);
    }
}
