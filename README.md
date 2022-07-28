# every8d-php

[![Build Status](https://travis-ci.org/minchao/every8d-php.svg?branch=master)](https://travis-ci.org/minchao/every8d-php)
[![Latest Stable Version](https://poser.pugx.org/minchao/every8d-php/v/stable)](https://packagist.org/packages/minchao/every8d-php)
[![Latest Unstable Version](https://poser.pugx.org/minchao/every8d-php/v/unstable)](https://packagist.org/packages/minchao/every8d-php)
[![composer.lock](https://poser.pugx.org/minchao/every8d-php/composerlock)](https://packagist.org/packages/minchao/every8d-php)

every8d-php 是 [EVERY8D](http://global.every8d.com.tw/) SMS HTTP API 2.1 的非官方 PHP Client SDK，使用前請先確認您已申請 EVERY8D 簡訊帳號。若您想在 Laravel 下使用，請參考 [every8d-laravel](https://github.com/minchao/every8d-laravel) 提供的 Service provider。

支援的 APIs：

- 取得帳戶餘額
  - [x] API21/HTTP/getCredit.ashx
- SMS
  - [x] API21/HTTP/sendSMS.ashx
  - [x] API21/HTTP/getDeliveryStatus.ashx
  - [x] API21/HTTP/eraseBooking.ashx
- MMS
  - [x] API21/HTTP/MMS/sendMMS.ashx
  - [x] API21/HTTP/MMS/getDeliveryStatus.ashx
  - [x] API21/HTTP/MMS/eraseBooking.ashx

## 執行環境

* PHP >= 7.0
* [Guzzle requirements](http://guzzle.readthedocs.io/en/latest/overview.html#requirements)

## 安裝

推薦使用 [Composer](https://getcomposer.org/) 安裝 every8d-php SDK，請在您的專案下執行：

```console
$ composer require minchao/every8d-php
```

## 使用

初始化 Client，設定 EVERY8D 帳號與密碼。

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$client = new \Every8d\Client(['username' => 'USERNAME', 'password' => 'PASSWORD']);
```

### 範例

#### 發送 SMS

Example:

```php
try {
    $sms = new \Every8d\Message\SMS('+886987654321', 'Hello, 世界');
    $result = $client->sendSMS($sms);
} catch (\Exception $e) {
    // 處理異常
}
```

Result:

```php
[
    'Credit' => 79.0,
    'Sent' => 1,
    'Cost' => 1.0,
    'Unsent' => 0,
    'BatchID' => '00000000-0000-0000-0000-000000000000',
]
```

#### 查詢 SMS 發送狀態

Example:

```php
try {
    $batchId = '00000000-0000-0000-0000-000000000000';
    $result = $client->getDeliveryStatusBySMS($batchId);
} catch (\Exception $e) {
    // 處理異常
}
```

Result:

```php
[
    'Count' => 1,
    'Records' => [
        'Name' => '',
        'Mobile' => '+886987654321',
        'SendTime' => '2018/01/01 00:00:00',
        'Cost' => 1.0,
        'Status' => 0,
    ],
]
```

#### 查詢餘額

Example:

```php
try {
    $client->getCredit();
} catch (\Exception $e) {
    // 處理異常
}
```

Result:

```php
79.0
```

### 使用 Webhook 接收簡訊發送回報

若您的帳號有設定 callback 回報網址，簡訊伺服器就會在簡訊發送後以 HTTP GET 方法通知回報網址。您可參考 [webhook](./webhook/index.php) 中的範例來接收簡訊發送回報。

啟動 Webhook：

使用 [PHP Built-in web server](http://php.net/manual/en/features.commandline.webserver.php) 快速啟動一個 Webhook 服務。

```console
$ php -S 127.0.0.1:80 -t webhook webhook/index.php
PHP 7.0.26 Development Server started at Mon Jan  1 12:00:00 2018
Listening on http://127.0.0.1:80
Document root is /srv/www/every8d-php/webhook
Press Ctrl-C to quit.
```

> 注意：PHP Built-in web server 僅供開發測試使用，請不要使用在正式環境或公用網路上

回報範例：

這是一個發送 SMS 後，接收到的回報範例。

```
[Mon Jan  1 12:01:00 2018] http://your-webhook.com/callback?BatchID=00000000-0000-0000-0000-000000000000&RM=%2b886987654321&RT=20180101120002&STATUS=100&SM=Hello%2c+%e4%b8%96%e7%95%8c&CustID=CUSTID&UserNo=000000&ST=20180101120001&MR=1&SUBJECT=&NAME=NAME&USERID=USERNAME&SOURCE=&CHARGE=0
array:14 [
  "BatchID" => "00000000-0000-0000-0000-000000000000"
  "RM" => "+886987654321"
  "RT" => "20180101120002"
  "STATUS" => "100"
  "SM" => "Hello, 世界"
  "CustID" => "CUSTID"
  "UserNo" => "000000"
  "ST" => "20180101120001"
  "MR" => "1"
  "SUBJECT" => ""
  "NAME" => "NAME"
  "USERID" => "USERNAME"
  "SOURCE" => ""
  "CHARGE" => "0"
]
```

## 開發

### 開發工具

本專案提供 Command Line Developer Tools，供您在開發時作為測試工具使用。

指令：

```console
$ bin/every8d
Developer Tools 0.0.1

Usage:
  command [options] [arguments]

Options:
  -h, --help               Display this help message
  -q, --quiet              Do not output any message
  -V, --version            Display this application version
      --ansi               Force ANSI output
      --no-ansi            Disable ANSI output
  -n, --no-interaction     Do not ask any interactive question
  -u, --username=USERNAME  EVERY8D Username
  -p, --password=PASSWORD  EVERY8D Password
  -v|vv|vvv, --verbose     Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Available commands:
  cancel           取消預約簡訊
  credit           餘額查詢
  delivery-status  發送狀態查詢
  help             Displays help for a command
  list             Lists commands
  send             發送 SMS
```

發送 SMS 範例如下：

```console
$ bin/every8d send -u USERNAME -p PASSWORD +886987654321 'Hello, World'
array:5 [
  "Credit" => 79.0
  "Sent" => 1
  "Cost" => 1.0
  "Unsent" => 0
  "BatchID" => "00000000-0000-0000-0000-000000000000"
]
```

### 測試

執行 PHPCS 與 Unit tests。

```console
$ composer run check
```

產生測試覆蓋率報告。

```console
$ composer run coverage-html
```

## License

This library is distributed under the BSD-style license found in the [LICENSE](./LICENSE) file.
