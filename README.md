# every8d-php

[![Build Status](https://travis-ci.org/minchao/every8d-php.svg?branch=master)](https://travis-ci.org/minchao/every8d-php)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/95646cba27e7495d94f364546142d0fc)](https://www.codacy.com/app/minchao/every8d-php?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=minchao/every8d-php&amp;utm_campaign=Badge_Grade)
[![Codacy Badge](https://api.codacy.com/project/badge/Coverage/95646cba27e7495d94f364546142d0fc)](https://www.codacy.com/app/minchao/every8d-php?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=minchao/every8d-php&amp;utm_campaign=Badge_Coverage)
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

## 開發

### 開發工具

本專案提供 Command Line Developer Tools，供您在開發時作為測試工具使用

指令：

```
$ bin/every8d
Developer Tools 0.0.0

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
