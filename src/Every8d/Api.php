<?php

namespace Every8d;

use Every8d\Message\MMS;
use Every8d\Message\SMS;

class Api
{
    /**
     * @var Client
     */
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @return float
     * @throws Exception\BadResponseException
     * @throws Exception\ErrorResponseException
     * @throws Exception\NotFoundException
     * @throws Exception\UnexpectedResponseException
     * @throws \Exception
     * @throws \Http\Client\Exception
     */
    public function credit(): float
    {
        $request = $this->client->newFormRequest('API21/HTTP/getCredit.ashx');
        $response = $this->client->send($request);
        $contents = $response->getBody()->getContents();

        return (float)$contents;
    }

    /**
     * @param string $uri
     * @param array $formData
     * @return array
     * @throws Exception\BadResponseException
     * @throws Exception\ErrorResponseException
     * @throws Exception\NotFoundException
     * @throws Exception\UnexpectedResponseException
     * @throws \Exception
     * @throws \Http\Client\Exception
     */
    protected function send(string $uri, array $formData): array
    {
        $request = $this->client->newFormRequest($uri, $formData);
        $response = $this->client->send($request);
        $contents = $response->getBody()->getContents();
        $record = str_getcsv($contents, ',');

        return [
            'Credit' => (float)$record[0],
            'Sent' => (int)$record[1],
            'Cost' => (float)$record[2],
            'Unsent' => (int)$record[3],
            'BatchID' => $record[4],
        ];
    }

    /**
     * @param SMS $sms
     * @return array
     * @throws Exception\BadResponseException
     * @throws Exception\NotFoundException
     * @throws Exception\UnexpectedResponseException
     * @throws \Exception
     * @throws \Http\Client\Exception
     */
    public function sendSMS(SMS $sms): array
    {
        return $this->send('API21/HTTP/sendSMS.ashx', $sms->buildFormData());
    }

    /**
     * @param MMS $mms
     * @return array
     * @throws Exception\BadResponseException
     * @throws Exception\ErrorResponseException
     * @throws Exception\NotFoundException
     * @throws Exception\UnexpectedResponseException
     * @throws \Exception
     * @throws \Http\Client\Exception
     */
    public function sendMMS(MMS $mms): array
    {
        return $this->send('API21/HTTP/MMS/sendMMS.ashx', $mms->buildFormData());
    }
}
