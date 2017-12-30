<?php

namespace Every8d;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class Client
{
    const LIBRARY_VERSION = '0.0.0';

    const DEFAULT_BASE_URL = 'https://oms.every8d.com/';

    const DEFAULT_USER_AGENT = 'every8d-php/' . self::LIBRARY_VERSION;

    /**
     * @var ClientInterface
     */
    protected $httpClient;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $userAgent;

    /**
     * @var UriInterface
     */
    protected $baseURL;

    public function __construct(string $username, string $password, ClientInterface $httpClient)
    {
        $this->username = $username;
        $this->password = $password;
        $this->userAgent = self::DEFAULT_USER_AGENT;
        $this->baseURL = new Uri(self::DEFAULT_BASE_URL);
        $this->httpClient = $httpClient;
    }

    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    public function setUserAgent(string $userAgent): self
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    public function getBaseURL(): UriInterface
    {
        return $this->baseURL;
    }

    public function setBaseURL(UriInterface $baseURL): self
    {
        $this->baseURL = $baseURL;

        return $this;
    }

    /**
     * @param string $method
     * @param string|UriInterface $uri
     * @param string|null $contentType
     * @param string|null|resource|StreamInterface $body
     * @return Request
     */
    public function newRequest(string $method, $uri, string $contentType = null, $body = null): Request
    {
        $headers = [
            'User-Agent' => $this->userAgent,
        ];
        if (!is_null($contentType)) {
            $headers['Content-Type'] = $contentType;
        }

        return new Request(
            $method,
            $uri,
            $headers,
            $body
        );
    }
}
