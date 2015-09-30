Yii2-betssonsports
=================

Yii2 client for Betsson Sportsbook API

Requirements:
=================

PHP5 with SOAP client.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require --prefer-dist drsdre/yii2-betssonsports "*"
```

or add

```json
"drsdre/yii2-betssonsports": "*"
```

to the `require` section of your `composer.json` file.

Usage
-----

You need to setup the client as application component:

```php
'components' => [
    'betssonsportsApi' => [
        'class' => '\BetssonSports\Client',
        'service_url' => 'zzz',
        'loginName' => 'xxx',
        'password' => 'zzz',
    ]
    ...
]
```

or define the client directly in the code:

```php
$client = new \BetssonSports\Client([
    'service_url' => 'yyy',
	'loginName' => 'xxx',
	'password' => 'zzz',
]);
```

How to use API:
=================

Request documentation from Betsson Affiliate program.	

That's all!
-----------