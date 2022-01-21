<?php

namespace Every8d;

use Every8d\Exception\BadResponseException;
use Every8d\Exception\ErrorResponseException;
use Every8d\Exception\NotFoundException;
use Every8d\Exception\UnexpectedStatusCodeException;
use Every8d\Message;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

/**
 * @method float getCredit()
 * @method array sendSMS(Message\SMS $sms)
 * @method array sendMMS(Message\MMS $mms)
 * @method array getDeliveryStatusBySMS(string $batchID, int $pageNo = null)
 * @method array getDeliveryStatusByMMS(string $batchID, int $pageNo = null)
 * @method array cancelSMS(string $batchID)
 * @method array cancelMMS(string $batchID)
 */
class Client
{
    const LIBRARY_VERSION = '1.0.2';

    const DEFAULT_BASE_URL = 'https://oms.every8d.com/';

    const DEFAULT_USER_AGENT = 'every8d-php/' . self::LIBRARY_VERSION;

    const ENV_USERNAME_NAME = 'EVERY8D_USERNAME';

    const ENV_PASSWORD_NAME = 'EVERY8D_PASSWORD';

    /**
     * @var ClientInterface
     */
    protected $httpClient;

    /**
     * @var string
     */
    protected $userAgent;

    /**
     * @var UriInterface
     */
    protected $baseUrl;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var Api
     */
    protected $api;

    public function __construct(array $config = [])
    {
        $config = array_merge([
            'httpClient' => null,
            'userAgent' => self::DEFAULT_USER_AGENT,
            'baseUrl' => self::DEFAULT_BASE_URL,
            'username' => getenv(self::ENV_USERNAME_NAME),
            'password' => getenv(self::ENV_PASSWORD_NAME),
        ], $config);

        $this->httpClient = $config['httpClient'] ?: new \GuzzleHttp\Client();
        $this->setUserAgent($config['userAgent']);
        $this->setBaseUrl($config['baseUrl']);
        $this->username = $config['username'];
        $this->password = $config['password'];
        $this->api = new Api($this);
    }

    public function __call(string $method, array $arguments)
    {
        if (!method_exists($this->api, $method)) {
            throw new \BadMethodCallException(sprintf('Method "%s" not found', $method));
        }

        return $this->getAPI()->$method(...$arguments);
    }

    public function getHttpClient(): ClientInterface
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

    public function getBaseUrl(): UriInterface
    {
        return $this->baseUrl;
    }

    public function setBaseUrl(string $baseUrl): self
    {
        $this->baseUrl = new Uri($baseUrl);

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
        if (method_exists(Utils::class, 'uriFor')) {
            $uri = Utils::uriFor($uri);
        } else {
            $uri = \GuzzleHttp\Psr7\uri_for($uri);
        }
        $path = rtrim($this->baseUrl->getPath() . $uri->getPath(), '/');

        $uri = $uri
            ->withPath($path)
            ->withScheme($this->baseUrl->getScheme())
            ->withUserInfo($this->baseUrl->getUserInfo())
            ->withHost($this->baseUrl->getHost())
            ->withPort($this->baseUrl->getPort());

        $headers['User-Agent'] = $this->userAgent;

        return new Request(
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
     * @throws UnexpectedStatusCodeException
     */
    public function send(RequestInterface $request): ResponseInterface
    {
        try {
            $response = $this->httpClient->send($request);
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

            return $response;
        } catch (RequestException $exception) {
            $statusCode = $exception->getCode();
            switch ($statusCode) {
                case 404:
                    throw new NotFoundException('Not found');
                    break;
                default:
                    throw new UnexpectedStatusCodeException(sprintf('Unexpected status code: %d', $statusCode));
            }
        }
    }

    public function getApi(): Api
    {
        return $this->api;
    }
}
