<?php

namespace Every8d;

use Every8d\Exception\BadResponseException;
use Every8d\Exception\ErrorResponseException;
use Every8d\Exception\NotFoundException;
use Every8d\Exception\UnexpectedResponseException;
use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Discovery\UriFactoryDiscovery;
use Http\Message\RequestFactory;
use Http\Message\UriFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class Client
{
    const LIBRARY_VERSION = '0.0.0';

    const DEFAULT_BASE_URL = 'https://oms.every8d.com/';

    const DEFAULT_USER_AGENT = 'every8d-php/' . self::LIBRARY_VERSION;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * @var RequestFactory
     */
    protected $requestFactory;

    /**
     * @var UriFactory
     */
    protected $uriFactory;

    /**
     * @var string
     */
    protected $userAgent;

    /**
     * @var UriInterface
     */
    protected $baseURL;

    public function __construct(
        string $username,
        string $password,
        HttpClient $httpClient = null,
        RequestFactory $requestFactory = null,
        UriFactory $uriFactory = null
    ) {
        $this->username = $username;
        $this->password = $password;
        $this->httpClient = $httpClient ?: HttpClientDiscovery::find();
        $this->requestFactory = $requestFactory ?: MessageFactoryDiscovery::find();
        $this->uriFactory = $uriFactory ?: UriFactoryDiscovery::find();
        $this->userAgent = self::DEFAULT_USER_AGENT;
        $this->setBaseURL(self::DEFAULT_BASE_URL);
    }

    public function getHttpClient(): HttpClient
    {
        return $this->httpClient;
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

    public function setBaseURL(string $baseURL): self
    {
        $this->baseURL = $this->uriFactory->createUri($baseURL);

        return $this;
    }

    /**
     * @param string $method
     * @param string|UriInterface $uri
     * @param array $headers
     * @param string|null|resource|StreamInterface $body
     * @return RequestInterface
     */
    public function newRequest(string $method, $uri, array $headers = [], $body = null): RequestInterface
    {
        $uri = $this->uriFactory->createUri($uri);
        if ($uri->getScheme() === '') {
            $path = rtrim($this->baseURL->getPath() . $uri->getPath(), '/');
            $uri = $uri
                ->withScheme($this->baseURL->getScheme())
                ->withUserInfo($this->baseURL->getUserInfo())
                ->withHost($this->baseURL->getHost())
                ->withPort($this->baseURL->getPort())
                ->withPath($path);
        }

        $headers['User-Agent'] = $this->userAgent;

        return $this->requestFactory->createRequest(
            $method,
            $uri,
            $headers,
            $body
        );
    }

    /**
     * @param string|UriInterface $uri
     * @param array $body
     * @return RequestInterface
     */
    public function newFormRequest($uri, array $body = []): RequestInterface
    {
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        $body['UID'] = $this->username;
        $body['PWD'] = $this->password;

        return $this->newRequest('POST', $uri, $headers, http_build_query($body));
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws BadResponseException
     * @throws ErrorResponseException
     * @throws NotFoundException
     * @throws UnexpectedResponseException
     * @throws \Exception
     * @throws \Http\Client\Exception
     */
    public function send(RequestInterface $request): ResponseInterface
    {
        $response = $this->httpClient->sendRequest($request);

        $this->checkErrorResponse($response);

        return $response;
    }

    /**
     * Check the API response for errors
     *
     * @param ResponseInterface $response
     * @throws BadResponseException
     * @throws ErrorResponseException
     * @throws NotFoundException
     * @throws UnexpectedResponseException
     */
    public function checkErrorResponse(ResponseInterface $response)
    {
        $statusCode = $response->getStatusCode();
        switch ($statusCode) {
            case 200:
                if (!$response->getBody()->getSize()) {
                    throw new BadResponseException('Unexpected empty body');
                }

                $first = $response->getBody()->read(1);
                $response->getBody()->rewind();

                if ($first === '-') {
                    $contents = $response->getBody()->getContents();
                    $error = explode(',', $contents);

                    throw new ErrorResponseException(trim($error[1]), (int)$error[0]);
                }

                break;
            case 404:
                throw new NotFoundException('Not found');
                break;
            default:
                throw new UnexpectedResponseException(sprintf('Unexpected status code: %d', $statusCode));
                break;
        }
    }
}
