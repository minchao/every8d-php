# every8d-php

[![Build Status](https://travis-ci.org/minchao/every8d-php.svg?branch=master)](https://travis-ci.org/minchao/every8d-php)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/95646cba27e7495d94f364546142d0fc)](https://www.codacy.com/app/minchao/every8d-php?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=minchao/every8d-php&amp;utm_campaign=Badge_Grade)
[![Codacy Badge](https://api.codacy.com/project/badge/Coverage/95646cba27e7495d94f364546142d0fc)](https://www.codacy.com/app/minchao/every8d-php?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=minchao/every8d-php&amp;utm_campaign=Badge_Coverage)
[![Latest Stable Version](https://poser.pugx.org/minchao/every8d-php/v/stable)](https://packagist.org/packages/minchao/every8d-php)
[![Latest Unstable Version](https://poser.pugx.org/minchao/every8d-php/v/unstable)](https://packagist.org/packages/minchao/every8d-php)
[![composer.lock](https://poser.pugx.org/minchao/every8d-php/composerlock)](https://packagist.org/packages/minchao/every8d-php)

every8d-php 是 [EVERY8D](http://global.every8d.com.tw/) SMS HTTP API 2.1 的非官方 PHP Client SDK，使用前請先確認您已申請 EVERY8D 簡訊帳號

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
* [HTTPlug](http://docs.php-http.org/)

## 安裝

推薦使用 [Composer](https://getcomposer.org/) 安裝 every8d-php SDK，請在您的專案下執行：

```
composer require minchao/every8d-php php-http/curl-client
```

> 由於 every8d-php 的 HTTP Client 是採用 HTTPlug 的實現，您可以按喜好選擇搭配的 HTTP Client，如 `php-http/curl-client` 或 `php-http/guzzle6-adapter`

## 使用

初始化 Client，設定 EVERY8D 帳號與密碼

```php
<?php

require_once(__DIR__ . '/vendor/autoload.php');

$client = new \Every8d\Client('USERNAME', 'PASSWORD');
```

### 範例

#### 發送 SMS

Example:

```php
try {
    $sms = new \Every8d\Message\SMS('+886987654321', 'Hello, 世界');
    $result = $client->getApi()->sendSMS($sms);
} catch (\Exception $e) {
    // 處理異常
}
```

$result:

```php
[
    'Credit' => 79.0,
    'Sent' => 1,
    'Cost' => 1.0,
    'Unsent' => 0,
    'BatchID' => '00000000-0000-0000-0000-000000000000',
]
```

### 使用 Webhook 接收簡訊發送回報

若您的帳號有設定 callback 回報網址，簡訊伺服器就會在簡訊發送後以 HTTP GET 方法通知回報網址。您可參考 [webhook](./webhook/index.php) 中的範例來接收簡訊發送回報

啟動 Webhook：

使用 [PHP Built-in web server](http://php.net/manual/en/features.commandline.webserver.php) 快速啟動一個 Webhook 服務

```
$ php -S 127.0.0.1:80 -t webhook webhook/index.php
PHP 7.0.26 Development Server started at Mon Jan  1 12:00:00 2018
Listening on http://127.0.0.1:80
Document root is /srv/www/every8d-php/webhook
Press Ctrl-C to quit.
```

> 注意：PHP Built-in web server 僅供開發測試使用，請不要使用在正式環境或公用網路上

回報範例：

這是一個發送 SMS 後，接收到的回報範例

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

本專案提供 Command Line Developer Tools，供您在開發時作為測試工具使用

指令：

```
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

```
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

執行 PHPCS 與 Unit tests

```
$ composer run check
```

## License

This library is distributed under the BSD-style license found in the [LICENSE](./LICENSE) file.
