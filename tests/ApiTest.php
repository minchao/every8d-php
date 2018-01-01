<?php

namespace Every8d\Tests;

use Every8d\Client;
use Every8d\Exception\BadResponseException;
use Every8d\Message\MMS;
use Every8d\Message\SMS;
use PHPUnit\Framework\TestCase;

class ApiTest extends TestCase
{
    use HelperTrait;

    public function testShouldBeOkWhenGetCredit()
    {
        $resp = $this->createResponse(200, null, [], '79.0');
        $client = new Client('', '', $this->createMockHttpClient($resp));

        $expected = 79.0;
        $actual = $client->getApi()->getCredit();

        $this->assertEquals($expected, $actual);
    }

    public function testShouldBeOkWhenSendSMS()
    {
        $resp = $this->createResponse(
            200,
            null,
            [],
            '87.0,1,1,0,00000000-0000-0000-0000-000000000000'
        );
        $client = new Client('', '', $this->createMockHttpClient($resp));

        $expected = [
            'Credit' => 87.0,
            'Sent' => 1,
            'Cost' => 1.0,
            'Unsent' => 0,
            'BatchID' => '00000000-0000-0000-0000-000000000000',
        ];
        $actual = $client->getApi()->sendSMS(new SMS([
            'MSG' => 'Hello, 世界',
            'DEST' => '+8860987654321',
        ]));

        $this->assertEquals($expected, $actual);
    }

    public function testShouldBeOkWhenSendMMS()
    {
        $resp = $this->createResponse(
            200,
            null,
            [],
            '80.0,1,1,0,A0000000-0000-0000-0000-000000000000'
        );
        $client = new Client('', '', $this->createMockHttpClient($resp));

        $expected = [
            'Credit' => 80.0,
            'Sent' => 1,
            'Cost' => 1.0,
            'Unsent' => 0,
            'BatchID' => 'A0000000-0000-0000-0000-000000000000',
        ];
        $actual = $client->getApi()->sendMMS(new MMS([
            'MSG' => 'Hello, 世界',
            'DEST' => '+8860987654321',
            'ATTACHMENT' => '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',
            'TYPE' => 'png',
        ]));

        $this->assertEquals($expected, $actual);
    }

    public function testShouldBeOkWhenGetDeliveryStatusBySMS()
    {
        $resp = $this->createResponse(
            200,
            null,
            [],
            "2\nTest	+886987654321	2017/12/18 23:14:17	1	100\n	+886987654321	2017/12/18 23:14:18	0	101\n"
        );
        $client = new Client('', '', $this->createMockHttpClient($resp));

        $expected = [
            'Count' => 2,
            'Records' => [
                [
                    'Name' => 'Test',
                    'Mobile' => '+886987654321',
                    'SendTime' => '2017/12/18 23:14:17',
                    'Cost' => 1.0,
                    'Status' => 100,
                ],
                [
                    'Name' => '',
                    'Mobile' => '+886987654321',
                    'SendTime' => '2017/12/18 23:14:18',
                    'Cost' => 0.0,
                    'Status' => 101,
                ],
            ],
        ];
        $actual = $client->getApi()->getDeliveryStatusBySMS('00000000-0000-0000-0000-000000000000', 1);

        $this->assertEquals($expected['Records'], $actual['Records']);
    }

    public function testShouldBeOkWhenGetDeliveryStatusByMMS()
    {
        $resp = $this->createResponse(
            200,
            null,
            [],
            "0\n"
        );
        $client = new Client('', '', $this->createMockHttpClient($resp));

        $expected = [
            'Count' => 0,
            'Records' => [
            ],
        ];
        $actual = $client->getApi()->getDeliveryStatusByMMS('00000000-0000-0000-0000-000000000000');

        $this->assertEquals($expected['Records'], $actual['Records']);
    }

    public function testGetDeliveryStatusBySMSWithBadResponseException()
    {
        $this->expectException(BadResponseException::class);
        $this->expectExceptionMessage('Invalid delivery status');

        $resp = $this->createResponse(
            200,
            null,
            [],
            "0"
        );
        $client = new Client('', '', $this->createMockHttpClient($resp));
        $client->getApi()->getDeliveryStatusByMMS('00000000-0000-0000-0000-000000000000');
    }
}
